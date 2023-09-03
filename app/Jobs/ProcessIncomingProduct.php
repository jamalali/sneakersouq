<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Actions\Shopify\GetProductByTitle;
use App\Jobs\UpdateShopifyProduct;
use App\Jobs\CreateShopifyProduct;

class ProcessIncomingProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public $product
    ) {}

    public function handle(): void
    {
        $shopProduct = GetProductByTitle::get($this->product->title);

        if ($shopProduct) {
            UpdateShopifyProduct::dispatch($this->product, $shopProduct);
        } else {
            CreateShopifyProduct::dispatch($this->product);
        }
    }
}
