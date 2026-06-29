<?php

namespace Database\Seeders;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\Meal;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Otp;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->clearFoodifyTables();

        $users = $this->seedUsers();
        $categories = $this->seedCategories();
        $meals = $this->seedMeals($categories);
        $paymentMethods = $this->seedPaymentMethods($users);

        $this->seedOtps($users);
        $this->seedFavorites($users, $meals);
        $this->seedCartItems($users, $meals);
        $orders = $this->seedOrders($users, $meals, $paymentMethods);
        $this->seedNotifications($users, $orders);
    }

    private function clearFoodifyTables(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ([
            'notifications',
            'payments',
            'order_items',
            'orders',
            'payment_methods',
            'cart_items',
            'favorites',
            'meals',
            'categories',
            'otps',
            'users',
        ] as $table) {
            DB::table($table)->truncate();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function seedUsers()
    {
        $users = collect([
            User::query()->create([
                'full_name' => 'Foodify Test User',
                'phone' => '+201001234567',
                'email' => 'test@example.com',
                'password' => Hash::make('password123'),
                'birth_date' => '1998-05-15',
                'address' => 'Nasr City, Cairo',
                'image' => 'https://placehold.co/300x300?text=User',
                'phone_verified_at' => now(),
                'is_active' => true,
            ]),
        ]);

        for ($i = 1; $i <= 7; $i++) {
            $users->push(User::query()->create([
                'full_name' => fake()->name(),
                'phone' => '+20'.fake()->unique()->numerify('1#########'),
                'email' => fake()->unique()->safeEmail(),
                'password' => Hash::make('password123'),
                'birth_date' => fake()->date('Y-m-d', '-18 years'),
                'address' => fake()->streetAddress().', Cairo',
                'image' => 'https://placehold.co/300x300?text=User+'.$i,
                'phone_verified_at' => now()->subDays(random_int(1, 30)),
                'is_active' => true,
            ]));
        }

        return $users;
    }

    /**
     * @return \Illuminate\Support\Collection<int, Category>
     */
    private function seedCategories()
    {
        return collect([
            ['name' => 'Salads', 'image' => 'https://placehold.co/600x400?text=Salads'],
            ['name' => 'Bowls', 'image' => 'https://placehold.co/600x400?text=Bowls'],
            ['name' => 'Sandwiches', 'image' => 'https://placehold.co/600x400?text=Sandwiches'],
            ['name' => 'Pasta', 'image' => 'https://placehold.co/600x400?text=Pasta'],
            ['name' => 'Desserts', 'image' => 'https://placehold.co/600x400?text=Desserts'],
            ['name' => 'Drinks', 'image' => 'https://placehold.co/600x400?text=Drinks'],
        ])->map(fn (array $category): Category => Category::query()->create([
            ...$category,
            'is_active' => true,
        ]));
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Category>  $categories
     * @return \Illuminate\Support\Collection<int, Meal>
     */
    private function seedMeals($categories)
    {
        $mealNames = [
            'Salads' => ['Tuna Avocado Salad', 'Greek Chicken Salad', 'Quinoa Power Salad'],
            'Bowls' => ['Teriyaki Chicken Bowl', 'Beef Rice Bowl', 'Vegan Buddha Bowl'],
            'Sandwiches' => ['Grilled Chicken Sandwich', 'Turkey Club Sandwich', 'Halloumi Veggie Wrap'],
            'Pasta' => ['Chicken Alfredo Pasta', 'Pesto Penne', 'Spicy Arrabbiata'],
            'Desserts' => ['Chocolate Protein Brownie', 'Greek Yogurt Parfait', 'Fruit Honey Cup'],
            'Drinks' => ['Mango Smoothie', 'Iced Matcha Latte', 'Fresh Orange Juice'],
        ];

        return $categories->flatMap(function (Category $category) use ($mealNames) {
            return collect($mealNames[$category->name])->map(function (string $name, int $index) use ($category): Meal {
                return Meal::query()->create([
                    'category_id' => $category->id,
                    'name' => $name,
                    'description' => fake()->sentence(14),
                    'image' => 'https://placehold.co/800x600?text='.urlencode($name),
                    'price' => fake()->randomFloat(2, 55, 240),
                    'nutrition' => [
                        'calories' => random_int(120, 650),
                        'protein' => random_int(8, 45),
                        'carbs' => random_int(10, 80),
                        'fat' => random_int(3, 35),
                        'fiber' => random_int(1, 12),
                    ],
                    'ingredients' => fake()->randomElements([
                        'chicken',
                        'beef',
                        'tuna',
                        'rice',
                        'pasta',
                        'lettuce',
                        'tomato',
                        'avocado',
                        'cheese',
                        'olive oil',
                        'spinach',
                        'mushroom',
                    ], random_int(4, 7)),
                    'rating' => fake()->randomFloat(1, 3.6, 5.0),
                    'is_recommended' => $index === 0 || fake()->boolean(35),
                    'is_available' => true,
                ]);
            });
        })->values();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, User>  $users
     * @return \Illuminate\Support\Collection<int, PaymentMethod>
     */
    private function seedPaymentMethods($users)
    {
        return $users->flatMap(function (User $user) {
            return collect([
                PaymentMethod::query()->create([
                    'user_id' => $user->id,
                    'type' => 'cash_on_delivery',
                    'is_default' => true,
                ]),
                PaymentMethod::query()->create([
                    'user_id' => $user->id,
                    'type' => 'card',
                    'card_brand' => fake()->randomElement(['Visa', 'Mastercard']),
                    'last_four' => fake()->numerify('####'),
                    'is_default' => false,
                ]),
            ]);
        })->values();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, User>  $users
     */
    private function seedOtps($users): void
    {
        foreach ($users->take(4) as $user) {
            Otp::query()->create([
                'phone' => $user->phone,
                'code' => (string) random_int(100000, 999999),
                'type' => fake()->randomElement(['register', 'forgot_password']),
                'expires_at' => now()->addMinutes(5),
                'verified_at' => fake()->boolean() ? now()->subMinute() : null,
                'is_used' => fake()->boolean(),
            ]);
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, User>  $users
     * @param  \Illuminate\Support\Collection<int, Meal>  $meals
     */
    private function seedFavorites($users, $meals): void
    {
        foreach ($users as $user) {
            foreach ($meals->random(4) as $meal) {
                Favorite::query()->create([
                    'user_id' => $user->id,
                    'meal_id' => $meal->id,
                ]);
            }
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, User>  $users
     * @param  \Illuminate\Support\Collection<int, Meal>  $meals
     */
    private function seedCartItems($users, $meals): void
    {
        foreach ($users->take(5) as $user) {
            foreach ($meals->random(3) as $meal) {
                CartItem::query()->create([
                    'user_id' => $user->id,
                    'meal_id' => $meal->id,
                    'quantity' => random_int(1, 3),
                    'unit_price' => $meal->price,
                ]);
            }
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, User>  $users
     * @param  \Illuminate\Support\Collection<int, Meal>  $meals
     * @param  \Illuminate\Support\Collection<int, PaymentMethod>  $paymentMethods
     * @return \Illuminate\Support\Collection<int, Order>
     */
    private function seedOrders($users, $meals, $paymentMethods)
    {
        $orders = collect();

        foreach ($users as $user) {
            for ($i = 1; $i <= 2; $i++) {
                $method = $paymentMethods->where('user_id', $user->id)->random();
                $selectedMeals = $meals->random(3);
                $subtotal = 0;

                $order = Order::query()->create([
                    'order_number' => 'FD'.now()->format('YmdHis').$user->id.$i.random_int(100, 999),
                    'user_id' => $user->id,
                    'payment_method_id' => $method->id,
                    'subtotal' => 0,
                    'delivery_fee' => 30,
                    'total' => 0,
                    'payment_status' => fake()->randomElement(['pending', 'paid', 'failed']),
                    'status' => fake()->randomElement(['pending', 'confirmed', 'preparing', 'on_the_way', 'delivered', 'cancelled']),
                    'delivery_address' => $user->address,
                    'estimated_delivery_time' => random_int(25, 60),
                    'created_at' => now()->subDays(random_int(1, 20)),
                    'updated_at' => now()->subDays(random_int(0, 5)),
                ]);

                foreach ($selectedMeals as $meal) {
                    $quantity = random_int(1, 3);
                    $lineTotal = round((float) $meal->price * $quantity, 2);
                    $subtotal += $lineTotal;

                    OrderItem::query()->create([
                        'order_id' => $order->id,
                        'meal_id' => $meal->id,
                        'meal_name' => $meal->name,
                        'meal_image' => $meal->image,
                        'quantity' => $quantity,
                        'unit_price' => $meal->price,
                        'total' => $lineTotal,
                    ]);
                }

                $order->update([
                    'subtotal' => $subtotal,
                    'total' => $subtotal + 30,
                ]);

                Payment::query()->create([
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'payment_method_id' => $method->id,
                    'amount' => $order->total,
                    'status' => $order->payment_status === 'paid' ? 'success' : fake()->randomElement(['pending', 'failed']),
                    'transaction_reference' => 'TXN-'.fake()->unique()->numerify('########'),
                    'paid_at' => $order->payment_status === 'paid' ? now()->subDays(random_int(1, 10)) : null,
                ]);

                $orders->push($order->fresh());
            }
        }

        return $orders;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, User>  $users
     * @param  \Illuminate\Support\Collection<int, Order>  $orders
     */
    private function seedNotifications($users, $orders): void
    {
        foreach ($users as $user) {
            $userOrders = $orders->where('user_id', $user->id)->values();

            Notification::query()->create([
                'user_id' => $user->id,
                'title' => 'Welcome to Foodify',
                'body' => 'Your account is ready. Explore fresh meals today.',
                'type' => 'system',
                'image' => null,
                'is_read' => false,
            ]);

            foreach ($userOrders as $order) {
                Notification::query()->create([
                    'user_id' => $user->id,
                    'title' => 'Order '.$order->status,
                    'body' => 'Your order '.$order->order_number.' is currently '.$order->status.'.',
                    'type' => 'order',
                    'image' => null,
                    'is_read' => fake()->boolean(45),
                ]);
            }

            Notification::query()->create([
                'user_id' => $user->id,
                'title' => 'Healthy pick for today',
                'body' => 'Try one of our high-protein recommended meals.',
                'type' => 'health_tip',
                'image' => 'https://placehold.co/600x400?text=Health+Tip',
                'is_read' => false,
            ]);
        }
    }
}
