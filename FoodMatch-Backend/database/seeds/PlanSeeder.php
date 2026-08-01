<?php

use App\Model\Plan;
use App\Model\PlanCategory;
use App\Model\PlanDay;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        // Skip if data already exists
        if (PlanCategory::count() > 0) {
            $this->command->info('Plan data already exists, skipping.');
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // ── Categories ──────────────────────────────────────────────
        $cat1 = PlanCategory::create([
            'name'      => 'Healthy & Diet',
            'image'     => null,
            'is_active' => 1,
            'priority'  => 1,
        ]);

        $cat2 = PlanCategory::create([
            'name'      => 'Family Meals',
            'image'     => null,
            'is_active' => 1,
            'priority'  => 2,
        ]);

        // ── Plans ────────────────────────────────────────────────────
        $plan1 = Plan::create([
            'restaurant_id'   => null,
            'category_id'     => $cat1->id,
            'title'           => 'Weekly Healthy Plan',
            'description'     => 'A full week of balanced, nutritious meals designed by our nutritionist team.',
            'type'            => 'weekly',
            'min_days'        => 5,
            'meals_per_day'   => 3,
            'breakfast_price' => 8.00,
            'lunch_price'     => 12.00,
            'dinner_price'    => 15.00,
            'image'           => null,
            'menu_pdf'        => null,
            'is_active'       => 1,
        ]);

        $plan2 = Plan::create([
            'restaurant_id'   => null,
            'category_id'     => $cat2->id,
            'title'           => 'Monthly Family Bundle',
            'description'     => 'A full month of family-sized meals — perfect for 2–4 people with variety every day.',
            'type'            => 'monthly',
            'min_days'        => 20,
            'meals_per_day'   => 2,
            'breakfast_price' => 0.00,
            'lunch_price'     => 20.00,
            'dinner_price'    => 25.00,
            'image'           => null,
            'menu_pdf'        => null,
            'is_active'       => 1,
        ]);

        $plan3 = Plan::create([
            'restaurant_id'   => null,
            'category_id'     => $cat1->id,
            'title'           => 'Keto Weekly Plan',
            'description'     => 'Low-carb, high-fat meals for those following a ketogenic lifestyle.',
            'type'            => 'weekly',
            'min_days'        => 5,
            'meals_per_day'   => 2,
            'breakfast_price' => 10.00,
            'lunch_price'     => 14.00,
            'dinner_price'    => 18.00,
            'image'           => null,
            'menu_pdf'        => null,
            'is_active'       => 1,
        ]);

        // ── Plan Days ────────────────────────────────────────────────
        // Plan 1: Mon–Fri (1–5)
        foreach ([1, 2, 3, 4, 5] as $day) {
            PlanDay::create(['plan_id' => $plan1->id, 'day_of_week' => $day]);
        }

        // Plan 2: Mon–Sat (1–6)
        foreach ([1, 2, 3, 4, 5, 6] as $day) {
            PlanDay::create(['plan_id' => $plan2->id, 'day_of_week' => $day]);
        }

        // Plan 3: Mon, Wed, Fri, Sat (1,3,5,6)
        foreach ([1, 3, 5, 6] as $day) {
            PlanDay::create(['plan_id' => $plan3->id, 'day_of_week' => $day]);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('Plan seeder complete: 2 categories, 3 plans, ' . PlanDay::count() . ' plan days.');
    }
}
