<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\ProductImage;
use App\Repositories\ProductImageRepository;
use App\Http\Resources\ProductImageResourceCollection;

class ProductImageController extends Controller
{
    protected $categoryProductRepo;

    const ORDER_BY = array('product_id','image_id');
    const ORDER = array('asc', 'desc');

    public function __construct(ProductImageRepository $productImageRepository)
    {
        $this->productImageRepo = $productImageRepository;
    }

    public function index()
    {
        $category_product = ProductImage::all();
        return response([ 'product_image' => ProductImageResource::collection($category_product), 'message' => 'Saved successfully'], 200);
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

            $response = $this->productImageRepo->productImageSection($rawData);
            
            $result = new ProductImageResourceCollection($response);
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
            'product_id' => 'required',
            'image_id' => 'required'
        ];

        $customMessage = [
            'product_id.required' => 'Bidang isian product wajib diisi.',
            'image_id.required' => 'Bidang image style wajib diisi.'
        ];

        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $message) {
                return $message;
            }
        } else {
            try {
                $rawData = [
                    'product_id' => $request['product_id'],
                    'image_id' => $request['image_id']
                ];

                $this->productImageRepo->submit($rawData);

                $result = ['message' => 'success', 'data' => $rawData];
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
            'product_id' => 'required',
            'image_id' => 'required'
        ];

        $customMessage = [
            'product_id.required' => 'Bidang product_id name wajib diisi.',
            'image_id.required' => 'Bidang description style wajib diisi.'
        ];

        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $message) {
                return $message;
            }
        } else {
            try {
                $rawData = [
                    'product_id' => $request['product_id'],
                    'image_id' => $request['image_id']
                ];

                $dataExisting= $this->productImageRepo->productImageById($request['id']);
                if (empty($dataExisting)) {
                    $result = ['code' => 403, 'message' => 'Id section tidak valid.', 'data' => (object)[]];
                    return response()->json($result, 202);
                }

                $this->productImageRepo->action([$request['id']], $rawData);

                $result = ['message' => 'success', 'data' => $rawData];
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
                $dataExisting= $this->productImageRepo->productImageById($request['id']);
                if (empty($dataExisting)) {
                    $result = ['code' => 403, 'message' => 'Id section tidak valid.', 'data' => (object)[]];
                    return response()->json($result, 202);
                }

                $this->productImageRepo->delete([$request['id']]);

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
