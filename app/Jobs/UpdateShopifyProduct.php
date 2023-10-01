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

class UpdateShopifyProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $cacheKey = null;
    public $shopifyProductId = null;
    public $product = null;

    public function __construct($cacheKey, $shopifyProductId)
    {
        $this->cacheKey = $cacheKey;
        $this->shopifyProductId = $shopifyProductId;
        $this->product = Cache::get($cacheKey);
    }

    public function middleware(): array
    {
        return [new WithoutOverlapping($this->product->title)];
    }
    
    public function handle(): void
    {
        $shopifyCnfg = config('sneakersouq.shopify');
        $endpoint = $shopifyCnfg['store_url'] . '/admin/api/2022-04/products/' . $this->shopifyProductId . '.json';

        data_forget($this->product, 'images');
        data_forget($this->product, 'image');

        foreach($this->product->variants as &$variant) {
            $variant->price = $variant->price * 3.673;
            $variant->inventory_policy = 'continue';
        }

        $data = [];
        $data['product'] = $this->product;

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $shopifyCnfg['access_token'],
            'Content-Type' => 'application/json'
        ])->put($endpoint, $data);

        if ($response->tooManyRequests()) {
            $retryAfter = $response->header('retry-after');
            if ($retryAfter) { $this->release($retryAfter); }
        } else if ($response->ok()) {
            Cache::forget($this->cacheKey);
        }
    }
}
