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
        $dateTimeNow = date("ymd_Hi_e");   

        $fileName = "products_" . $dateTimeNow . ".csv";
        $fileNameNoImg = "products_" . $dateTimeNow . "_no_images.csv";

        $filePath = $storagePath.$fileName;
        $filePathNoImg = $storagePath.$fileNameNoImg;

        $file = fopen($filePath, 'w');
        $fileNoImg = fopen($filePathNoImg, 'w');

        $colHeadings = array();

        $colHeadings[] = 'Handle';
        $colHeadings[] = 'Title';
        $colHeadings[] = 'Body (HTML)';
        $colHeadings[] = 'Vendor';
        $colHeadings[] = 'Type';
        $colHeadings[] = 'Tags';
        $colHeadings[] = 'Published';

        $colHeadings[] = 'Option1 Name';
        $colHeadings[] = 'Option1 Value';
        $colHeadings[] = 'Option2 Name';
        $colHeadings[] = 'Option2 Value';
        $colHeadings[] = 'Option3 Name';
        $colHeadings[] = 'Option3 Value';
            
        $colHeadings[] = 'Variant SKU';
        $colHeadings[] = 'Variant Grams';
        $colHeadings[] = 'Variant Inventory Tracker';
        $colHeadings[] = 'Variant Inventory Qty';
        $colHeadings[] = 'Variant Inventory Policy';
        $colHeadings[] = 'Variant Fulfillment Service';
        $colHeadings[] = 'Variant Price';
        $colHeadings[] = 'Variant Compare At Price';
        $colHeadings[] = 'Variant Requires Shipping';
        $colHeadings[] = 'Variant Taxable';
        $colHeadings[] = 'Variant Barcode';
        $colHeadings[] = 'Variant Weight Unit';

        $colHeadings[] = 'Status';

        // Add headings to CSV without images
        fputcsv($fileNoImg, $colHeadings);

        $colHeadings[] = 'Image Src';
        $colHeadings[] = 'Image Position';
        $colHeadings[] = 'Image Alt Text';

        fputcsv($file, $colHeadings);

        $rowNum = 1;
        $topRow = false;
        foreach($results as $result) {
            $productContent = is_array($result) ? $result['Product'] : $result->Product;
            $product = json_decode($productContent);

            $topRow = true;

            $vIndex = 0;
            foreach($product->variants as $variant) {

                $line = array();

                if ($variant->price > 0) {
                    $vPrice = $variant->price * 3.673;
                    $vPrice = number_format((float)$vPrice, 2, '.', '');
                    $vInventoryQuantity = 99;
                    $vInventoryPolicy = 'continue';
                } else {
                    continue;
                }

                $image = isset($product->images[$vIndex]) ? $product->images[$vIndex] : null;

                // Handle
                $line[] = $product->handle;

                // Title
                $line[] = $product->title;

                // Body (HTML)
                $line[] = $topRow ? $product->body_html : '';

                // Vendor
                $line[] = $product->vendor;

                // Type
                $line[] = $product->product_type;

                // Tags
                $line[] = $product->tags;

                // Published
                $line[] = 'TRUE';

                // Option1 Name & Value
                $line[] = isset($product->options[0]) ? $product->options[0]->name : '';
                $line[] = $variant->option1;

                // Option2 Name & Value
                $line[] = isset($product->options[1]) ? $product->options[1]->name : '';
                $line[] = $variant->option2;

                // Option3 Name & Value
                $line[] = isset($product->options[2]) ? $product->options[2]->name : '';
                $line[] = $variant->option3;

                // Variant SKU
                $line[] = $variant->sku;

                // Variant Grams
                $line[] = $variant->grams;
                
                // Variant Inventory Tracker
                $line[] = 'Shopify';

                // Variant Inventory Qty
                $line[] = $vInventoryQuantity;

                // Variant Inventory Policy
                $line[] = $vInventoryPolicy;

                // Variant Fulfillment Service
                $line[] = 'manual';

                // Variant Price
                $line[] = $vPrice;

                // Variant Compare At Price
                $line[] = $variant->compare_at_price ? $variant->compare_at_price : '';

                // Variant Requires Shipping
                $line[] = 'true';
                
                // Variant Taxable
                $line[] = 'true';

                // Variant Barcode
                $line[] = $variant->barcode ? $variant->barcode : '';

                // Variant Weight Unit
                $line[] = $variant->weight_unit ? $variant->weight_unit : '';

                // Status
                $line[] = 'active';

                // Add line to CSV without images
                fputcsv($fileNoImg, $line);
                
                // Image Src
                $line[] = isset($image) ? $image->src : '';

                // Image Position
                $line[] = isset($image) ? $image->position : '';

                // Image Alt Text
                $line[] = isset($image) ? $product->title : '';

                fputcsv($file, $line);
    
                $vIndex++;
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
