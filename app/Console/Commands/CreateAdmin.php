<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    protected $signature = 'golpobari:create-admin
        {--from-env : Read ADMIN_NAME / ADMIN_EMAIL / ADMIN_PASSWORD from .env}
        {--role=owner : owner or staff}';

    protected $description = 'Create (or reset the password of) an admin account';

    public function handle(): int
    {
        if ($this->option('from-env')) {
            $name = (string) env('ADMIN_NAME', 'Admin');
            $email = (string) env('ADMIN_EMAIL');
            $password = (string) env('ADMIN_PASSWORD');
        } else {
            $name = $this->ask('Name', 'Admin');
            $email = $this->ask('Email');
            $password = $this->secret('Password (min 12 chars, letters + numbers)');
        }

        $v = Validator::make(compact('name', 'email', 'password'), [
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email'],
            'password' => ['required', Password::min(12)->letters()->numbers()->mixedCase()],
        ]);
        if ($v->fails()) {
            foreach ($v->errors()->all() as $e) {
                $this->error($e);
            }

            return self::FAILURE;
        }

        $role = in_array($this->option('role'), ['owner', 'staff'], true) ? $this->option('role') : 'owner';
        $admin = Admin::updateOrCreate(['email' => strtolower($email)], ['name' => $name, 'password' => $password, 'role' => $role, 'is_active' => true]);

        $this->info(($admin->wasRecentlyCreated ? 'Created' : 'Updated')." admin {$admin->email} ({$role}).");
        if ($this->option('from-env')) {
            $this->warn('Now remove ADMIN_PASSWORD from your .env file.');
        }

        return self::SUCCESS;
    }
}
