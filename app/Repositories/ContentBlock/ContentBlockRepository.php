<?php

namespace App\Repositories\ContentBlock;

use App\Models\ContentBlock;
use App\Repositories\BaseRepository;
use App\Repositories\Concerns\StoresPublicImage;

class ContentBlockRepository extends BaseRepository implements ContentBlockInterface
{
    use StoresPublicImage;

    public const STATUSES = ['active', 'inactive'];

    public function __construct(ContentBlock $model)
    {
        parent::__construct($model);
    }

    protected function data($request): array
    {
        return [
            'section'    => $request->section,
            'icon'       => $request->icon,
            'title'      => $request->title,
            'body'       => $request->body,
            'url'        => $request->url,
            'sort_order' => $request->sort_order ?? 0,
            'status'     => $request->status,
            'image'      => $this->resolveImage($request, 'image', 'image_url', 'content-blocks', $this->currentImage($request, 'image')),
        ];
    }

    /** Grouped by section so the admin list reads the way the site renders. */
    public function all(array $filters = [])
    {
        return $this->model->newQuery()
            ->when(filled($filters['section'] ?? null), fn ($q) => $q->where('section', $filters['section']))
            ->when(filled($filters['status'] ?? null), fn ($q) => $q->where('status', $filters['status']))
            ->when(filled($filters['search'] ?? null), fn ($q) => $q->where('title', 'like', "%{$filters['search']}%"))
            ->orderBy('section')->orderBy('sort_order')->orderBy('id')
            ->get();
    }

    public function formData(): array
    {
        return [
            'sections' => ContentBlock::SECTIONS,
            'statuses' => self::STATUSES,
        ];
    }
}
