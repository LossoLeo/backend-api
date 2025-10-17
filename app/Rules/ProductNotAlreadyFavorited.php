<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Product;

class ProductNotAlreadyFavorited implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $product = Product::where('external_id', $value)->first();

        if (!$product) {
            return;
        }

        $alreadyFavorited = auth()->user()
            ->products()
            ->where('product_id', $product->id)
            ->exists();

        if ($alreadyFavorited) {
            $fail(trans('favorites.already_exists', [], 'pt_BR'));
        }
    }
}
