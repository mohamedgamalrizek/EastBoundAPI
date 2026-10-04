<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;

trait ReturnFormatTrait
{

    protected function responseWithSuccess($message = '', $data = [], int $status_code = 200)
    {
        return [
            'status'        => true,
            'message'       => $message,
            'data'          => $data,
            'status_code'   => $status_code,
        ];
    }

    protected function responseWithError($message = '', $data = [], int $status_code = 400)
    {
        return [
            'status'        => false,
            'message'       => $message,
            'data'          => $data,
            'status_code'   => $status_code,
        ];
    }
}
