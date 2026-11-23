<?php
namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Paginator::useBootstrapFive();   // tanpa ini, tombol halaman (pagination) tampil rusak
        Blade::directive('rp', fn ($e) => "<?php echo 'Rp '.number_format((float)($e), 0, ',', '.'); ?>");
    }
}