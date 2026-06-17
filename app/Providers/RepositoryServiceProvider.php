<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\Contracts\CourtRepositoryInterface;
use App\Repositories\CourtRepository;
use App\Repositories\Contracts\TestimonialRepositoryInterface;
use App\Repositories\TestimonialRepository;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\BookingRepository;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Repositories\PaymentRepository;
use App\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Repositories\ExpenseRepository;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use App\Repositories\NotificationRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(CourtRepositoryInterface::class, CourtRepository::class);
        $this->app->bind(TestimonialRepositoryInterface::class, TestimonialRepository::class);
        $this->app->bind(BookingRepositoryInterface::class, BookingRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
        $this->app->bind(ExpenseRepositoryInterface::class, ExpenseRepository::class);
        $this->app->bind(NotificationRepositoryInterface::class, NotificationRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
