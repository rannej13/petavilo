<?php

declare(strict_types=1);

namespace Modules\Community\Models;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Modules\User\App\Models\User;

final class Community
{
    public static function displayName(?User $user): string
    {
        if (! $user instanceof User) {
            return 'Former member';
        }

        if ($user->hasRole('admin')) {
            return (string) config('catmarket.site_name', 'Petavilo').' team';
        }

        $name = trim((string) $user->getAttribute('name'));
        $parts = preg_split('/\s+/', $name) ?: [];
        $first = (string) ($parts[0] ?? 'Member');
        $last = isset($parts[1]) ? ' '.Str::upper(Str::substr((string) end($parts), 0, 1)).'.' : '';

        return $first.$last;
    }

    public static function isStaff(?User $user): bool
    {
        return $user instanceof User && $user->hasRole('admin');
    }

    public static function format(string $body): HtmlString
    {
        $escaped = e(trim($body));
        $linked = preg_replace(
            '~(https?://[^\s<]+[^\s<.,;:!?)\]])~i',
            '<a href="$1" rel="nofollow ugc noopener" target="_blank">$1</a>',
            $escaped,
        ) ?? $escaped;

        $paragraphs = array_filter(array_map('trim', preg_split("/\R{2,}/", $linked) ?: []));

        return new HtmlString(implode('', array_map(static fn (string $p): string => '<p>'.nl2br($p, false).'</p>', $paragraphs)));
    }

    public static function initials(string $name): string
    {
        return Str::upper(Str::substr($name, 0, 1));
    }
}
