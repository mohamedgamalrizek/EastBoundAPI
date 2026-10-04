<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Backend\Concerns\BuildsListAnalytics;

/**
 * Standard admin CRUD controller for the ERP modules.
 *
 * A concrete controller extends this, sets $viewPath + $redirectRoute, injects
 * its repository, and only declares store()/update() (so each keeps its typed
 * FormRequest for validation) which delegate to persist(). index/create/edit/
 * delete are inherited.
 *
 * Convention:
 *   - views live at "{$viewPath}.index|create|edit"
 *   - the list view receives $items; create/edit receive $item (null on create)
 *     plus everything from the repository's formData()
 */
abstract class BaseCrudController extends Controller
{
    use BuildsListAnalytics;

    /** Repository instance — assign in the subclass constructor. */
    protected $repo;

    /**
     * Declarative list-page analytics. Set in a subclass to auto-render a
     * <x-list-analytics> block, e.g. ['model' => 'Task', 'group' => 'status'].
     * Null = no chart. See BuildsListAnalytics::autoAnalytics().
     */
    protected ?array $listAnalyticsConfig = null;

    /** Blade view folder, e.g. 'backend.hotel'. */
    protected string $viewPath;

    /** Route to redirect to after a successful store/update, e.g. 'hotel.index'. */
    protected string $redirectRoute;

    public function index(Request $request)
    {
        return view("{$this->viewPath}.index", [
            'items'     => $this->repo->all($request->query()),
            'analytics' => $this->listAnalytics($request),
        ]);
    }

    /**
     * Optional KPI/chart payload for the list page, consumed by the
     * <x-list-analytics> component. Return null (default) to render no chart.
     * Child controllers override this to provide ['stats','donut','trend'].
     */
    protected function listAnalytics(Request $request): ?array
    {
        return $this->listAnalyticsConfig
            ? $this->autoAnalytics($this->listAnalyticsConfig)
            : null;
    }

    public function create()
    {
        return view("{$this->viewPath}.create", array_merge(
            ['item' => null],
            $this->repo->formData()
        ));
    }

    public function edit($id)
    {
        return view("{$this->viewPath}.edit", array_merge(
            ['item' => $this->repo->find($id)],
            $this->repo->formData()
        ));
    }

    public function delete($id)
    {
        $result = $this->repo->delete($id);

        return response()->json($result, $result['status_code']);
    }

    /** Turn a repository CRUD result into a redirect. Used by store()/update(). */
    protected function persist(array $result)
    {
        if ($result['status']) {
            return redirect()->route($this->redirectRoute)->with('success', $result['message']);
        }

        return back()->with('danger', $result['message'])->withInput();
    }
}
