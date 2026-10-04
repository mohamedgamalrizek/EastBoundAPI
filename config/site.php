<?php

use App\Enums\Status;
use App\Enums\Gender;
use App\Enums\TodoStatus;

return [


    'gender' => [
        Gender::OTHERS->value   => 'others',
        Gender::MALE->value     => 'male',
        Gender::FEMALE->value   => 'female',
    ],

    'status' => [

        'default' => [
            Status::INACTIVE->value  => 'inactive',
            Status::ACTIVE->value    => 'active',
        ],

        'Todo' => [
            TodoStatus::PENDING->value      => 'pending',
            TodoStatus::PROCESSING->value   => 'processing',
            TodoStatus::COMPLETED->value    => 'complete',
        ],
    ],

    'translations' => [
        'alert'         => 'Alert',
        'auth'          => 'Auth',
        'label'         => 'Label',
        'language'      => 'Language',
        'menus'         => 'Menus',
        'mobile'        => 'Mobile',
        'permissions'   => 'Permissions',
        'placeholder'   => 'Placeholder',
    ],

    'date_format' => [
        'M j, Y',
        'F d, Y',
        'j F Y',
        'm.d.y',
        'd-m-Y',
        'd/m/Y',
        'D M j Y',
        'jS F, Y (l)',
        'l, jS F Y',
    ],

    'time_format' => [
        'g:i a',
        'h:i:s A',
        'H:i:s',
    ],

];
