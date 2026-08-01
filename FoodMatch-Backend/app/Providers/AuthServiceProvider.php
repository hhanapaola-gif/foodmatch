<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Explicitly load Passport keys from env vars so they work on Railway
        // where the storage/ filesystem is ephemeral (key files don't persist).
        // PassportServiceProvider::makeCryptKey() reads config('passport.private_key')
        // first; setting it here ensures it's always populated when env vars are present.
        if ($privateKey = env('PASSPORT_PRIVATE_KEY')) {
            config(['passport.private_key' => $privateKey]);
        }

        if ($publicKey = env('PASSPORT_PUBLIC_KEY')) {
            config(['passport.public_key' => $publicKey]);
        }
    }
}
