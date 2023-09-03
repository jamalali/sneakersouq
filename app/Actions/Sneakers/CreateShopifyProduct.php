<?php

namespace App\Actions\Sneakers;

use Illuminate\Support\Facades\Http;

class CreateShopifyProduct
{
    public static function create(object $product)
    {
        $shopifyCnfg = config('sneakersouq.shopify');
        $endpoint = $shopifyCnfg['store_url'] . '/admin/api/2022-04/products.json';

        $data = [];
        $data['product'] = $product;

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $shopifyCnfg['access_token'],
            'Content-Type' => 'application/json'
        ])->post($endpoint, $data);

        $shopProduct = $response->json()['product'];

        var_dump($shopProduct);
    }
}