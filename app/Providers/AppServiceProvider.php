<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Ticket;
use App\Policies\CommentPolicy;
use App\Policies\TicketPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        // Enregistrer les policies
        Gate::policy(Ticket::class, TicketPolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);
    }
}

