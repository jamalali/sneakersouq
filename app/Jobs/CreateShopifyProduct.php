<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CreateShopifyProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $product = null;
    public $cacheKey = null;

    public function __construct($cacheKey)
    {
        $this->cacheKey = $cacheKey;
        $this->product = Cache::get($cacheKey);
    }

    public function middleware(): array
    {
        return [new WithoutOverlapping($this->product->title)];
    }

    public function handle(): void
    {
        $shopifyCnfg = config('sneakersouq.shopify');
        $endpoint = $shopifyCnfg['store_url'] . '/admin/api/2022-04/products.json';

        foreach($this->product->variants as &$variant) {
            if ($variant->price > 0) {
                $variant->price = $variant->price * 3.673;
                $variant->inventory_quantity = 99;
            } else {
                $variant->inventory_quantity = 0;
                $variant->inventory_policy = 'deny';
            }
        }

        $data = [];
        $data['product'] = $this->product;

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $shopifyCnfg['access_token'],
            'Content-Type' => 'application/json'
        ])->post($endpoint, $data);

        if ($response->tooManyRequests()) {
            $retryAfter = $response->header('retry-after');
            if ($retryAfter) { $this->release($retryAfter); }
        } else if ($response->created()) {
            Cache::forget($this->cacheKey);
        }
    }
}
