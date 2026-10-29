<?php

namespace App\Events\Concerns;

trait ResolvesKdsChannel
{
    public static function channelName(): string
    {
        $configured = config('broadcasting.connections.pusher.kds_channel');
        if (! empty($configured)) {
            return (string) $configured;
        }

        return match (app()->environment()) {
            'production' => 'pos-seafood-kds-production',
            'staging' => 'pos-seafood-kds-staging',
            default => 'pos-seafood-kds-development',
        };
    }
}

