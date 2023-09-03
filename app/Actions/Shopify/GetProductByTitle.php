<?php

namespace App\Actions\Shopify;

use Illuminate\Support\Facades\Http;

class GetProductByTitle
{
    public static function get(string $title)
    {
        $shopifyCnfg = config('sneakersouq.shopify');
        $endpoint = $shopifyCnfg['store_url'] . '/admin/api/2022-04/products.json';

        $params = [
            'title' => $title
        ];

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $shopifyCnfg['access_token'],
            'Content-Type' => 'application/json'
        ])->get($endpoint, $params);

        $jsonResponse = $response->json();

        if (count($jsonResponse['products']) === 0) {
            return null;
        }

        $product = $jsonResponse['products'][0];

        return $product;
    }
}