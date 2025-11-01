<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorProduct extends Model
{
    protected $table = 'vendor_products';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'addOnsPrice',
        'addOnsTitle',
        'calories',
        'categoryID',
        'createdAt',
        'description',
        'fats',
        'grams',
        'isAvailable',
        'item_attribute',
        'name',
        'nonveg',
        'photo',
        'photos',
        'product_specification',
        'proteins',
        'publish',
        'quantity',
        'takeawayOption',
        'veg',
        'vendorID',
        'migratedBy',
        'disPrice',
        'price',
        'vType',
        'reviewsCount',
        'reviewAttributes',
        'reviewsSum',
        'updatedAt',
        'sizeTitle',
        'sizePrice',
        'attributes',
        'variants',
        'updated_at',
        'categoryTitle',
        'vendorTitle',
    ];
}


