<?php

namespace Database\Seeders;

use App\Models\VisaApplication;
use App\Models\VisaDocument;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Demo uploads for the Visa Documents screen. Each row gets a real (tiny)
 * placeholder file on the public disk so Download actually returns a file
 * instead of 404-ing.
 */
class VisaDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $applications = VisaApplication::orderBy('id')->get()->keyBy('application_no');

        if ($applications->isEmpty()) {
            return;
        }

        // [application_no, document type, file name, status]
        $rows = [
            ['VISA-3301', 'Passport',        'passport.pdf',       'Verified'],
            ['VISA-3301', 'Photo',           'photo.jpg',          'Verified'],
            ['VISA-3301', 'Bank Statement',  'bank-statement.pdf', 'Submitted'],
            ['VISA-3300', 'Passport',        'passport.pdf',       'Verified'],
            ['VISA-3300', 'Bank Statement',  'bank.pdf',           'Verified'],
            ['VISA-3300', 'Invitation Letter', 'invite.pdf',       'Verified'],
            ['VISA-3299', 'Passport',        'passport.pdf',       'Submitted'],
            ['VISA-3299', 'Photo',           'photo.jpg',          'Submitted'],
            ['VISA-3297', 'Passport',        'passport.pdf',       'Verified'],
            ['VISA-3297', 'Insurance',       'insurance.pdf',      'Verified'],
            ['VISA-3295', 'Passport',        'passport.pdf',       'Submitted'],
            ['VISA-3295', 'Admission Letter', 'admission.pdf',     'Submitted'],
            ['VISA-3294', 'Passport',        'passport.pdf',       'Verified'],
        ];

        foreach ($rows as $i => $r) {
            $application = $applications->get($r[0]);

            if (! $application) {
                continue;
            }

            $isImage   = str_ends_with($r[2], '.jpg');
            $extension = $isImage ? 'jpg' : 'pdf';
            $mime      = $isImage ? 'image/jpeg' : 'application/pdf';
            $path      = "visa-documents/{$application->id}/seed-{$i}.{$extension}";

            Storage::disk('public')->put(
                $path,
                $isImage ? $this->placeholderJpg() : $this->placeholderPdf($r[1])
            );

            VisaDocument::updateOrCreate(
                [
                    'visa_application_id' => $application->id,
                    'document_type'       => $r[1],
                ],
                [
                    'file_name'   => $r[2],
                    'file_path'   => $path,
                    'mime_type'   => $mime,
                    'status'      => $r[3],
                    'uploaded_at' => $application->applied_date ?? now(),
                ]
            );
        }
    }

    /** Smallest valid single-page PDF, with the document type as its title. */
    private function placeholderPdf(string $title): string
    {
        return "%PDF-1.4\n"
            . "1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n"
            . "2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n"
            . "3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 595 842]>>endobj\n"
            . "trailer<</Root 1 0 R/Info<</Title(" . str_replace(['(', ')'], '', $title) . " - sample)>>>>\n"
            . "%%EOF\n";
    }

    /** 1x1 grey JPEG. */
    private function placeholderJpg(): string
    {
        return base64_decode(
            '/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0a'
            . 'HBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/wAALCAABAAEBAREA/8QAFAABAAAAAAAA'
            . 'AAAAAAAAAAAACf/EABQQAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQEAAD8AKp//2Q=='
        );
    }
}
