 <?php

namespace Database\Seeders;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FlowerShopSeeder extends Seeder
{
    public function run(): void
    {
        // Safe to re-run: wipe shop tables (children first because of FK constraints)
        DB::transaction(function () {
            foreach (['reviews', 'wishlists', 'cart_items', 'order_items', 'orders', 'user_phones', 'products'] as $table) {
                DB::table($table)->delete();
            }
        });

        // Users (password for everyone: "password")
        $users = [];
        foreach ([
            ['Test User', 'test@example.com'],
            ['Somchai Jaidee', 'somchai@example.com'],
            ['Malee Suksan', 'malee@example.com'],
            ['Anan Rakdee', 'anan@example.com'],
        ] as [$name, $email]) {
            $users[] = User::firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => 'password', 'email_verified_at' => now()],
            );
        }
        [$test, $somchai, $malee, $anan] = $users;

        // Phones (multi-valued attribute)
        $now = now();
        DB::table('user_phones')->insert([
            ['uid' => $test->id, 'phone_no' => '0811111111', 'created_at' => $now, 'updated_at' => $now],
            ['uid' => $somchai->id, 'phone_no' => '0822222222', 'created_at' => $now, 'updated_at' => $now],
            ['uid' => $somchai->id, 'phone_no' => '0833333333', 'created_at' => $now, 'updated_at' => $now],
            ['uid' => $malee->id, 'phone_no' => '0844444444', 'created_at' => $now, 'updated_at' => $now],
            ['uid' => $anan->id, 'phone_no' => '0855555555', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // Products, keyed by name
        $products = collect([
            ['Red Rose Bouquet', 'bouquet', 'A dozen fresh red roses, wrapped in kraft paper.', 890, 30],
            ['Pastel Mixed Bouquet', 'bouquet', 'Soft pink and white blooms with eucalyptus.', 650, 25],
            ['Sunflower Bouquet', 'bouquet', 'Bright sunflowers to make any day happier.', 590, 20],
            ['Tulip Bouquet', 'bouquet', 'Ten Dutch tulips in spring colours.', 720, 15],
            ['Single Red Rose', 'single', 'One long-stemmed red rose.', 79, 100],
            ['Single Sunflower', 'single', 'One large sunflower.', 59, 80],
            ['Single White Lily', 'single', 'A fragrant white lily stem.', 89, 60],
            ['Potted Orchid', 'potted', 'Phalaenopsis orchid in a ceramic pot.', 450, 18],
            ['Potted Lavender', 'potted', 'Lavender plant in a terracotta pot.', 250, 22],
            ['Mini Cactus Set', 'potted', 'Set of three mini cacti.', 199, 40],
            ['Eucalyptus Wreath', 'wreath', 'Handmade eucalyptus door wreath.', 790, 10],
            ['Dried Flower Wreath', 'wreath', 'Long-lasting dried flower wreath.', 950, 8],
        ])->mapWithKeys(fn ($p) => [$p[0] => Product::create([
            'name' => $p[0],
            'category' => $p[1],
            'description' => $p[2],
            'price' => $p[3],
            'image' => null,
            'stock' => $p[4],
        ])]);

        // Carts
        foreach ([
            [$test, 'Red Rose Bouquet', 1],
            [$test, 'Single Sunflower', 3],
            [$somchai, 'Potted Orchid', 1],
            [$malee, 'Tulip Bouquet', 2],
        ] as [$user, $product, $qty]) {
            CartItem::create(['uid' => $user->id, 'product_id' => $products[$product]->id, 'quantity' => $qty]);
        }

        // Wishlists
        foreach ([
            [$test, ['Eucalyptus Wreath', 'Potted Lavender']],
            [$somchai, ['Red Rose Bouquet', 'Tulip Bouquet', 'Single White Lily']],
            [$anan, ['Dried Flower Wreath']],
        ] as [$user, $names]) {
            foreach ($names as $name) {
                DB::table('wishlists')->insert([
                    'uid' => $user->id,
                    'product_id' => $products[$name]->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // Orders (unit_price is a snapshot of the product price at purchase time)
        foreach ([
            [$test, 'delivered', '-20 days', [['Red Rose Bouquet', 1], ['Single Red Rose', 2]]],
            [$test, 'pending', '+3 days', [['Pastel Mixed Bouquet', 1]]],
            [$somchai, 'delivered', '-12 days', [['Sunflower Bouquet', 2], ['Potted Lavender', 1]]],
            [$malee, 'delivered', '-7 days', [['Potted Orchid', 1]]],
            [$malee, 'pending', '+5 days', [['Eucalyptus Wreath', 1], ['Single White Lily', 3]]],
            [$anan, 'pending', '+2 days', [['Mini Cactus Set', 2]]],
        ] as [$user, $status, $delivery, $items]) {
            $total = collect($items)->sum(fn ($i) => $products[$i[0]]->price * $i[1]);

            $order = Order::create([
                'uid' => $user->id,
                'shipping_address' => "{$user->name}, 123 Flower Road, Chiang Mai 50200",
                'delivery_date' => now()->modify($delivery)->toDateString(),
                'total_amount' => $total,
                'status' => $status,
            ]);

            foreach ($items as [$name, $qty]) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $products[$name]->id,
                    'quantity' => $qty,
                    'unit_price' => $products[$name]->price,
                ]);
            }
        }

        // Reviews (rating 1-5 + comment)
        foreach ([
            [$test, 'Red Rose Bouquet', 5, 'Fresh and beautiful, arrived on time!'],
            [$somchai, 'Red Rose Bouquet', 4, 'Lovely, but a few petals were bruised.'],
            [$somchai, 'Sunflower Bouquet', 5, 'Made my mum smile.'],
            [$malee, 'Sunflower Bouquet', 4, null],
            [$malee, 'Potted Orchid', 5, 'Healthy plant, very elegant pot.'],
            [$test, 'Single Red Rose', 3, 'Nice, but a bit small.'],
            [$somchai, 'Potted Lavender', 2, 'Smelled great but the plant wilted in a week.'],
        ] as [$user, $name, $rating, $comment]) {
            Review::create([
                'uid' => $user->id,
                'product_id' => $products[$name]->id,
                'rating' => $rating,
                'comment' => $comment,
            ]);
        }
    }
}
