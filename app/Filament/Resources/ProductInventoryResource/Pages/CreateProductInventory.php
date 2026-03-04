<?php
namespace App\Filament\Resources\ProductInventoryResource\Pages;
use App\Filament\Resources\ProductInventoryResource;
use App\Models\YgoProduct;
use Filament\Resources\Pages\CreateRecord;

class CreateProductInventory extends CreateRecord
{
    protected static string $resource = ProductInventoryResource::class;

    public function mount(): void
    {
        parent::mount();

        $productId = request()->query('product_id');
        if (!$productId) return;

        $product = YgoProduct::with('type')->find($productId);
        if (!$product) return;

        $this->form->fill([
            'ygo_product_id'        => $product->id,
            'market_price_snapshot' => $product->market_price,
            'selling_price'         => $product->market_price,
            '_product_name'         => $product->name,
            '_product_type'         => $product->type?->description ?? '-',
            '_product_description'  => $product->description,
            '_product_image'        => $product->image_url,
        ]);
    }
}
