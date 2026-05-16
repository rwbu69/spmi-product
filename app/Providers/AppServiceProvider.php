<?php

namespace App\Providers;

use App\Listeners\SendMenuNotification;
use App\Models\LaporanAmi;
use App\Models\ManajemenDokumen;
use App\Policies\LaporanAmiPolicy;
use App\Policies\ManajemenDokumenPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Spatie\Activitylog\Events\ActivityLogged;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->registerPolicies();
        $this->registerActivityNotifications();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    protected function registerPolicies(): void
    {
        Gate::policy(ManajemenDokumen::class, ManajemenDokumenPolicy::class);
        Gate::policy(LaporanAmi::class, LaporanAmiPolicy::class);
    }

    protected function registerActivityNotifications(): void
    {
        Event::listen(ActivityLogged::class, [SendMenuNotification::class, 'handle']);
    }
}
