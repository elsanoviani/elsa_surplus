<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Category;
use Carbon\Carbon;

class CategoryRepository
{
    
    public static function categorySection($rawData)
    {
        $data = \DB::table('waizly_db.category')
            ->select('category.name','category.enable');

            $filter = $rawData['filter'] ? $rawData['filter'] : '';
            if($filter != '') {
                $data = $data->where(function($subquery) use($filter){
                    $subquery->whereRaw('LOWER(name) like "%'.$filter.'%"');
                });
            }

            if (strlen($rawData['order_by']) > 0) {
                $data = $data->orderBy($rawData['order_by'], $rawData['order']);
             } else {
                $data = $data->orderBy('category.created_at','desc');
             }

        $datas = $data->paginate($rawData['per_page']);

        return $datas;
    }

    public static function submit($rawData)
    {
        Category::create($rawData);
    }

    public static function categoryById($id)
    {
        $datas = Category::selectRaw('name,enable')
            ->where('id', $id)->first();

        return $datas;
    }

    public static function action($id, $rawData)
    {
        Category::whereIn('id', $id)->update($rawData);
    }

    public static function delete($id)
    {
        Category::whereIn('id', $id)->get()->each->delete();
    }
}
