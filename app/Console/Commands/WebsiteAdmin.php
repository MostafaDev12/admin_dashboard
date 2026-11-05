<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class WebsiteAdmin extends Command
{
    protected $signature = 'website:admin {email} {--name=Website administrator}';

    protected $description = 'Create or reset an administrator with an interactively supplied password';

    public function handle(): int
    {
        $email = $this->argument('email');
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('A valid email is required.');

            return self::FAILURE;
        }
        $password = $this->secret('Password (at least 12 characters)');
        if (mb_strlen($password ?? '') < 12) {
            $this->error('Use at least 12 characters.');

            return self::FAILURE;
        }
        $admin = Admin::firstOrNew(['email' => $email]);
        $admin->name = $this->option('name');
        $admin->password = Hash::make($password);
        $admin->role_id = 0;
        $admin->save();
        $this->info('Administrator saved. Sign in at /admin/login.');

        return self::SUCCESS;
    }
}
