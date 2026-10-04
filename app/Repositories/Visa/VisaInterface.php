<?php

namespace App\Repositories\Visa;

interface VisaInterface
{
    // ---- CRUD ----
    public function all(array $filters = []);

    public function find($id);

    public function formData();

    public function store($request);

    public function update($request);

    public function delete($id);

    // ---- Visa management pages ----
    public function dashboard();

    public function applicationDetails($id);

    public function appointment();

    public function appointmentFormData();

    public function documents();

    public function documentFormData();

    public function documentTypes(): array;

    public function uploadDocument($request);

    public function updateDocument($request);

    public function deleteDocument($id);

    public function findDocument($id);

    public function bookAppointment($request);

    public function deleteAppointment($id);

    public function tracking();

    public function advance($request, $id);

    public function expiry();

    public function notifyExpiry($id);

    public function reports();
}
