<?php

namespace App\Repositories;

use App\Traits\ReturnFormatTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * Shared CRUD plumbing for the ERP module repositories.
 *
 * A concrete repository extends this, injects its model, and only implements
 * data() (request -> columns). Override the hooks (applyFilters, formData,
 * guardDelete, $with) when a module needs more. Returns use ReturnFormatTrait
 * so controllers get a uniform ['status','message','status_code'] shape.
 */
abstract class BaseRepository
{
    use ReturnFormatTrait;

    protected Model $model;

    /** Relations eager-loaded on list / find. */
    protected array $with = [];

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /** Map a validated request to the model's columns. */
    abstract protected function data($request): array;

    /* ---- Overridable hooks ---------------------------------------------- */

    /** Apply list filters. $filters is already stripped of null/'' values. */
    protected function applyFilters($query, array $filters): void
    {
        //
    }

    /** Select-option data shared by the create & edit forms. */
    public function formData(): array
    {
        return [];
    }

    /** Return an error message to block deletion, or null to allow it. */
    protected function guardDelete($model): ?string
    {
        return null;
    }

    /* ---- CRUD ----------------------------------------------------------- */

    public function all(array $filters = [])
    {
        return $this->filtered($filters)->latest()->get();
    }

    public function paginate(int $perPage = 10, array $filters = [])
    {
        return $this->filtered($filters)->latest()->paginate($perPage)->withQueryString();
    }

    public function find($id)
    {
        return $this->model->newQuery()->with($this->with)->findOrFail($id);
    }

    public function store($request)
    {
        try {
            $model = $this->model->newQuery()->create($this->data($request));
            $this->logActivity('created', $model);

            return $this->responseWithSuccess(___('alert.successfully_added'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function update($request)
    {
        try {
            $model = $this->find($request->id);
            $model->update($this->data($request));
            $this->logActivity('updated', $model);

            return $this->responseWithSuccess(___('alert.successfully_updated'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function delete($id)
    {
        try {
            $model = $this->find($id);

            if ($message = $this->guardDelete($model)) {
                return $this->responseWithError($message);
            }

            $this->logActivity('deleted', $model);
            $model->delete();

            return $this->responseWithSuccess(___('alert.successfully_deleted'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    /* ---- internal ------------------------------------------------------- */

    /**
     * Record a CRUD action in the activity log. Wrapped so a logging failure
     * can never break the underlying CRUD operation.
     */
    protected function logActivity(string $event, $model): void
    {
        try {
            $name = class_basename($model);

            activity()
                ->performedOn($model)
                ->causedBy(auth()->user())
                ->event($event)
                ->log("{$event} {$name} #{$model->getKey()}");
        } catch (\Throwable $th) {
            // Logging is best-effort; never surface its failures to the user.
        }
    }

    private function filtered(array $filters)
    {
        $query = $this->model->newQuery()->with($this->with);

        $this->applyFilters($query, array_filter($filters, fn ($v) => $v !== null && $v !== ''));

        return $query;
    }
}
