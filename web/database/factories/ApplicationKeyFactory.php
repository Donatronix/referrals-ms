<?php

namespace Database\Factories;

use App\Models\ApplicationKey;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationKeyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ApplicationKey::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $cipherList = [
            'AES-128-ECB',
            'AES-256-ECB'
        ];

        $cipher = $this->faker->randomElement($cipherList);

        return [
            'version_key' => $this->faker->unique()->randomNumber(9),
            'cipher' => $cipher,
            'cipher_key' => $cipher === 'AES-128-ECB' ? hash('md5', $this->faker->text) : hash('sha256', $this->faker->text),
        ];
    }
}
