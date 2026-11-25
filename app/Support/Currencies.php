<?php

namespace App\Support;

final class Currencies
{
    public const CODES = ['GHS', 'USD', 'EUR', 'GBP', 'NGN', 'KES', 'ZAR', 'CAD', 'AUD'];

    /**
     * @return array<int, string>
     */
    public static function codes(): array
    {
        return self::CODES;
    }

    /**
     * @return array<int, array{code: string, label: string}>
     */
    public static function options(): array
    {
        return [
            ['code' => 'GHS', 'label' => 'Ghana Cedi'],
            ['code' => 'USD', 'label' => 'US Dollar'],
            ['code' => 'EUR', 'label' => 'Euro'],
            ['code' => 'GBP', 'label' => 'British Pound'],
            ['code' => 'NGN', 'label' => 'Nigerian Naira'],
            ['code' => 'KES', 'label' => 'Kenyan Shilling'],
            ['code' => 'ZAR', 'label' => 'South African Rand'],
            ['code' => 'CAD', 'label' => 'Canadian Dollar'],
            ['code' => 'AUD', 'label' => 'Australian Dollar'],
        ];
    }
}
