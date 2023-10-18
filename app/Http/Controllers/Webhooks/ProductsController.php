<?php
namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\ProcessIncomingProducts;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ProductsController extends Controller
{
    public function index(Request $request): Object
    {
        $bodyContent = $request->getContent();
        $bodyJson = json_decode($bodyContent);
        $results = $bodyJson->result;

        $expectedNumResults = 250;

        if (count($results) > $expectedNumResults) {
            return response()->json([
                'message' => 'Results count must be ' . $expectedNumResults .' or less. ' . count($results) .' given.'
            ], 400);
        }

        Log::info('Received ' . count($results) . ' products from Agenty');

        $cacheKey = uniqid('incoming_products_', true);
        Cache::put($cacheKey, $results);

        ProcessIncomingProducts::dispatch($cacheKey);

        return response()->json([
            'message' => 'success'
        ], 201);
    }
}