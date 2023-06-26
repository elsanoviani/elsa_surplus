<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\ProductImage;
use Carbon\Carbon;

class ProductImageRepository
{
    
    public static function productImageSection($rawData)
    {
        $data = \DB::table('surlus_db.product_image')
            ->join('surlus_db.product', 'product_image.product_id', '=', 'product.id')
            ->join('surlus_db.image', 'product_image.image_id', '=', 'image.id')
            ->select('product_image.product_id','product_image.image_id','product.name as product_name','image.name as image_name');

            $filter = $rawData['filter'] ? $rawData['filter'] : '';
            if($filter != '') {
                $data = $data->where(function($subquery) use($filter){
                    $subquery->whereRaw('image_id like "%'.$filter.'%"')
                            ->orWhereRaw('product_id like "%'.$filter.'%"');
                });
            }

            if (strlen($rawData['order_by']) > 0) {
                $data = $data->orderBy($rawData['order_by'], $rawData['order']);
             } else {
                $data = $data->orderBy('product_image.created_at','desc');
             }

        $datas = $data->paginate($rawData['per_page']);

        return $datas;
    }

    public static function submit($rawData)
    {
        ProductImage::create($rawData);
    }

    public static function productImageById($id)
    {
        $datas = ProductImage::selectRaw('image_id,product_id')
            ->where('id', $id)->first();

        return $datas;
    }

    public static function action($id, $rawData)
    {
        ProductImage::whereIn('id', $id)->update($rawData);
    }

    public static function delete($id)
    {
        ProductImage::whereIn('id', $id)->get()->each->delete();
    }
}
