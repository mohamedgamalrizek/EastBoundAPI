<?php

namespace App\Repositories\Concerns;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * File handling for CMS image columns.
 *
 * These columns hold a plain string that `media_url()` resolves — either an
 * absolute URL (handy for stock imagery) or a path under public/. So the forms
 * offer both: upload a file, or paste a URL. An upload always wins; otherwise
 * the typed value is kept, and clearing the field clears the image.
 *
 * Deliberately separate from UploadRepository, which stores rows in `uploads`
 * and is resolved through logo()/getImage(); mixing the two would mean two
 * different meanings for the same column.
 */
trait StoresPublicImage
{
    /**
     * Resolve an image column from the request.
     *
     * @param  string $fileField Name of the file input (e.g. "image_file").
     * @param  string $textField Name of the URL/path text input.
     * @param  string $folder    Sub-folder of public/uploads.
     * @param  string|null $current Existing value, replaced when a file arrives.
     */
    protected function resolveImage($request, string $fileField, string $textField, string $folder, ?string $current = null): ?string
    {
        if ($request->hasFile($fileField)) {
            $stored = $this->storePublicImage($request->file($fileField), $folder);

            // Only drop the old file once the new one is safely on disk.
            if ($stored) {
                $this->deletePublicImage($current);

                return $stored;
            }
        }

        // The form no longer exposes a text box to blank out, so removing an
        // image is an explicit tick rather than "clear the field and save".
        if ($request->boolean($fileField . '_remove')) {
            $this->deletePublicImage($current);

            return null;
        }

        // No upload: keep whatever the form carried over (the hidden field
        // holds the stored value, so an untouched save is a no-op).
        return filled($request->input($textField)) ? trim($request->input($textField)) : null;
    }

    /**
     * The value currently stored for an image column.
     *
     * BaseRepository::data() only receives the request, so on update we look
     * the row up by the id the edit form posts. Returns null when creating.
     */
    protected function currentImage($request, string $column): ?string
    {
        if (! filled($request->id ?? null) || ! isset($this->model)) {
            return null;
        }

        return $this->model->newQuery()->whereKey($request->id)->value($column);
    }

    /** Move an uploaded file into public/uploads/{folder} and return its path. */
    protected function storePublicImage($file, string $folder): ?string
    {
        if (! $file || ! $file->isValid()) {
            return null;
        }

        $directory = public_path("uploads/{$folder}");

        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $name = Str::slug($folder) . '_' . now()->format('YmdHis') . '_' . Str::random(6)
            . '.' . $file->getClientOriginalExtension();

        $file->move($directory, $name);

        return "uploads/{$folder}/{$name}";
    }

    /** Remove a previously uploaded file. External URLs are left alone. */
    protected function deletePublicImage(?string $path): void
    {
        $path = trim((string) $path);

        if ($path === '' || Str::startsWith($path, ['http://', 'https://', 'data:'])) {
            return;
        }

        $full = public_path(ltrim($path, '/'));

        if (File::exists($full) && File::isFile($full)) {
            File::delete($full);
        }
    }
}
