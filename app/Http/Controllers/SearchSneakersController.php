<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\RedirectResponse;

class SearchSneakersController extends Controller
{
    public function index(Request $request): Response
    {
        $sneakers = null;
        $cacheKey = null;

        $limit = '100';
        $page = $request->query('page', null);
        $sku = $request->query('sku', null);

        $success = $request->query('success', null);
        $fail = $request->query('fail', null);

        if ($sku) {
            $sneakersData = $this->getSneakersBySku($sku, $page, $limit);

            $cacheKey = substr(md5(rand()), 0, 15);
            Cache::put($cacheKey, json_encode($sneakersData), $seconds = 86400);

            $sneakers = $sneakersData->results;
        }

        return Inertia::render('SearchSneakers', [
            'sneakers' => $sneakers,
            'cacheKey' => $cacheKey,
            'page' => (int) $page,
            'sku' => $sku,
            'success' => $success,
            'fail'=> $fail
        ]);
    }

    public function shopifySync(Request $request): RedirectResponse
    {
        $sneakers = null;

        $successCount = 0;
        $failCount = 0;

        $cacheKey           = $request->input('cacheKey', null);
        $selectedSneakers   = $request->input('selectedSneakers', null);

        if ($cacheKey) {
            $sneakersData = Cache::get($cacheKey, null);
        }

        if ($sneakersData) {
            $sneakers = json_decode($sneakersData)->results;
        }

        $sneakersToAdd = array_filter($sneakers, fn ($s) => in_array($s->id, $selectedSneakers));

        foreach($sneakersToAdd as $sneaker) {
            $response = $this->addShopifyProduct($sneaker);

            if ($response['successful']) { $successCount++; }
            if ($response['failed']) { $failCount++; }
        }

        return redirect()->route('search-sneakers', [
            'success' => $successCount,
            'fail' => $failCount
        ]);
    }

    protected function addShopifyProduct($sneaker)
    {
        $shopifyCnfg = config('sneakersouq.shopify');
        $endpoint = $shopifyCnfg['store_url'] . '/admin/api/2022-04/products.json';

        $data = [];
        $product = [];
        $product['product_type'] = 'Sneakers';

        if (isset($sneaker->name))  { $product['title'] = $sneaker->name; }
        if (isset($sneaker->story)) { $product['body_html'] = $sneaker->story; }
        if (isset($sneaker->brand)) { $product['vendor'] = $sneaker->brand; }

        if (isset($sneaker->image)) {
            $imageData = $sneaker->image;

            if ($imageData->original) {
                $product['images'] = [
                    [
                        'src' => $imageData->original
                    ]
                ];
            }
        }

        $product['variants'] = [
            [
                'option1' => 'Default Title',
                'price' => '0.00',
                'sku' => $sneaker->sku
            ]
        ];

        $product['tags'] = [
            $sneaker->gender
        ];

        $product['metafields'] = [
            [
                'key' => 'db_id',
                'value' => $sneaker->id,
                'type' => 'single_line_text_field',
                'namespace' => 'product'
            ],
            [
                'key' => 'db_release_date',
                'value' => $sneaker->releaseDate,
                'type' => 'date',
                'namespace' => 'product'
            ],
            [
                'key' => 'db_silhouette',
                'value' => $sneaker->silhouette,
                'type' => 'single_line_text_field',
                'namespace' => 'product'
            ],
            [
                'key' => 'db_colorway',
                'value' => $sneaker->colorway,
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

        return [
            'product' => $product,
            'successful' => $response->successful(),
            'failed' => $response->failed()
        ];
    }

    protected function getSneakersBySku($sku, $page, $limit) {
        $sneaksDb = config('sneakersouq.sneakers_db');
        
        $url = $sneaksDb['url'] . '/sneakers';

        $params = [
            'sku' => $sku,
            'limit' => $limit,
            'page' => $page
        ];

        $params = array_filter($params, fn ($value) => $value != null);

        $response = Http::withHeaders([
            'X-RapidAPI-Key' => $sneaksDb['api_key'],
            'X-RapidAPI-Host' => $sneaksDb['api_host']
        ])->get($url, $params);

        return $response->object();
    }
}
