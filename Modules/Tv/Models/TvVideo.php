<?php

declare(strict_types=1);

namespace Modules\Tv\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TvVideo extends Model
{
    public const PLATFORM_YOUTUBE = 'youtube';

    public const PLATFORM_TIKTOK = 'tiktok';

    protected $fillable = [
        'title', 'source_url', 'category', 'species', 'credit', 'own_or_licensed',
        'next_label', 'next_url', 'next_label_2', 'next_url_2', 'is_published', 'sort',
    ];

    protected $casts = [
        'own_or_licensed' => 'boolean',
        'is_published' => 'boolean',
    ];

    public static function categories(): array
    {
        return [
            'funny-cats' => 'Funny cats',
            'funny-dogs' => 'Funny dogs',
            'cute' => 'Cute moments',
            'fails' => 'Pet fails',
            'care' => 'Care tips',
            'rescue' => 'Rescue stories',
        ];
    }

    protected static function booted(): void
    {
        static::saving(static function (self $video): void {
            $parsed = self::parse((string) $video->getAttribute('source_url'));

            if ($parsed === null) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'source_url' => 'Paste a YouTube Shorts, YouTube or TikTok video link.',
                ]);
            }

            $video->setAttribute('platform', $parsed['platform']);
            $video->setAttribute('video_id', $parsed['id']);
        });
    }

    /**
     * @return array{platform: string, id: string}|null
     */
    public static function parse(string $url): ?array
    {
        $url = trim($url);

        if (preg_match('~(?:youtube\.com/(?:shorts/|watch\?(?:.*&)?v=|embed/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $match)) {
            return ['platform' => self::PLATFORM_YOUTUBE, 'id' => $match[1]];
        }

        if (preg_match('~tiktok\.com/@[^/]+/video/(\d{8,25})~', $url, $match) || preg_match('~tiktok\.com/(?:embed/v2/|v/)(\d{8,25})~', $url, $match)) {
            return ['platform' => self::PLATFORM_TIKTOK, 'id' => $match[1]];
        }

        return null;
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->orderBy('sort')->orderByDesc('id');
    }

    public function embedUrl(): string
    {
        $id = (string) $this->getAttribute('video_id');

        return $this->getAttribute('platform') === self::PLATFORM_TIKTOK
            ? 'https://www.tiktok.com/embed/v2/'.$id
            : 'https://www.youtube-nocookie.com/embed/'.$id.'?autoplay=1&playsinline=1&rel=0&modestbranding=1';
    }

    public function thumbnailUrl(): ?string
    {
        return $this->getAttribute('platform') === self::PLATFORM_YOUTUBE
            ? 'https://i.ytimg.com/vi/'.$this->getAttribute('video_id').'/hqdefault.jpg'
            : null;
    }

    public function platformLabel(): string
    {
        return $this->getAttribute('platform') === self::PLATFORM_TIKTOK ? 'TikTok' : 'YouTube';
    }

    /**
     * @return list<array{label: string, url: string}>
     */
    public function nextSteps(): array
    {
        $steps = [];

        foreach ([['next_label', 'next_url'], ['next_label_2', 'next_url_2']] as [$label, $url]) {
            if (filled($this->getAttribute($label)) && filled($this->getAttribute($url))) {
                $steps[] = ['label' => (string) $this->getAttribute($label), 'url' => (string) $this->getAttribute($url)];
            }
        }

        return $steps;
    }
}
