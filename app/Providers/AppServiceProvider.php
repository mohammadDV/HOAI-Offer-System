<?php

namespace App\Providers;

use App\Services\HoaiService\Contracts\HoaiCalculatorContract;
use App\Services\HoaiService\HoaiCalculatorService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(HoaiCalculatorService::class, function (Application $app): HoaiCalculatorService {

            $config = $app->make('config')->get('hoai', []);

            return new HoaiCalculatorService($config);
        });

        $this->app->bind(HoaiCalculatorContract::class, fn (Application $app): HoaiCalculatorContract => $app->make(HoaiCalculatorService::class));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
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
}
