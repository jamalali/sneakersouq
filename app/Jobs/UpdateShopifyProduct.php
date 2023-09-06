<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class UpdateShopifyProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 10;

    public function __construct(
        public $product,
        public $shopifyProduct
    ) {}
    
    public function handle(): void
    {
        $productShopifyId = $this->shopifyProduct['id'];
        $shopifyCnfg = config('sneakersouq.shopify');
        $endpoint = $shopifyCnfg['store_url'] . '/admin/api/2022-04/products/' . $productShopifyId . '.json';

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
    }
}
