<?php

namespace App\Repositories\User;

interface UserInterface
{

    public function all(int $paginate = null, bool $status = null, array $filters = []);
    public function get($id);

    public function store($request);
    public function update($request);
    public function delete($id);

    public function permissionUpdate($request);

    public function profileUpdate($request);
    public function passwordUpdate($request);

    public function passwordReset($request);

    public function signup($request);

    public function verifyToken($request);

    public function resendToken($request);

    public function passwordResetToken($request);
}
