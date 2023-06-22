<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Category;
use App\Repositories\CategoryRepository;
use App\Http\Resources\CategoryResourceCollection;

class CategoryController extends Controller
{
    protected $categoryRepo;

    const ORDER_BY = array('name','enable');
    const ORDER = array('asc', 'desc');

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepo = $categoryRepository;
    }

    public function index()
    {
        $category = Category::all();
        return response([ 'category' => CategoryResource::collection($category), 'message' => 'Saved successfully'], 200);
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

            $response = $this->categoryRepo->categorySection($rawData);

            $result = new CategoryResourceCollection($response);
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
            'enable' => 'required|integer'
        ];

        $customMessage = [
            'name.required' => 'Bidang isian name wajib diisi.',
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
                $rawData = [
                    'name' => $request['name'],
                    'enable' => $request['enable']
                ];

                $this->categoryRepo->submit($rawData);

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
            'name' => 'required|min:3|max:30',
            'enable' => 'required'
        ];

        $customMessage = [
            'name.required' => 'Bidang isian name wajib diisi.',
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
                $rawData = [
                    'name' => $request['name'],
                    'enable' => $request['enable']
                ];

                $dataExisting= $this->categoryRepo->categoryById($request['id']);
                if (empty($dataExisting)) {
                    $result = ['code' => 403, 'message' => 'Id section tidak valid.', 'data' => (object)[]];
                    return response()->json($result, 202);
                }

                $this->categoryRepo->action([$request['id']], $rawData);

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
                $dataExisting= $this->categoryRepo->categoryById($request['id']);
                if (empty($dataExisting)) {
                    $result = ['code' => 403, 'message' => 'Id section tidak valid.', 'data' => (object)[]];
                    return response()->json($result, 202);
                }

                $this->categoryRepo->delete([$request['id']]);

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
