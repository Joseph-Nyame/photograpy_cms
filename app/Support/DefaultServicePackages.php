<?php

namespace App\Support;

final class DefaultServicePackages
{
    public static function all(): array
    {
        return [
            'portrait-session' => [
                [
                    'name' => 'Essential',
                    'items' => [
                        ['label' => '60-minute shoot', 'value' => 800],
                        ['label' => 'One location in Accra', 'value' => null],
                        ['label' => '20 edited images', 'value' => null],
                        ['label' => 'Private online gallery', 'value' => null],
                    ],
                ],
                [
                    'name' => 'Signature',
                    'items' => [
                        ['label' => '90-minute shoot', 'value' => 1200],
                        ['label' => 'Two locations', 'value' => null],
                        ['label' => '40 edited images', 'value' => null],
                        ['label' => 'Private online gallery', 'value' => null],
                    ],
                ],
            ],
            'event-coverage' => [
                [
                    'name' => 'Essential',
                    'items' => [
                        ['label' => 'Up to 4 hours', 'value' => 2500],
                        ['label' => 'Two photographers', 'value' => null],
                        ['label' => '80+ edited images', 'value' => null],
                        ['label' => 'Private online gallery', 'value' => null],
                    ],
                ],
                [
                    'name' => 'Plus',
                    'items' => [
                        ['label' => 'Up to 6 hours', 'value' => 3600],
                        ['label' => 'Two photographers', 'value' => null],
                        ['label' => '150+ edited images', 'value' => null],
                        ['label' => 'Same-week delivery', 'value' => null],
                    ],
                ],
            ],
            'wedding-day' => [
                [
                    'name' => 'Classic',
                    'items' => [
                        ['label' => 'Full-day coverage', 'value' => 6000],
                        ['label' => 'Two photographers', 'value' => null],
                        ['label' => '400+ edited images', 'value' => null],
                        ['label' => 'Printed 30-page album', 'value' => null],
                    ],
                ],
                [
                    'name' => 'Signature',
                    'items' => [
                        ['label' => 'Full day plus engagement session', 'value' => 8500],
                        ['label' => 'Two photographers', 'value' => null],
                        ['label' => '500+ edited images', 'value' => null],
                        ['label' => 'Printed 40-page album', 'value' => null],
                    ],
                ],
            ],
            'football-matchday' => [
                [
                    'name' => 'Matchday',
                    'items' => [
                        ['label' => '3 hours of coverage', 'value' => 1800],
                        ['label' => 'Match action', 'value' => null],
                        ['label' => 'Touchline reactions', 'value' => null],
                        ['label' => 'Team portraits', 'value' => null],
                    ],
                ],
            ],
        ];
    }

    public static function for(string $slug): array
    {
        return self::all()[$slug] ?? [];
    }
}
