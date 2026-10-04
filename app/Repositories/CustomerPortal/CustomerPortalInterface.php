<?php

namespace App\Repositories\CustomerPortal;

interface CustomerPortalInterface
{
    public function dashboard();

    public function storeTicket($request);

    public function passport();

    public function travelers();

    // ---- Self-service: Travelers ----
    public function findOwnTraveler($id);

    public function storeTraveler($request);

    public function updateTraveler($request);

    public function deleteTraveler($id);

    // ---- Self-service: Passports ----
    public function findOwnPassport($id);

    public function storePassport($request);

    public function updatePassport($request);

    public function deletePassport($id);

    public function bookings();

    public function tours();

    public function visa();

    /** A visa document owned by the signed-in customer, or null. */
    public function ownedVisaDocument($id);

    public function flights();

    public function hotels();

    public function transport();

    // ---- Self-service: booking & payment ----
    public function bookTour($request);

    /** Price a tour with a coupon/points before anything is saved. */
    public function quoteTour($request): array;

    // ---- Reviews ----
    public function reviews(): array;

    public function storeReview($request);

    /** A document (invoice|receipt|hotel) owned by the signed-in customer, or null. */
    public function ownedDocument(string $kind, $id);

    /** Online checkout for a Pay Now click; null when the method is offline. */
    public function beginOnlinePayment($request, string $kind): ?array;

    public function payBooking($request);

    public function bookHotel($request);

    public function payHotel($request);

    public function requestTransport($request);

    public function payTransport($request);

    public function invoices();

    public function payments();

    public function wallet();

    public function documents();

    public function support();

    public function settings();

    public function updateSettings($request);

    // ---- Wishlist (saved tour packages) ----
    public function wishlist();

    /** Add/remove a package from the signed-in customer's wishlist. */
    public function toggleWishlist($packageId): array;

    public function removeWishlist($id);

    /** Package ids the signed-in visitor has saved, without creating a customer record for a mere browse. */
    public function wishlistedPackageIds(): array;
}
