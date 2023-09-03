<?php

namespace App\Actions\Shopify;

use Illuminate\Support\Facades\Http;

class UpdateProduct
{
    public static function update(object $newProduct, array $currentProduct)
    {
        $productShopifyId = $currentProduct['id'];
        $shopifyCnfg = config('sneakersouq.shopify');
        $endpoint = $shopifyCnfg['store_url'] . '/admin/api/2022-04/products/' . $productShopifyId . '.json';

        $data = [];
        $data['product'] = $newProduct;

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $shopifyCnfg['access_token'],
            'Content-Type' => 'application/json'
        ])->put($endpoint, $data);

        $shopProduct = $response->json()['product'];

        return $shopProduct;
    }
}