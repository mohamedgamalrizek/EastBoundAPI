<?php

namespace App\Repositories\AgentPortal;

interface AgentPortalInterface
{
    public function dashboard();

    public function bookings();

    public function bookingFormData();

    public function storeBooking($request);

    public function booking($id);

    public function updateBooking($request, $id);

    public function cancelBooking($id);

    public function customers();

    /** A visa document belonging to one of this agent's own customers, or null. */
    public function ownedVisaDocument($id);

    public function commissions();

    public function wallet();

    public function transactions();

    public function invoices();

    public function reports();

    public function support();
}
