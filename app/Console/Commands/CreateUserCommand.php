<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class CreateUserCommand extends Command
{
    protected $signature = 'create-user';

    protected $description = 'Create a new user account';

    public function handle(): int
    {
        $name = text(
            label: 'Name',
            required: true,
        );

        $email = text(
            label: 'Email',
            required: true,
            validate: function (string $value) {
                if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return 'Please enter a valid email address.';
                }

                if (User::where('email', $value)->exists()) {
                    return 'A user with this email already exists.';
                }

                return null;
            },
        );

        $pw = password(
            label: 'Password',
            required: true,
            validate: function (string $value) {
                try {
                    validator(['password' => $value], ['password' => Password::defaults()])->validate();
                } catch (ValidationException $e) {
                    return $e->errors()['password'][0];
                }

                return null;
            },
        );

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($pw),
            'email_verified_at' => now(),
        ]);

        $this->comment("User {$email} created successfully.");

        return self::SUCCESS;
    }
}
