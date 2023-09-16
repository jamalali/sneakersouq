<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ProcessIncomingProducts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public $cacheKey
    ){}

    public function handle(): void
    {
        $results = Cache::get($this->cacheKey);

        Log::info('Processing ' . count($results) . ' products.');

        foreach($results as $result) {
            $productContent = is_array($result) ? $result['Product'] : $result->Product;
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

        Cache::forget($this->cacheKey);
    }
}
