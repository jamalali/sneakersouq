<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Actions\Sneakers\GetSneakerFromCache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\RedirectResponse;

class SneakerController extends Controller
{
    public function show(Request $request, $sneakerId, string $cacheKey = null, string $shopifyProductId = null): Response
    {
        $sneaker = null;
        $shopProduct = null;

        if ($cacheKey) {
            $sneaker = GetSneakerFromCache::get($sneakerId, $cacheKey);
        }

        if ($sneaker) {
            $shopProduct = $this->getShopifyProductByTitle($sneaker->name);
        }

        return Inertia::render('Sneaker', [
            'sneaker' => $sneaker,
            'cacheKey' => $cacheKey,
            'shopProduct' => $shopProduct
        ]);
    }

    protected function getShopifyProductByTitle($title)
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
        $product['adminUrl'] = $shopifyCnfg['admin_url'] . '/products/' . $product['id'];
        $product['storeUrl'] = $shopifyCnfg['store_url'] . '/products/' . $product['handle'];

        return $product;
    }

    public function shopifyUp(Request $request): RedirectResponse
    {
        $shopifyCnfg = config('sneakersouq.shopify');
        $input = $request->all();

        $endpoint = $shopifyCnfg['store_url'] . '/admin/api/2022-04/products.json';

        $data = [];
        $product = [];
        $product['product_type'] = 'Sneakers';

        if ($request->has('name'))  { $product['title'] = $input['name']; }
        if ($request->has('story')) { $product['body_html'] = $input['story']; }
        if ($request->has('brand')) { $product['vendor'] = $input['brand']; }

        if ($request->has('image')) {
            $imageData = $input['image'];

            if ($imageData['original']) {
                $product['images'] = [
                    [
                        'src' => $imageData['original']
                    ]
                ];
            }
        }

        $product['variants'] = [
            [
                'option1' => 'Default Title',
                'price' => '0.00',
                'sku' => $input['sku']
            ]
        ];

        $product['tags'] = [
            $input['gender']
        ];

        $product['metafields'] = [
            [
                'key' => 'db_id',
                'value' => $input['id'],
                'type' => 'single_line_text_field',
                'namespace' => 'product'
            ],
            [
                'key' => 'db_release_date',
                'value' => $input['releaseDate'],
                'type' => 'date',
                'namespace' => 'product'
            ],
            [
                'key' => 'db_silhouette',
                'value' => $input['silhouette'],
                'type' => 'single_line_text_field',
                'namespace' => 'product'
            ],
            [
                'key' => 'db_colorway',
                'value' => $input['colorway'],
                'type' => 'single_line_text_field',
                'namespace' => 'product'
            ]
        ];

        $data['product'] = $product;

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $shopifyCnfg['access_token'],
            'Content-Type' => 'application/json'
        ])->post($endpoint, $data);

        $product = $response->json()['product'];

        return redirect()->route('sneaker', [
            'sneakerId' => $input['id'],
            'cacheKey' => $input['cacheKey'],
            'shopifyProductId' => $product['id']
        ]);

        // return to_route('sneaker', [
        //     'sneakerId' => $input['id']
        // ]);        
    }
}