<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class UpdateShopifyProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $product = null;

    public function __construct(public $cacheKey, public $shopifyProductId)
    {
        $this->product = Cache::get($this->cacheKey);
    }
    
    public function handle(): void
    {
        $shopifyCnfg = config('sneakersouq.shopify');
        $endpoint = $shopifyCnfg['store_url'] . '/admin/api/2022-04/products/' . $this->shopifyProductId . '.json';

        $data = [];
        $data['product'] = $this->product;

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $shopifyCnfg['access_token'],
            'Content-Type' => 'application/json'
        ])->put($endpoint, $data);

        if ($response->tooManyRequests()) {
            $retryAfter = $response->header('retry-after');
            if ($retryAfter) { $this->release($retryAfter); }
        }

        if ($response->ok()) {
            $product = $response->json()['product'];

            Log::info('Updated product in Shopify', ['title' => $product['title']]);
            Cache::forget($this->cacheKey);
        }
    }
}
