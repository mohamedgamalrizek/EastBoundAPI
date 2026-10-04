<?php

namespace App\Console\Commands;

use App\Models\VisaDocument;
use App\Repositories\Visa\VisaRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * One-time cleanup for installs that were already running before visa
 * documents moved off the "public" disk.
 *
 * Any visa_documents.file_path saved before this change was written under
 * storage/app/public — symlinked to public/storage, so the file sat behind a
 * guessable/scrapable URL with no login required. This command copies each
 * of those files onto the "local" disk (storage/app, no public URL) at the
 * same relative path, then deletes the public copy once the copy is
 * confirmed. VisaDocument::file_path itself never changes format — it was
 * always disk-relative — so no data migration is needed, only the bytes on
 * disk move.
 *
 * Safe to run more than once: a document already off the public disk (or
 * missing from both disks) is left alone and reported, not touched.
 */
class MovePrivateDocuments extends Command
{
    protected $signature = 'flow:move-private-documents {--dry-run : List what would move without touching any file}';

    protected $description = 'Move visa documents saved under the public disk onto the private (local) disk';

    public function handle(): int
    {
        $disk   = VisaRepository::DOCUMENT_DISK;
        $dryRun = (bool) $this->option('dry-run');

        $moved = 0;
        $skipped = 0;
        $missing = 0;

        VisaDocument::query()->orderBy('id')->chunkById(100, function ($documents) use ($disk, $dryRun, &$moved, &$skipped, &$missing) {
            foreach ($documents as $document) {
                $path = $document->file_path;

                if (! $path) {
                    continue;
                }

                if (! Storage::disk('public')->exists($path)) {
                    // Already moved (or never was on the public disk) — the
                    // download route resolves it against the local disk now,
                    // so only report anything that is missing from both.
                    if (! Storage::disk($disk)->exists($path)) {
                        $missing++;
                        $this->warn("Missing on both disks, document #{$document->id}: {$path}");
                    } else {
                        $skipped++;
                    }

                    continue;
                }

                if ($dryRun) {
                    $this->line("Would move document #{$document->id}: {$path}");
                    $moved++;

                    continue;
                }

                $contents = Storage::disk('public')->get($path);
                Storage::disk($disk)->put($path, $contents);

                // Only remove the public copy once the private copy is
                // confirmed on disk, so a failed write never loses the file.
                if (Storage::disk($disk)->exists($path)) {
                    Storage::disk('public')->delete($path);
                    $moved++;
                    $this->line("Moved document #{$document->id}: {$path}");
                } else {
                    $this->error("Copy failed, left in place, document #{$document->id}: {$path}");
                }
            }
        });

        $this->newLine();
        $this->info(($dryRun ? '[dry run] ' : '') . "Moved: {$moved}, already private: {$skipped}, missing: {$missing}.");

        return self::SUCCESS;
    }
}
