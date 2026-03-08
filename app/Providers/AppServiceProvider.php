<?php

namespace App\Providers;

use App\Models\Payment;
use App\Policies\PaymentPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Models\Invoice;
use App\Policies\InvoicePolicy;
use App\Models\Student;
use App\Policies\StudentPolicy;

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
    Gate::policy(\App\Models\Payment::class, \App\Policies\PaymentPolicy::class);
    Gate::policy(Invoice::class, InvoicePolicy::class);
    Gate::policy(Student::class, StudentPolicy::class);
}
}
