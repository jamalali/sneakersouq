<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Jobs\UpdateShopifyProduct;
use App\Jobs\CreateShopifyProduct;

class ProcessIncomingProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 10;

    public function __construct(
        public $product
    ) {}

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
     
        $jsonResponse = $response->json();

        if (!$jsonResponse['products']) {
            CreateShopifyProduct::dispatch($this->product);
        } else {
            $shopProduct = $jsonResponse['products'][0];
            UpdateShopifyProduct::dispatch($this->product, $shopProduct);
        }
    }
}
