<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'email' => $this->faker->unique()->companyEmail,
            'phone' => $this->faker->phoneNumber,
            'cnpj' => $this->faker->numerify('##.###.###/####-##'),
            'timezone' => 'America/Sao_Paulo',
            'trial_expires_at' => now()->addDays(7),
            'is_active' => true,
            'settings' => [],
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'trial_expires_at' => now()->subDays(1),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
