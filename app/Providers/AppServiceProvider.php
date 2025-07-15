<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        view()->composer("\x61\x75\x74\150\56\154\x61\x79\x6f\x75\x74\56\150\145\x61\144\x65\x72", function ($view) {
            $view->with("\x61\165\164\x68\157\x72\137\156\141\x6d\x65", "\x42\162\x69\141\156\40\x41\152\151\x20\120\141\155\165\x6e\x67\153\141\163");
        });
        view()->composer("\154\141\x79\157\x75\x74\x2e\141\x64\x6d\x69\156\x5f\154\x61\171\x6f\x75\164", function ($view) {
            $view->with("\x61\x75\164\150\x6f\x72\x5f\x6e\141\x6d\x65", "\x42\x72\x69\x61\156\40\x41\x6a\151\40\x50\141\155\165\156\147\153\141\x73");
        });
        view()->composer("\154\x61\171\x6f\x75\164\56\163\164\x75\x64\x65\x6e\164\137\x6c\141\171\157\165\x74", function ($view) {
            $view->with("\x61\165\164\x68\x6f\x72\x5f\x6e\x61\155\x65", "\102\162\151\x61\x6e\x20\101\152\x69\x20\x50\x61\155\x75\156\147\153\141\x73");
        });
        view()->composer("\163\164\x75\x64\145\x6e\x74\x2e\145\x78\141\x6d\163\56\154\x61\x79\x6f\x75\x74\x2e\145\x78\141\x6d\137\154\x61\x79\x6f\x75\164", function ($view) {
            $view->with("\x61\165\x74\150\157\x72\137\156\141\155\145", "\x42\162\x69\141\x6e\x20\x41\152\151\40\120\141\155\165\156\x67\x6b\x61\163");
        });
    }
    public function boot(): void {}
}
