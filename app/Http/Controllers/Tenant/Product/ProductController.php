<?php

namespace App\Http\Controllers\Tenant\Product;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Product\ProductRequest;
use App\Services\Tenant\Product\ProductService;

class ProductController extends Controller
{
    protected $Product;

    public function __construct(ProductService $Product)
    {
        $this->Product = $Product;
    }

    public function index(Request $request)
    {
        return $this->Product->paginate($request, 25);
    }

    public function store(ProductRequest $request)
    {
        $Product = $this->Product->store($request->validated());

        if ($Product)
            return response(['status' => 'OK'], 200);

        return response(['status' => 'ERROR'], 500);
    }

    public function show($id)
    {
        $Product = $this->Product->find($id, true);

        return response(['data' => $Product], 200);
    }

    public function update(ProductRequest $request, $id)
    {
        $Product = $this->Product->update($id, $request->validated());

        if ($Product)
            return response(['status' => 'OK'], 200);

        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->Product->delete($id))
            return response(['status' => 'OK'], 200);

        return response(['status' => 'ERROR'], 500);
    }
}
