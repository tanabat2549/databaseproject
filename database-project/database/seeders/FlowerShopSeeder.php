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
            ['Red Rose Bouquet', 'bouquet', 'A dozen fresh red roses, wrapped in kraft paper.', 890, 30,'https://cdn.shopify.com/s/files/1/0507/3754/5401/files/FE81_LAY_preset_ftd-mx-hero-lv-lay.jpg?v=1724708935'],
            ['Pastel Mixed Bouquet', 'bouquet', 'Soft pink and white blooms with eucalyptus.', 650, 25, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSwhvCzh1_d_0yWR6zl7U9cLjVGfg65Xs8krRpx9_1lAk-LIlwVbU0pd0UV&s=10'],
            ['Sunflower Bouquet', 'bouquet', 'Bright sunflowers to make any day happier.', 590, 20, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSyHuUWfyworOMt7UHEIVB4pQJ86DfPnh_0W5yz2sbP0DcBkLT72gRnTgQ&s=10'],
            ['Tulip Bouquet', 'bouquet', 'Ten Dutch tulips in spring colours.', 720, 15, 'https://www.glasshouseflorist.com/905-superlarge_default/rainbow-tulips-bouquet-round.jpg'],
            ['Single Red Rose', 'single', 'One long-stemmed red rose.', 79, 100, 'https://instamart-media-assets.swiggy.com/swiggy/image/upload/fl_lossy,f_auto,q_auto/NI_CATALOG/IMAGES/CIW/2024/12/18/86d6b10f-4b46-4212-a788-85c58c6f82ff_9540_2.png'],
            ['Single Sunflower', 'single', 'One large sunflower.', 59, 80, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRndfWHaO0I1bm09PXc-cbQlPFbMz-I-FbJYaAxlnxvWkO_hECrgqapvxE&s=10'],
            ['Single White Lily', 'single', 'A fragrant white lily stem.', 89, 60, 'https://freshknots.in/wp-content/uploads/2025/09/ElegantLilyWrap2.jpg'],
            ['Potted Orchid', 'potted', 'Phalaenopsis orchid in a ceramic pot.', 450, 18, 'https://assets.digitalcontent.marksandspencer.app/images/w_2560,q_auto,f_auto/SD_FD_F44A_29126446_NC_X_EC_0/White-Phalaenopsis-Orchid-in-Ceramic-Pot'],
            ['Potted Lavender', 'potted', 'Lavender plant in a terracotta pot.', 250, 22, 'https://assets.digitalcontent.marksandspencer.app/images/w_2560,q_auto,f_auto/SD_FD_F44A_29527205_NC_X_EC_0/Lavender-Plant-in-Terracotta-Pot'],
            ['Mini Cactus Set', 'potted', 'Set of three mini cacti.', 199, 40, 'https://urbanjngl.com/cdn/shop/files/Cacti-plants-set-Cactus-with-pot.jpg?v=1770307101'],
            ['Eucalyptus Wreath', 'wreath', 'Handmade eucalyptus door wreath.', 790, 10, 'https://thebritishcrafthouse.co.uk/wp-content/uploads/2026/07/20260723_190604-scaled.jpg'],
            ['Dried Flower Wreath', 'wreath', 'Long-lasting dried flower wreath.', 950, 8, 'https://images.urbndata.com/is/image/Anthropologie/101815454_089_b?$a15-pdp-detail-shot$&fit=constrain&qlt=80&wid=640'],
        ])->mapWithKeys(fn ($p) => [$p[0] => Product::create([
            'name' => $p[0],
            'category' => $p[1],
            'description' => $p[2],
            'price' => $p[3],
            'stock' => $p[4],
            'image' => $p[5] ?? null,
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
