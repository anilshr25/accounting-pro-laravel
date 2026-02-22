<?php

namespace App\Services\Tenant\Product;

use App\Models\Tenant\Product\Product;
use App\Http\Resources\Tenant\Product\ProductResource;
use Illuminate\Support\Facades\DB;

class ProductService
{
    protected $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function paginate($request, $limit = 25)
    {
        $products = $this->product
            ->when($request->filled('product_name'), function ($query) use ($request) {
                $query->where('product_name', 'like', "%{$request->product_name}%");
            })
            ->when($request->filled('unit'), function ($query) use ($request) {
                $query->where('unit', $request->unit);
            })
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->where('category', $request->category);
            })
            ->when($request->filled('code'), function ($query) use ($request) {
                $query->where('code', $request->code);
            })
            ->orderBy('created_at', 'ASC')
            ->paginate($request->limit ?? $limit);

        return ProductResource::collection($products);
    }

    public function store(array $data)
    {
        try {
            return DB::transaction(function () use ($data) {
                return $this->product->create($data);
            });
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $product = $this->product->find($id);

        if (!$product) {
            return null;
        }

        return $resource ? new ProductResource($product) : $product;
    }

    public function update($id, array $data)
    {
        try {
            return DB::transaction(function () use ($id, $data) {

                $product = $this->find($id);

                if (!$product) {
                    return false;
                }

                return $product->update($data);
            });
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $product = $this->find($id);

            if (!$product) {
                return false;
            }

            return $product->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
