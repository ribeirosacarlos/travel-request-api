<?php
// routes/console.php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('user:create-admin {name} {email} {password}', function () {
    $name = $this->argument('name');
    $email = $this->argument('email');
    $password = $this->argument('password');

    if (\App\Models\User::where('email', $email)->exists()) {
        $this->error('User with this email already exists!');
        return 1;
    }

    $user = \App\Models\User::create([
        'name' => $name,
        'email' => $email,
        'password' => \Illuminate\Support\Facades\Hash::make($password),
        'role' => 'admin',
    ]);

    $this->info("Admin user created successfully!");
    $this->info("ID: {$user->id}");
    $this->info("Name: {$user->name}");
    $this->info("Email: {$user->email}");

    return 0;
})->purpose('Create an admin user');