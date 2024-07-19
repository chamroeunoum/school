<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\People>
 */
class PeopleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        list( $lastname , $firstname ) = explode( " " , fake()->name() );
        list($name,$domain) = explode("@",fake()->unique()->safeEmail());
        return [
            'firstname' => $firstname ,
            'lastname' => $lastname ,
            'gender' => random_int(0,1) ,
            'dob' => fake()->dateTimeBetween('1950-01-01', '2005-01-01')->format('Y-m-d') ,
            'mobile_phone' => fake()->phoneNumber ,
            'office_phone' => fake()->phoneNumber ,
            'email' => strtolower($lastname.$firstname."@".$domain) ,
            'member_since' => fake()->dateTimeBetween('2000-01-01', '2020-01-01')->format('Y-m-d') ,
            'active' => 1
        ];
    }
    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterMaking(function (\App\Models\People $people) {
            $people->selfAssignCode();
        })->afterCreating(function (\App\Models\People $people) {
            $people->selfAssignCode();
        });
    }
}
