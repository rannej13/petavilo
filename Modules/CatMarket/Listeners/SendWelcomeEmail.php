<?php

declare(strict_types=1);

namespace Modules\CatMarket\Listeners;

use Illuminate\Auth\Events\Registered;
use Modules\CatMarket\Support\SiteEmails;

class SendWelcomeEmail
{
    public function handle(Registered $event): void
    {
        try {
            SiteEmails::welcome($event->user);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
