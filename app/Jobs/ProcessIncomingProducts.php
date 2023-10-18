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

        $storagePath = storage_path('app/public/product_data/');
        $fileName = "products_" . time() . ".csv";
        $filePath = $storagePath.$fileName;
        $file = fopen($filePath, 'w');

        $colHeadings = array(
            'Command',
            'Handle',
            'Title',
            'Body HTML',
            'Vendor',
            'Type',
            'Tags',
            'Tags Command',
            'Status',
            'Published',
            'Published Scope',
            'Row #',
            'Top Row',

            'Image Type',
            'Image Src',
            'Image Command',
            'Image Position',
            'Image Width',
            'Image Height',
            'Image Alt Text',

            'Variant Command',
            'Option1 Name',
            'Option1 Value',
            'Option2 Name',
            'Option2 Value',
            'Option3 Name',
            'Option3 Value',
            'Variant Position',
            'Variant SKU',
            'Variant Barcode',
            'Variant Weight',
            'Variant Weight Unit',
            'Variant Price',
            'Variant Compare At Price',
            'Variant Taxable',
            'Variant Inventory Tracker',
            'Variant Inventory Policy',
            'Variant Fulfillment Service',
            'Variant Requires Shipping',
            'Variant Inventory Qty',
            'Variant Inventory Adjust'
        );

        fputcsv($file, $colHeadings);

        $i = 0;
        $rowNum = 1;
        $topRow = false;
        foreach($results as $result) {
            $productContent = is_array($result) ? $result['Product'] : $result->Product;
            $product = json_decode($productContent);

            $topRow = true;

            foreach($product->variants as $variant) {

                $line = array();

                if ($variant->price > 0) {
                    $vPrice = $variant->price * 3.673;
                    $vInventoryQuantity = 99;
                    $vInventoryPolicy = 'continue';
                } else {
                    $vPrice = 0.00;
                    $vInventoryQuantity = 0;
                    $vInventoryPolicy = 'deny';
                }

                $image = isset($product->images[$i]) ? $product->images[$i] : null;

                $line[] = 'MERGE';
                $line[] = $product->handle;
                $line[] = $product->title;
                $line[] = $topRow ? $product->body_html : '';
                $line[] = $product->vendor;
                $line[] = $product->product_type;
                $line[] = $product->tags;
                $line[] = 'REPLACE';
                $line[] = 'Active';
                $line[] = 'TRUE';
                $line[] = 'web';
                $line[] = $rowNum;
                $line[] = $topRow ? 'TRUE' : '';

                $line[] = isset($image) ? 'IMAGE' : '';
                $line[] = isset($image) ? $image->src : '';
                $line[] = isset($image) ? 'MERGE' : '';
                $line[] = isset($image) ? $image->position : '';
                $line[] = isset($image) ? $image->width : '';
                $line[] = isset($image) ? $image->height : '';
                $line[] = isset($image) ? $product->title : '';

                $line[] = 'MERGE';
                $line[] = isset($product->options[0]) ? $product->options[0]->name : '';
                $line[] = $variant->option1;
                $line[] = isset($product->options[1]) ? $product->options[1]->name : '';
                $line[] = $variant->option2;
                $line[] = isset($product->options[2]) ? $product->options[2]->name : '';
                $line[] = $variant->option3;
                $line[] = $variant->position;
                $line[] = $variant->sku;
                $line[] = $variant->barcode ? $variant->barcode : '';
                $line[] = $variant->weight ? $variant->weight : '';
                $line[] = $variant->weight_unit ? $variant->weight_unit : '';
                $line[] = $vPrice;
                $line[] = $variant->compare_at_price ? $variant->compare_at_price : '';
                $line[] = 'TRUE';
                $line[] = 'Shopify';
                $line[] = $vInventoryPolicy;
                $line[] = 'manual';
                $line[] = 'TRUE';
                $line[] = $vInventoryQuantity;
                $line[] = 0;

                fputcsv($file, $line);
    
                $i++;
                $rowNum++;
                $topRow = false;
            }

            // $cacheKey = 'incoming_product_' . $product->title;
            // Cache::put($cacheKey, $product);

            // ProcessIncomingProduct::dispatch($cacheKey, $filePath);
        }

        fclose($file);

        Cache::forget($this->cacheKey);
    }
}
