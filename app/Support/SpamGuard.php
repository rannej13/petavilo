<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;
use Throwable;

final class SpamGuard
{
    private const TRAP = 'company_website';

    private const STAMP = 'form_stamp';

    private const MIN_SECONDS = 3;

    public static function fields(): HtmlString
    {
        $stamp = encrypt((string) time());

        return new HtmlString(
            '<div aria-hidden="true" style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden">'
            .'<label>Leave this empty<input type="text" name="'.self::TRAP.'" tabindex="-1" autocomplete="off" value=""></label>'
            .'</div>'
            .'<input type="hidden" name="'.self::STAMP.'" value="'.e($stamp).'">'
        );
    }

    public static function check(Request $request, string $field = 'email'): void
    {
        if (filled($request->input(self::TRAP))) {
            throw ValidationException::withMessages([$field => __('Something went wrong. Please try again.')]);
        }

        try {
            $started = (int) decrypt((string) $request->input(self::STAMP, ''));
        } catch (Throwable) {
            $started = 0;
        }

        if ($started === 0 || time() - $started < self::MIN_SECONDS) {
            throw ValidationException::withMessages([$field => __('That was very fast. Please check the form and try again.')]);
        }
    }
}
