<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Jobs\UpdateShopifyProduct;
use App\Jobs\CreateShopifyProduct;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProcessIncomingProduct implements ShouldQueue
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

        $params = [
            'title' => $this->product->title
        ];

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $shopifyCnfg['access_token'],
            'Content-Type' => 'application/json'
        ])->get($endpoint, $params);

        $apiCallLimit = $response->header('X-Shopify-Shop-Api-Call-Limit');

        if ($response->tooManyRequests()) {
            $retryAfter = $response->header('retry-after');
            if ($retryAfter) { $this->release($retryAfter); }
        }

        $shopifyProducts = false;
        $shopifyProduct = false;
     
        $jsonResponse = $response->json();

        if (array_key_exists('products', $jsonResponse)) {
            $shopifyProducts = $jsonResponse['products'];

            if (count($shopifyProducts)) {
                $shopifyProduct = $shopifyProducts[0];
            }
        }

        if ($shopifyProduct) {
            UpdateShopifyProduct::dispatch($this->cacheKey, $shopifyProduct['id']);
        } else {
            CreateShopifyProduct::dispatch($this->cacheKey);
        }
    }
}
