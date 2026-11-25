<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\PortfolioImage;
use Illuminate\Database\Seeder;

class PortfolioImageSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'weddings' => [
                'Ama & Kojo under the palms',
                'Vows in the family garden',
                'The quiet preparation',
                'A first look in East Legon',
                'Confetti on the aisle',
                'The reception table',
                'Dancing with the bridal party',
                'A portrait at golden hour',
                'The last dance',
            ],
            'events' => [
                'Guests at the Labadi launch',
                'A keynote in warm light',
                'Conversations between sessions',
                'The product reveal',
                'Applause from the front row',
                'A candid during the break',
                'The room before doors opened',
                'Speakers on stage',
                'The closing toast',
            ],
            'football' => [
                'Matchday action at the touchline',
                'A challenge in midfield',
                'The keeper before kick-off',
                'Supporters in the stands',
                'A goal celebration',
                'The team huddle',
                'A portrait after full time',
                'Rain on the pitch',
                'The final whistle',
            ],
            'birthdays' => [
                'Sparklers over the cake',
                'The birthday portrait',
                'A table full of friends',
                'The first slice',
                'Dancing in the garden',
                'A quiet moment with family',
                'Confetti in the courtyard',
                'The group photograph',
                'The last song',
            ],
            'portraits' => [
                'Golden hour in Jamestown',
                'A studio portrait in warm paper',
                'A portrait against the sea wall',
                'Window light in East Legon',
                'A quiet street portrait',
                'A portrait in the studio doorway',
                'Movement and shadow',
                'A close portrait',
                'The final frame of the session',
            ],
        ];

        foreach ($categories as $slug => $alts) {
            $category = Category::query()->where('slug', $slug)->firstOrFail();

            foreach ($alts as $index => $alt) {
                $image = PortfolioImage::updateOrCreate(
                    [
                        'category_id' => $category->id,
                        'path' => sprintf('portfolio/%s/%02d.jpg', $slug, $index + 1),
                    ],
                    [
                        'alt' => $alt,
                        'sort' => $index + 1,
                        'featured' => $index === 0,
                    ],
                );

                if ($index === 0) {
                    $category->update(['cover_image_id' => $image->id]);
                }
            }
        }
    }
}
