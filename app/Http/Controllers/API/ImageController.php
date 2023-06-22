<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Image;
use App\Repositories\ImageRepository;
use App\Http\Resources\ImageResourceCollection;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    protected $productRepo;

    const ORDER_BY = array('name','file', 'enable');
    const ORDER = array('asc', 'desc');

    public function __construct(ImageRepository $imageRepository)
    {
        $this->imageRepo = $imageRepository;
    }

    public function index()
    {
        $image = Image::all();
        return response([ 'image' => ImageResource::collection($product), 'message' => 'Saved successfully'], 200);
    }

    public function list(Request $request)
    {
        $datas = (object)array();
        try {
            $page = (int) $request->page;
            $perPage = (int) $request->per_page < 1 ? 10 : $request->per_page;

            $orderBy = $request->order_by ?? '';
            $filter = $request->filter ?? '';
            $order = $request->order ?? '';
            $orderBy = (!($this->orderByListSection($orderBy, $order))) ? "" : $orderBy;

            $rawData = [
                'page' => $page,
                'per_page' => $perPage,
                'filter' => $filter,
                'order_by' => $orderBy,
                'order' => $order
            ];

            $response = $this->imageRepo->imageSection($rawData);

            $result = new ImageResourceCollection($response);
            return $this->returnJsonSuccess($result->toArray($request));
        } catch (\Exception $e) {
            $result = [
                'code'      => 400,
                'message'   => $e->getMessage(),
                'data'      => $datas
            ];
        }
        return response()->json($result, 202);
    }

    public function submit(Request $request)
    {
        // Validation
        $rules = [
            'name' => 'required|min:3|max:30',
            'file' => 'required|file|mimes:png',
            'enable' => 'required'
        ];

        $customMessage = [
            'name.required' => 'Bidang isian name wajib diisi.',
            'file.required' => 'Bidang file style wajib diisi.',
            'enable.required' => 'Bidang isian enable wajib diisi.'
        ];

        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $message) {
                return $message;
            }
        } else {
            try {

                $fileName = $request->file->getClientOriginalName();
                $filePath = 'image/' . $fileName;
        
                $isFileUploaded = Storage::disk('public')->put($filePath, file_get_contents($request->file));
        
                // File URL to access the video in frontend
                $url = Storage::disk('public')->url($filePath);
        
                if ($isFileUploaded) {
                    $rawData = [
                        'name' => $request['name'],
                        'file' => $filePath,
                        'enable' => $request['enable']
                    ];

                    $this->imageRepo->submit($rawData);

                    $result = ['message' => 'success', 'data' => $rawData];
                    return $this->returnJsonSuccess($result);
                }
                 $result = ['message' => 'error', 'data' => 'error'];
                return $this->returnJsonSuccess($result);
            } catch (\Exception $e) {
                $result = [
                    'code'      => 400,
                    'message'   => $e->getMessage(),
                    'data'      => (object)[]
                ];
            }
        }
        return response()->json($result, 202);
    }

    public function update(Request $request)
    {
        $rules = [
            'name' => 'required|min:3|max:30',
            'file' => 'required|file|mimes:png',
            'enable' => 'required'
        ];

        $customMessage = [
            'name.required' => 'Bidang isian name wajib diisi.',
            'file.required' => 'Bidang file style wajib diisi.',
            'enable.required' => 'Bidang isian enable wajib diisi.'
        ];

        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $message) {
                return $message;
            }
        } else {
            try {
                $fileName = $request->file->getClientOriginalName();
                $filePath = 'image/' . $fileName;
        
                $isFileUploaded = Storage::disk('public')->put($filePath, file_get_contents($request->file));
        
                // File URL to access the video in frontend
                $url = Storage::disk('public')->url($filePath);
        
                if ($isFileUploaded) {
        
                    $rawData = [
                        'name' => $request['name'],
                        'file' => $filePath,
                        'enable' => $request['enable']
                    ];

                    $dataExisting= $this->imageRepo->imageById($request['id']);
                    if (empty($dataExisting)) {
                        $result = ['code' => 403, 'message' => 'Id section tidak valid.', 'data' => (object)[]];
                        return response()->json($result, 202);
                    }

                    $this->imageRepo->action([$request['id']], $rawData);

                    $result = ['message' => 'success', 'data' => $rawData];
                    return $this->returnJsonSuccess($result);
                }

                $result = ['message' => 'error', 'data' => 'error'];
                return $this->returnJsonSuccess($result);
            } catch (\Exception $e) {
                $result = [
                    'code'      => 400,
                    'message'   => $e->getMessage(),
                    'data'      => (object)[]
                ];
            }
        }
        return response()->json($result, 202);
    }

    public function delete(Request $request)
    {
        // Validation
        $rules = [
            'id' => 'required',
        ];

        $customMessage = [
            'id.required' => 'Bidang isian id wajib diisi.'
        ];

        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $message) {
                return $message;
            }
        } else {
            try {
                $dataExisting= $this->imageRepo->imageById($request['id']);
                if (empty($dataExisting)) {
                    $result = ['code' => 403, 'message' => 'Id section tidak valid.', 'data' => (object)[]];
                    return response()->json($result, 202);
                }

                $this->imageRepo->delete([$request['id']]);

                $result = ['message' => 'success', 'data' => (object)[]];
                return $this->returnJsonSuccess($result);
            } catch (\Exception $e) {
                $result = [
                    'code'      => 400,
                    'message'   => $e->getMessage(),
                    'data'      => (object)[]
                ];
            }
        }
        return response()->json($result, 202);
    }

    private function orderByListSection($orderBy, $order)
    {
        $orderBy = strtolower($orderBy);
        $order = strtolower($order);

        $result = ((in_array($orderBy, self::ORDER_BY)) && (in_array($order, self::ORDER))) ? true : false;

        return $result;
    }

    protected function returnJsonSuccess($msg = [])
    {
        $result = [
            'success' => true,
            'code' => 200
        ];
        $result = array_merge($result, $msg);
        return response()->json($result);
    }
}
