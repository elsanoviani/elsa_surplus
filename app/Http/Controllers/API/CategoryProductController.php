<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\CategoryProduct;
use App\Repositories\CategoryProductRepository;
use App\Http\Resources\CategoryProductResourceCollection;

class CategoryProductController extends Controller
{
    protected $categoryProductRepo;

    const ORDER_BY = array('product_id','category_id');
    const ORDER = array('asc', 'desc');

    public function __construct(CategoryProductRepository $categoryProductRepository)
    {
        $this->categoryProductRepo = $categoryProductRepository;
    }

    public function index()
    {
        $category_product = CategoryProduct::all();
        return response([ 'category_product' => CategoryProductResource::collection($category_product), 'message' => 'Saved successfully'], 200);
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

            $response = $this->categoryProductRepo->categoryProductSection($rawData);
            
            $result = new CategoryProductResourceCollection($response);
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
            'category_id' => 'required',
            'product_id' => 'required'
        ];

        $customMessage = [
            'category_id.required' => 'Bidang isian name wajib diisi.',
            'product_id.required' => 'Bidang description style wajib diisi.'
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
                    'category_id' => $request['category_id'],
                    'product_id' => $request['product_id']
                ];

                $this->categoryProductRepo->submit($rawData);

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
            'category_id' => 'required',
            'product_id' => 'required'
        ];

        $customMessage = [
            'category_id.required' => 'Bidang isian name wajib diisi.',
            'product_id.required' => 'Bidang description style wajib diisi.'
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
                    'category_id' => $request['category_id'],
                    'product_id' => $request['product_id']
                ];

                $dataExisting= $this->categoryProductRepo->categoryProductById($request['id']);
                if (empty($dataExisting)) {
                    $result = ['code' => 403, 'message' => 'Id section tidak valid.', 'data' => (object)[]];
                    return response()->json($result, 202);
                }

                $this->categoryProductRepo->action([$request['id']], $rawData);

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
                $dataExisting= $this->categoryProductRepo->categoryProductById($request['id']);
                if (empty($dataExisting)) {
                    $result = ['code' => 403, 'message' => 'Id section tidak valid.', 'data' => (object)[]];
                    return response()->json($result, 202);
                }

                $this->categoryProductRepo->delete([$request['id']]);

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
