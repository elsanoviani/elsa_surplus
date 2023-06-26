<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Image;
use Carbon\Carbon;

class ImageRepository
{
    
    public static function imageSection($rawData)
    {
        $data = \DB::table('surplus_db.image')
            ->select('image.name','image.file','image.enable');

            $filter = $rawData['filter'] ? $rawData['filter'] : '';
            if($filter != '') {
                $data = $data->where(function($subquery) use($filter){
                    $subquery->whereRaw('LOWER(name) like "%'.$filter.'%"')
                            ->orWhereRaw('LOWER(file) like "%'.$filter.'%"');
                });
            }

            if (strlen($rawData['order_by']) > 0) {
                $data = $data->orderBy($rawData['order_by'], $rawData['order']);
             } else {
                $data = $data->orderBy('image.created_at','desc');
             }

        $datas = $data->paginate($rawData['per_page']);

        return $datas;
    }

    public static function submit($rawData)
    {
        Image::create($rawData);
    }

    public static function imageById($id)
    {
        $datas = Image::selectRaw('name,file,enable')
            ->where('id', $id)->first();

        return $datas;
    }

    public static function action($id, $rawData)
    {
        Image::whereIn('id', $id)->update($rawData);
    }

    public static function delete($id)
    {
        Image::whereIn('id', $id)->get()->each->delete();
    }
}
