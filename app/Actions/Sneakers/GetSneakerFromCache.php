<?php

namespace App\Actions\Sneakers;

use Illuminate\Support\Facades\Cache;

class GetSneakerFromCache
{
    public static function get(string $sneakerId, string $cacheKey)
    {
        $sneakersCache = Cache::get($cacheKey);
        $sneakersJson = json_decode($sneakersCache);
        $sneakers = $sneakersJson->results;

        $sneaker = null;

        foreach ($sneakers as $s) {
            if ($s->id == $sneakerId) {
                $sneaker = $s;
                break;
            }
        }

        return $sneaker;
    }
}