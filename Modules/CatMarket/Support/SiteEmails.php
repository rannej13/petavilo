<?php

declare(strict_types=1);

namespace Modules\CatMarket\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Modules\CatMarket\Mail\SiteMail;
use Modules\Listing\Models\Listing;
use Modules\Notification\Models\UserNotification;
use Throwable;

final class SiteEmails
{
    public static function welcome(object $user): void
    {
        $email = (string) ($user->email ?? '');

        if ($email === '' || str_ends_with($email, '.invalid')) {
            return;
        }

        $site = self::site();
        $lines = [
            'Hi '.trim((string) ($user->name ?? '')).', thanks for joining '.$site.'.',
            'You can post adverts, message sellers and rescues, save searches and keep your pets\' records in one place with the free Pet Passport.',
        ];
        $button = 'Post your first advert';
        $url = route('panel.listings.create');

        $needsVerifying = method_exists($user, 'hasVerifiedEmail') && ! $user->hasVerifiedEmail();

        if ($needsVerifying) {
            $lines[] = 'Please confirm this is your email address so people can trust your adverts and messages.';
            $button = 'Confirm my email';
            $url = URL::temporarySignedRoute('verification.verify', Carbon::now()->addDays(3), [
                'id' => $user->getKey(),
                'hash' => sha1(method_exists($user, 'getEmailForVerification') ? $user->getEmailForVerification() : $email),
            ]);
        }

        $lines[] = '';
        $lines[] = 'Before buying or adopting any animal, read our safety tips: never pay a deposit for a pet you have not seen in person with its mother.';

        self::send($email, new SiteMail(
            'Welcome to '.$site,
            $lines,
            $button,
            $url,
            'You get this email because an account was created on '.$site.' with this address. If that wasn\'t you, you can ignore it.',
        ));
    }

    public static function listingPosted(Listing $listing, bool $held): void
    {
        $ownerId = $listing->getAttribute('user_id');

        if ($ownerId === null || (bool) $listing->getAttribute('is_example')) {
            return;
        }

        $title = (string) $listing->getAttribute('title');

        if ($held) {
            UserNotification::publish(
                (int) $ownerId,
                UserNotification::TYPE_LISTING,
                'We are checking your advert',
                '"'.$title.'" will go live once our team has had a quick look, usually within a few hours. We check adverts for animals to keep pets and buyers safe.',
                route('panel.listings.index'),
            );

            return;
        }

        UserNotification::publish(
            (int) $ownerId,
            UserNotification::TYPE_LISTING,
            'Your advert is live',
            '"'.$title.'" is now live on '.self::site().'. You can bump or feature it from My adverts to reach more people.',
            self::listingUrl($listing),
        );
    }

    public static function listingExpiringSoon(Listing $listing): void
    {
        $ownerId = $listing->getAttribute('user_id');

        if ($ownerId === null || (bool) $listing->getAttribute('is_example')) {
            return;
        }

        $expires = $listing->getAttribute('expires_at');

        UserNotification::publish(
            (int) $ownerId,
            UserNotification::TYPE_LISTING,
            'Your advert ends soon',
            '"'.$listing->getAttribute('title').'" ends on '.($expires ? $expires->isoFormat('D MMMM') : 'soon').'. Still available? Bump it back to the top or feature it, and it stays live for as long as the boost runs. If it has sold, mark it sold so buyers stop messaging you.',
            route('panel.promotions.index', ['listing' => $listing->getKey()]),
        );
    }

    public static function listingExpired(Listing $listing): void
    {
        $ownerId = $listing->getAttribute('user_id');

        if ($ownerId === null || (bool) $listing->getAttribute('is_example')) {
            return;
        }

        UserNotification::publish(
            (int) $ownerId,
            UserNotification::TYPE_LISTING,
            'Your advert has ended',
            '"'.$listing->getAttribute('title').'" is no longer showing. You can relist it for free from My adverts, or bump or feature it to bring it back at the top of the results.',
            route('panel.listings.index'),
        );
    }

    public static function rescueVerified(object $organisation): void
    {
        $userId = $organisation->getAttribute('user_id');

        if ($userId === null) {
            return;
        }

        UserNotification::publish(
            (int) $userId,
            UserNotification::TYPE_PROMOTION,
            'Your rescue is verified',
            $organisation->getAttribute('name').' now shows the verified rescue badge. You can list animals for adoption and receive adoption applications from your rescue dashboard.',
            route('rescue.panel'),
        );
    }

    /**
     * @param  list<string>  $lines
     */
    public static function admin(string $subject, array $lines, ?string $url = null): void
    {
        $to = self::adminAddress();

        if ($to === '') {
            return;
        }

        self::send($to, new SiteMail(
            $subject,
            $lines,
            $url ? 'Open admin' : null,
            $url,
            'Admin alert from '.self::site().'.',
        ));
    }

    public static function adminAddress(): string
    {
        foreach ([config('catmarket.company.email'), config('catmarket.admin.email')] as $candidate) {
            $candidate = trim((string) $candidate);

            if ($candidate !== '' && filter_var($candidate, FILTER_VALIDATE_EMAIL) && ! str_ends_with($candidate, '.invalid')) {
                return $candidate;
            }
        }

        return '';
    }

    public static function listingUrl(Listing $listing): string
    {
        try {
            return route('listings.show', $listing);
        } catch (Throwable) {
            return route('panel.listings.index');
        }
    }

    private static function send(string $to, SiteMail $mail): void
    {
        try {
            Mail::to($to)->queue($mail);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private static function site(): string
    {
        return (string) config('catmarket.site_name', config('app.name', 'Petavilo'));
    }
}
