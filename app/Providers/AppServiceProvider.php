<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // En hosting compartido (InfinityFree y similares) el HTTPS lo pone un
        // proxy delante del servidor, y PHP cree que la visita llegó por HTTP.
        // Si APP_URL es https, se fuerza para que CSS, JS y formularios no
        // queden bloqueados por "contenido mixto".
        if ($this->app->isProduction() && str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceRootUrl(config('app.url'));
            URL::forceScheme('https');
        }
    }
}
