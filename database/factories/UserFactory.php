<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $people = \App\Models\People::factory()->create();
        return [
            'people_id' => $people->id ,
            'lastname' => $people->lastname ,
            'firstname' => $people->firstname  ,
            'email' =>  $people->email ,
            'username' =>  str_replace(" ","", strtolower( $people->lastname .  $people->firstname ) ),
            'phone' => $people->mobile_phone ,
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'active' => 1 ,
            'email_verified_at' => now(),
            'remember_token' => Str::random(10)
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterMaking(function (\App\Models\User $user) {
            $role = \App\Models\Role::where('name','Client')->first();
            $user->assignRole($role) ;
        })->afterCreating(function (\App\Models\User $user) {
            $role = \App\Models\Role::where('name','Client')->first();
            $user->assignRole($role) ;
        });
    }
}
