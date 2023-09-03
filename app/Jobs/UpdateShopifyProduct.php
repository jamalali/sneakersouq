<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Actions\Shopify\UpdateProduct;

class UpdateShopifyProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public $product,
        public $shopifyProduct
    ) {}
    
    public function handle(): void
    {
        UpdateProduct::update($this->product, $this->shopifyProduct);
    }
}
