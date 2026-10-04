<?php

namespace App\Repositories\Announcement;

use App\Models\Announcement;
use App\Repositories\BaseRepository;
use App\Repositories\Announcement\AnnouncementInterface;

class AnnouncementRepository extends BaseRepository implements AnnouncementInterface
{
    /** Allowed option sets — mirror the announcements migration. */
    public const AUDIENCES = ['All', 'Agents', 'Staff', 'Customers'];
    public const STATUSES  = ['active', 'archived'];

    /** Relations eager-loaded on list / find. */
    protected array $with = [];

    public function __construct(Announcement $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'title'        => $request->title,
            'body'         => $request->body,
            'audience'     => $request->audience,
            'published_on' => $request->published_on,
            'status'       => $request->status,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }
        if (isset($filters['audience'])) {
            $query->where('audience', $filters['audience']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }

    public function formData(): array
    {
        return [
            'audiences' => self::AUDIENCES,
            'statuses'  => self::STATUSES,
        ];
    }
}
