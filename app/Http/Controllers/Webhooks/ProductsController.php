<?php
namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\ProcessIncomingProduct;
use Illuminate\Support\Facades\Log;

class ProductsController extends Controller
{
    public function index(Request $request): Object
    {
        $bodyContent = $request->getContent();
        $bodyJson = json_decode($bodyContent);
        $results = $bodyJson->result;

        Log::info('Received ' . count($results) . ' products from Agenty');

        foreach($results as $result) {
            $productContent = $result->Product;
            $product = json_decode($productContent);

            unset($product->id);
            unset($product->created_at);
            unset($product->updated_at);
            unset($product->published_at);
            unset($product->handle);

            data_forget($product, 'variants.*.id');
            data_forget($product, 'variants.*.product_id');
            data_forget($product, 'variants.*.created_at');
            data_forget($product, 'variants.*.updated_at');

            data_forget($product, 'options.*.id');
            data_forget($product, 'options.*.product_id');

            data_forget($product, 'images.*.id');
            data_forget($product, 'images.*.product_id');
            data_forget($product, 'images.*.created_at');
            data_forget($product, 'images.*.updated_at');
            data_forget($product, 'images.*.variant_ids');

            $cacheKey = 'incoming_product_' . $product->title;
            Cache::put($cacheKey, $product);

            ProcessIncomingProduct::dispatch($cacheKey);
        }

        return response()->json([
            'success' => 'true'
        ], 201);
    }

}