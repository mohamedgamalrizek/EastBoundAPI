<?php

namespace App\Enums;

enum SmsGatewayProvider: string
{
    case MimSMS = 'MimSMS';

    public function label(): string
    {
        return ___("label.{$this->name}"); // label from name of case
    }

    public static function options(): array
    {
        return array_combine(
            array_map(fn ($case) => $case->value, self::cases()),
            array_map(fn ($case) => $case->label(), self::cases())
        );
    }
}
