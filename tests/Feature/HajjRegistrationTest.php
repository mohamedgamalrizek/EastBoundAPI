<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\HajjPackage;
use App\Models\HajjPilgrim;
use App\Models\Invoice;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Public Hajj / Umrah registration.
 *
 * The form used to write a CRM lead and nothing else: the Hajj module never
 * saw the registration, and the answers it collected (Economy / Standard /
 * Premium, a month typed as free text) matched nothing the module stores.
 * These tests hold the fix — a registration is a pilgrim, on a real package,
 * owing the package price.
 */
class HajjRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function package(): HajjPackage
    {
        return HajjPackage::where('status', 'active')->where('type', 'Hajj')->firstOrFail();
    }

    private function payload(HajjPackage $package, array $overrides = []): array
    {
        return array_merge([
            'hajj_package_id' => $package->id,
            'passport_no'     => 'BD9988776',
            'name'            => 'Kamrul Hasan',
            'phone'           => '01822334455',
            'email'           => 'kamrul@example.com',
            'pilgrims'        => 2,
            'preferred_month' => 'Ramadan 2027',
            'room_sharing'    => 'Triple',
            'details'         => 'Wheelchair assistance needed.',
        ], $overrides);
    }

    public function test_the_registration_form_offers_the_real_packages(): void
    {
        $package = $this->package();

        $this->get(route('front.book', 'hajj'))
            ->assertOk()
            ->assertSee($package->title)
            ->assertSee('passport_no', false)
            ->assertSee('hajj_package_id', false);
    }

    public function test_a_website_registration_reaches_the_hajj_module(): void
    {
        $package = $this->package();
        $seats   = (int) $package->seats;

        $this->post(route('front.book.request', 'hajj'), $this->payload($package))
            ->assertRedirect(route('front.book', 'hajj'))
            ->assertSessionHas('success');

        $pilgrim = HajjPilgrim::where('passport_no', 'BD9988776')->first();

        $this->assertNotNull($pilgrim, 'The registration must create a pilgrim, not only a lead.');
        $this->assertSame('Kamrul Hasan', $pilgrim->name);
        $this->assertSame($package->id, $pilgrim->hajj_package_id);
        $this->assertSame($package->title, $pilgrim->package_title);
        $this->assertSame('Registered', $pilgrim->status);

        // The money matches the package, and the billing observer invoiced it.
        $this->assertEqualsWithDelta((float) $package->price, (float) $pilgrim->amount_due, 0.01);
        $this->assertNotNull(
            Invoice::where('source_type', $pilgrim->getMorphClass())->where('source_id', $pilgrim->id)->first(),
            'A registration owing money should carry an invoice.'
        );

        // A seat is gone, the customer exists, and the preferences reached CRM.
        $this->assertSame($seats - 1, (int) $package->fresh()->seats);
        $this->assertNotNull(Customer::where('phone', '01822334455')->first());

        $lead = Lead::where('interest', 'Hajj Registration')->latest('id')->first();
        $this->assertNotNull($lead);
        $this->assertStringContainsString('Ramadan 2027', $lead->notes);
        $this->assertStringContainsString($pilgrim->pilgrim_no, $lead->notes);
    }

    public function test_the_package_and_passport_are_required(): void
    {
        $this->post(route('front.book.request', 'hajj'), [
            'name'  => 'No Package',
            'phone' => '01700000001',
        ])->assertSessionHasErrors(['hajj_package_id', 'passport_no']);

        $this->assertSame(0, HajjPilgrim::where('name', 'No Package')->count());
    }

    public function test_the_same_customer_cannot_register_twice_for_one_package(): void
    {
        $package = $this->package();

        $this->post(route('front.book.request', 'hajj'), $this->payload($package))->assertRedirect();
        $this->post(route('front.book.request', 'hajj'), $this->payload($package))
            ->assertSessionHas('danger');

        $this->assertSame(1, HajjPilgrim::where('passport_no', 'BD9988776')->count());
    }

    public function test_an_umrah_package_cannot_be_registered_through_the_hajj_form(): void
    {
        $umrah = HajjPackage::where('status', 'active')->where('type', 'Umrah')->firstOrFail();

        $this->post(route('front.book.request', 'hajj'), $this->payload($umrah, ['passport_no' => 'BD5544332']))
            ->assertSessionHas('danger');

        $this->assertSame(0, HajjPilgrim::where('passport_no', 'BD5544332')->count());
    }

    public function test_other_booking_types_still_only_raise_a_lead(): void
    {
        $this->post(route('front.book.request', 'hotel'), [
            'name'  => 'Hotel Enquiry',
            'phone' => '01700000002',
            'city'  => "Cox's Bazar",
        ])->assertRedirect();

        $this->assertNotNull(Lead::where('name', 'Hotel Enquiry')->first());
        $this->assertSame(0, HajjPilgrim::where('name', 'Hotel Enquiry')->count());
    }
}
