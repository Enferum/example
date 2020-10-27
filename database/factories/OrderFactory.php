<?php
    
    /** @var Factory $factory */
    
    use App\Models\Order;
    use App\Models\Service;
    use App\Models\Slot;
    use App\Models\User;
    use Illuminate\Database\Eloquent\Factory;
    use Illuminate\Support\Arr;
    
    $factory->define(Order::class, function (\Faker\Generator $faker) {
        return [
            'code'         => $faker->unique()->randomNumber(4),
            'customer_id'  => User::inRandomOrder()->whereRole(User::ROLE_CLIENT)->first()->id,
            'celebrity_id' => User::inRandomOrder()->whereRole(User::ROLE_CELEBRITY)->first()->id,
            'service_id'   => Service::inRandomOrder()->first()->id,
            'slot_id'      => Slot::inRandomOrder()->first()->id,
            'status'       => Arr::random([
                Order::STATUS_DELETED,
                Order::STATUS_DECLINED,
                Order::STATUS_NEW,
                Order::STATUS_UNPAID,
                Order::STATUS_PAID,
                Order::STATUS_APPROVED,
                Order::STATUS_FINISHED,
            ]),
            'total'        => rand(10, 999) * 100,
            'duration'     => rand(5, 30),
        ];
    });