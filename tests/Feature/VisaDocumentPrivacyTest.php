<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use App\Models\VisaApplication;
use App\Models\VisaDocument;
use App\Repositories\Visa\VisaRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Passport scans and other visa paperwork used to be written to the "public"
 * disk (VisaRepository::uploadDocument), which is symlinked straight onto the
 * web root — so the file sat at a guessable/scrapable URL with no login at
 * all, defeating the authenticated download route entirely.
 *
 * These pin the fix: nothing lands on the public disk, and every way to fetch
 * a document — back office, and the customer's own API — checks who is
 * asking before it checks the disk.
 */
class VisaDocumentPrivacyTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private static int $seq = 0;

    private function admin(): User
    {
        return User::where('email', 'superadmin@bugbuild.com')->firstOrFail();
    }

    private function staffWithoutVisaAccess(): User
    {
        // 'permissions' isn't mass-assignable (see User::$fillable), and left
        // null it would crash EnsurePermission's in_array() — so it's set
        // directly, same as UserRepository::store() does for a real signup.
        $user             = new User([
            'name'     => 'No Access Staff',
            'email'    => 'no-visa-access@example.com',
            'password' => Hash::make('secret123'),
        ]);
        $user->permissions = [];
        $user->save();

        return $user;
    }

    private function application(?int $customerId = null): VisaApplication
    {
        self::$seq++;

        return VisaApplication::create([
            'customer_id'    => $customerId,
            'application_no' => 'VISA-PRIV-' . self::$seq,
            'applicant_name' => 'Document Privacy Test',
            'country'        => 'UAE',
            'visa_type'      => 'Tourist',
        ]);
    }

    private function customer(string $phone): Customer
    {
        return Customer::create([
            'name'     => 'Doc Owner ' . $phone,
            'phone'    => $phone,
            'email'    => $phone . '@example.com',
            'password' => 'secret123',
            'status'   => 'active',
        ]);
    }

    /** Upload a document the same way the backend form does, and return it. */
    private function uploadDocument(VisaApplication $application): VisaDocument
    {
        $this->actingAs($this->admin())
            ->post(route('visa.documents.store'), [
                'visa_application_id' => $application->id,
                'document_type'       => 'Passport',
                'status'              => 'Submitted',
                'document'            => UploadedFile::fake()->create('passport.pdf', 200, 'application/pdf'),
            ])
            ->assertRedirect(route('visa.documents'))
            ->assertSessionHas('success');

        return VisaDocument::where('visa_application_id', $application->id)->latest('id')->firstOrFail();
    }

    /**
     * A document already sitting on the private disk, created directly rather
     * than through the authenticated upload endpoint. The route-gating tests
     * below only care what a request needs before it can read the file — not
     * how the file got there — and creating it this way keeps them from
     * inheriting the admin session that uploadDocument() leaves behind
     * (actingAs() sticks for the rest of the test once called).
     */
    private function seedDocument(VisaApplication $application): VisaDocument
    {
        $path = "visa-documents/{$application->id}/" . \Illuminate\Support\Str::uuid() . '.pdf';
        Storage::disk(VisaRepository::DOCUMENT_DISK)->put($path, '%PDF-1.4 fake visa document');

        return VisaDocument::create([
            'visa_application_id' => $application->id,
            'document_type'       => 'Passport',
            'file_name'           => 'passport.pdf',
            'file_path'           => $path,
            'mime_type'           => 'application/pdf',
            'status'              => 'Submitted',
            'uploaded_at'         => now(),
        ]);
    }

    public function test_an_uploaded_document_never_lands_on_the_public_disk(): void
    {
        $document = $this->uploadDocument($this->application());

        $this->assertTrue(
            Storage::disk('public')->missing($document->file_path),
            'The document must not exist on the public disk at all.'
        );
        $this->assertTrue(
            Storage::disk(VisaRepository::DOCUMENT_DISK)->exists($document->file_path),
            'The document must exist on the private (local) disk.'
        );

        // The public disk is what public/storage is symlinked to — proving it
        // is absent there is the same as proving no public URL serves it.
        $this->assertFileDoesNotExist(storage_path('app/public/' . $document->file_path));
    }

    public function test_the_backend_download_route_requires_authentication(): void
    {
        $document = $this->seedDocument($this->application());

        // Guest: the outer 'auth' middleware redirects to login before the
        // permission check (or the route) is ever reached.
        $this->get(route('visa.documents.download', $document->id))
            ->assertRedirect();
        $this->assertGuest();
    }

    public function test_the_backend_download_route_requires_the_visa_read_permission(): void
    {
        $document = $this->seedDocument($this->application());

        $this->actingAs($this->staffWithoutVisaAccess())
            ->get(route('visa.documents.download', $document->id))
            ->assertForbidden();
    }

    public function test_staff_with_visa_read_can_download_the_document(): void
    {
        $document = $this->seedDocument($this->application());

        $response = $this->actingAs($this->admin())
            ->get(route('visa.documents.download', $document->id));

        $response->assertOk();
        $response->assertDownload();
    }

    public function test_a_customer_can_download_their_own_visa_document_via_the_api(): void
    {
        $owner       = $this->customer('01788000111');
        $document    = $this->seedDocument($this->application($owner->id));

        $response = $this->actingAs($owner, 'sanctum')
            ->get('/api/v1/visa/documents/' . $document->id . '/download');

        $response->assertOk();
        $response->assertDownload();
    }

    public function test_a_customer_cannot_download_another_customers_visa_document_via_the_api(): void
    {
        $owner    = $this->customer('01788000222');
        $outsider = $this->customer('01788000333');
        $document = $this->seedDocument($this->application($owner->id));

        $this->actingAs($outsider, 'sanctum')
            ->getJson('/api/v1/visa/documents/' . $document->id . '/download')
            ->assertStatus(404);
    }

    public function test_the_customer_api_lists_document_metadata_but_never_a_file_path(): void
    {
        $owner    = $this->customer('01788000444');
        $document = $this->seedDocument($this->application($owner->id));

        $data = $this->actingAs($owner, 'sanctum')
            ->getJson('/api/v1/visa/' . $document->visa_application_id)
            ->assertOk()
            ->json('data');

        $this->assertSame($document->id, $data['documents'][0]['id']);
        $this->assertArrayNotHasKey('file_path', $data['documents'][0]);
    }
}
