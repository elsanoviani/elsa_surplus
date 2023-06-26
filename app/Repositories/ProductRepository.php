<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Product;
use Carbon\Carbon;

class ProductRepository
{
    
    public static function productSection($rawData)
    {
        $data = \DB::table('surplus_db.product')
            ->select('product.name','product.description','product.enable');

            $filter = $rawData['filter'] ? $rawData['filter'] : '';
            if($filter != '') {
                $data = $data->where(function($subquery) use($filter){
                    $subquery->whereRaw('LOWER(name) like "%'.$filter.'%"')
                            ->orWhereRaw('LOWER(description) like "%'.$filter.'%"');
                });
            }

            if (strlen($rawData['order_by']) > 0) {
                $data = $data->orderBy($rawData['order_by'], $rawData['order']);
             } else {
                $data = $data->orderBy('product.created_at','desc');
             }

        $datas = $data->paginate($rawData['per_page']);

        return $datas;
    }

    public static function submit($rawData)
    {
        Product::create($rawData);
    }

    public static function productById($id)
    {
        $datas = Product::selectRaw('name,description,enable')
            ->where('id', $id)->first();

        return $datas;
    }

    public static function action($id, $rawData)
    {
        Product::whereIn('id', $id)->update($rawData);
    }

    public static function delete($id)
    {
        Product::whereIn('id', $id)->get()->each->delete();
    }
}
