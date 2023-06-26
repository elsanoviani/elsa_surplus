<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\CategoryProduct;
use Carbon\Carbon;

class CategoryProductRepository
{
    
    public static function categoryProductSection($rawData)
    {
        $data = \DB::table('waizly_db.category_product')
            ->join('waizly_db.product', 'category_product.product_id', '=', 'product.id')
            ->join('waizly_db.category', 'category_product.category_id', '=', 'category.id')
            ->select('category_product.product_id','category_product.category_id','product.name as product_name','category.name as category_name');

            $filter = $rawData['filter'] ? $rawData['filter'] : '';
            if($filter != '') {
                $data = $data->where(function($subquery) use($filter){
                    $subquery->whereRaw('category_id like "%'.$filter.'%"')
                            ->orWhereRaw('product_id like "%'.$filter.'%"');
                });
            }

            if (strlen($rawData['order_by']) > 0) {
                $data = $data->orderBy($rawData['order_by'], $rawData['order']);
             } else {
                $data = $data->orderBy('category_product.created_at','desc');
             }

        $datas = $data->paginate($rawData['per_page']);

        return $datas;
    }

    public static function submit($rawData)
    {
        CategoryProduct::create($rawData);
    }

    public static function categoryProductById($id)
    {
        $datas = CategoryProduct::selectRaw('category_id,product_id')
            ->where('id', $id)->first();

        return $datas;
    }

    public static function action($id, $rawData)
    {
        CategoryProduct::whereIn('id', $id)->update($rawData);
    }

    public static function delete($id)
    {
        CategoryProduct::whereIn('id', $id)->get()->each->delete();
    }
}
