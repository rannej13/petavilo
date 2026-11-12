<?php

declare(strict_types=1);

namespace Modules\Guide\Support;

use Illuminate\Support\Str;

final class GuideRenderer
{
    public static function html(string $markdown): string
    {
        $html = Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        return self::decorateLinks($html);
    }

    public static function split(string $html): array
    {
        $offset = 0;
        $found = 0;

        while (($position = stripos($html, '<h2', $offset)) !== false) {
            $found++;

            if ($found === 2) {
                return [substr($html, 0, $position), substr($html, $position)];
            }

            $offset = $position + 3;
        }

        return [$html, ''];
    }

    private static function decorateLinks(string $html): string
    {
        $ownHost = (string) parse_url((string) config('app.url'), PHP_URL_HOST);

        return (string) preg_replace_callback('/<a href="(https?:\/\/[^"]+)"/i', static function (array $match) use ($ownHost): string {
            $host = (string) parse_url(html_entity_decode($match[1]), PHP_URL_HOST);

            if ($host === '' || $host === $ownHost) {
                return $match[0];
            }

            return '<a href="'.e(self::tagAmazon(html_entity_decode($match[1]), $host)).'" rel="sponsored nofollow noopener" target="_blank"';
        }, $html);
    }

    private static function tagAmazon(string $url, string $host): string
    {
        $host = strtolower($host);
        $tag = match (true) {
            str_ends_with($host, 'amazon.co.uk') => config('catmarket.amazon.uk'),
            str_ends_with($host, 'amazon.com') => config('catmarket.amazon.us'),
            default => null,
        };

        if (! is_string($tag) || trim($tag) === '') {
            return $url;
        }

        $parts = parse_url($url);
        parse_str((string) ($parts['query'] ?? ''), $query);
        $query['tag'] = trim($tag);

        return ($parts['scheme'] ?? 'https').'://'.($parts['host'] ?? $host).($parts['path'] ?? '/').'?'.http_build_query($query)
            .(isset($parts['fragment']) ? '#'.$parts['fragment'] : '');
    }
}
