<?php

declare(strict_types=1);

namespace Modules\CatMarket\Support;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Modules\Page\Models\Page;
use Throwable;

final class ContactEmail
{
    private const MARKER = 'catmarket/contact-email';

    public static function sync(?string $current = null): void
    {
        try {
            $current = strtolower(trim((string) ($current ?? CatMarketSettings::contactEmail())));

            if ($current === '' || ! Schema::hasTable('pages')) {
                return;
            }

            $disk = Storage::disk('local');
            $previous = $disk->exists(self::MARKER) ? strtolower(trim((string) $disk->get(self::MARKER))) : '';

            if ($previous !== '' && $previous !== $current) {
                Page::query()->where('body', 'like', '%'.$previous.'%')->get()->each(
                    static fn (Page $page): bool => $page->forceFill([
                        'body' => str_ireplace($previous, $current, (string) $page->getAttribute('body')),
                    ])->save(),
                );
            }

            $disk->put(self::MARKER, $current);
        } catch (Throwable) {
            return;
        }
    }
}
