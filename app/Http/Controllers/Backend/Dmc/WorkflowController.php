<?php

namespace App\Http\Controllers\Backend\Dmc;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dmc\SaveWorkflowRequest;
use App\Models\Dmc;
use App\Models\Supplier;
use App\Models\User;
use App\Repositories\Dmc\WorkflowRepository;
use App\Services\Dmc\ProcurementWorkflow;
use App\Services\Dmc\SalesWorkflow;
use App\Services\Dmc\TripOperations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class WorkflowController extends Controller
{
    public function __construct(private WorkflowRepository $repo) {}

    public function index(Request $request)
    {
        [$module, $definition] = $this->definition($request);
        $request->validate(['view' => ['nullable', 'in:pending,upcoming,today,active,confirmed'], 'trip' => ['nullable', 'integer', 'min:1']]);
        $records = $this->repo->query($module, $request->only(['view', 'trip']))->latest('id')->paginate(25)->withQueryString();

        return view('backend.dmc.index', compact('module', 'definition', 'records') + $this->formData($definition));
    }

    public function create(Request $request)
    {
        [$module, $definition] = $this->definition($request);
        abort_unless($definition['editable'] || ($definition['create_only'] ?? false), 404);
        $record = new $definition['model'];

        return view('backend.dmc.form', compact('module', 'definition', 'record') + $this->formData($definition));
    }

    public function edit(Request $request)
    {
        [$module, $definition] = $this->definition($request);
        abort_unless($definition['editable'] || ($definition['update_only'] ?? false), 404);
        $record = $this->repo->query($module)->findOrFail($request->route('record'));
        abort_unless($this->repo->canEdit($module, $record), 403);
        if ($module === 'trip-items') {
            $resources = ['hotel_id' => 'hotel', 'driver_id' => 'transport', 'guide_id' => 'guide', 'activity_id' => 'activity'];
            $definition['fields'] = array_filter($definition['fields'], fn ($field) => ! isset($resources[$field]) || $resources[$field] === $record->quotationItem->service_type, ARRAY_FILTER_USE_KEY);
        }

        return view('backend.dmc.form', compact('module', 'definition', 'record') + $this->formData($definition));
    }

    public function store(SaveWorkflowRequest $request)
    {
        [$module, $definition] = $this->definition($request);
        abort_unless($definition['editable'] || ($definition['create_only'] ?? false), 404);
        $record = $this->repo->save($module, $request->validated());

        return redirect()->route('dmc.'.$module.'.show', $record->id)->with('success', 'Record created successfully.');
    }

    public function update(SaveWorkflowRequest $request)
    {
        [$module, $definition] = $this->definition($request);
        abort_unless($definition['editable'] || ($definition['update_only'] ?? false), 404);
        $record = $this->repo->query($module)->findOrFail($request->route('record'));
        $record = $this->repo->save($module, $request->validated(), $record);

        return redirect()->route('dmc.'.$module.'.show', $record->id)->with('success', 'Record updated successfully.');
    }

    public function show(Request $request)
    {
        [$module, $definition] = $this->definition($request);
        $record = $this->repo->query($module)->findOrFail($request->route('record'));

        return view('backend.dmc.show', compact('module', 'definition', 'record') + ['canEdit' => $this->repo->canEdit($module, $record)] + $this->formData($definition));
    }

    public function action(Request $request)
    {
        [$module, $definition] = $this->definition($request);
        $data = $request->validate([
            'action' => ['required', Rule::in(['submit', 'approve', 'reject', 'accept', 'revise', 'send', 'respond', 'award', 'ready', 'start', 'complete', 'confirm', 'lose', 'receive'])],
            'note' => ['nullable', 'string', 'max:2000'],
            'customer_confirmation' => ['nullable', 'string', 'max:255'],
            'manager_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);
        $record = $this->repo->query($module)->findOrFail($request->route('record'));
        if ($record instanceof Dmc\Quotation && in_array($data['action'], ['approve', 'reject'])) {
            abort_unless($request->user()->can_access('dmc_sales_approve_gm') || $request->user()->can_access('dmc_sales_approve_tl'), 403);
        } elseif ($record instanceof Dmc\Rfq && $data['action'] === 'award') {
            abort_unless($request->user()->can_access('dmc_procurement_update') || $request->user()->can_access('dmc_sales_approve_gm'), 403);
        } else {
            abort_unless($request->user()->can_access($definition['permission'].'_update'), 403);
        }
        if ($record instanceof Dmc\Quotation) {
            $result = app(SalesWorkflow::class)->transition($record, $data['action'], $request->user(), $data);
            $module = $result instanceof Dmc\SalesOrder ? 'sales-orders' : 'quotations';
        } elseif ($record instanceof Dmc\Rfq) {
            $result = app(ProcurementWorkflow::class)->transition($record, $data['action'], $request->user(), $data['note'] ?? null);
            $module = $result instanceof Dmc\VendorOrder ? 'vendor-orders' : 'rfqs';
        } elseif ($record instanceof Dmc\Trip) {
            $result = app(TripOperations::class)->transition($record, $data['action']);
        } elseif ($record instanceof Dmc\TripItem && $data['action'] === 'confirm') {
            $result = app(TripOperations::class)->confirmItem($record);
        } elseif ($record instanceof Dmc\Opportunity && $data['action'] === 'lose') {
            $result = DB::transaction(function () use ($record, $data) {
                $record = Dmc\Opportunity::lockForUpdate()->findOrFail($record->id);
                if (in_array($record->stage, ['Won', 'Lost']) || ! filled($data['note'] ?? null)
                    || $record->quotations()->whereIn('status', ['Submitted', 'Approved', 'Accepted'])->exists()) {
                    throw ValidationException::withMessages(['workflow' => 'A reason is required; closed opportunities or opportunities with committed quotations cannot be marked lost.']);
                }
                $record->update(['stage' => 'Lost', 'notes' => trim($record->notes."\nLost: ".$data['note'])]);
                $record->lead?->update(['stage' => 'Lost', 'lost_reason' => $data['note']]);

                return $record;
            });
        } elseif ($record instanceof Dmc\VendorOrder && $data['action'] === 'receive') {
            abort_unless($record->status === 'Issued', 422);
            $record->update(['status' => 'Received']);
            $result = $record;
        } else {
            abort(422, 'This action is not available for this record.');
        }

        return redirect()->route('dmc.'.$module.'.show', $result->id)->with('success', 'Workflow action completed.');
    }

    private function definition(Request $request): array
    {
        $module = $request->route('module');

        return [$module, $this->repo->definition($module)];
    }

    private function formData(array $definition): array
    {
        $options = [];
        foreach ($definition['fields'] as $name => $field) {
            if (($field['type'] ?? '') === 'relation') {
                $query = $field['model']::orderBy($field['column']);
                if ($name === 'expense_account_id') {
                    $query->where('type', 'Expense');
                }
                $columns = ['id', $field['column']];
                if (in_array($name, ['manager_id', 'assigned_to'])) {
                    $columns = array_unique(array_merge($columns, ['permissions', 'status']));
                }
                $rows = $query->get($columns);
                if (in_array($name, ['manager_id', 'assigned_to'])) {
                    $permission = $name === 'manager_id' ? 'dmc_operations_update' : 'dmc_sales_read';
                    $rows = $rows->filter(fn ($user) => $user->isAdminUser() && $user->can_access($permission) && $user->status === Status::ACTIVE);
                }
                $options[$name] = $rows
                    ->mapWithKeys(fn ($row) => [$row->id => '#'.$row->id.' '.$row->{$field['column']}])->all();
            } elseif (isset($field['options'])) {
                $options[$name] = $field['options'];
            }
        }

        return ['options' => $options, 'presenter' => $this->repo, 'suppliers' => Supplier::orderBy('name')->get(['id', 'name']), 'paymentKey' => Str::uuid(),
            'operationsManagers' => User::orderBy('name')->get(['id', 'name', 'permissions', 'status'])->filter(fn ($user) => $user->isAdminUser() && $user->can_access('dmc_operations_update') && $user->status === Status::ACTIVE)];
    }
}
