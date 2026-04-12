<?php

namespace App\Services\Tenant\Scheme;

use App\Models\Tenant\Scheme\Scheme;
use App\Http\Resources\Tenant\Scheme\SchemeResource;

class SchemeService
{
    protected $scheme;
    public function __construct(Scheme $scheme)
    {
        $this->scheme = $scheme;
    }
    public function paginate($request, $limit = 25)
    {
        $scheme = $this->scheme
            ->with('supplier')
            ->when($request->filled('supplier_id'), function ($query) use ($request) {
                $query->where('supplier_id', $request->supplier_id);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('scheme_type'), function ($query) use ($request) {
                $query->where('scheme_type', $request->scheme_type);
            })
            ->orderBy('id', 'ASC')
            ->paginate($request->limit ?? $limit);

        return SchemeResource::collection($scheme);
    }

    public function search($request, $limit = 10)
    {
        $scheme = $this->scheme
            ->with('supplier')
            ->when($request->filled('supplier_id'), function ($query) use ($request) {
                $query->where('supplier_id', $request->supplier_id);
            })
            ->when(
                $request->filled('status'),
                fn($q) =>
                $q->where('status', 'like', "%{$request->status}%")
            )
            ->latest()
            ->limit($limit)
            ->get();
        return SchemeResource::collection($scheme);
    }

    public function store($data)
    {
        try {
            if (isset($data['image'])) {
                $file = $data['image'];

                $filename = time() . '_' . $file->getClientOriginalName();

                $destinationPath = public_path('uploads');

                $file->move($destinationPath, $filename);

                $data['image'] = 'uploads/' . $filename;
            }

            $data['status'] = $data['status'] ?? 'pending';

            return $this->scheme->create($data);
        } catch (\Exception $ex) {
            dd($ex->getMessage());
        }
    }

    public function find($id, $resource = false)
    {
        $scheme = $this->scheme->with('supplier')->find($id);
        if (!$scheme) {
            return null;
        }
        return $resource ? new SchemeResource($scheme) : $scheme;
    }

    public function update($id, $data)
    {
        try {
            $scheme = $this->find($id);
            if (!$scheme) {
                return false;
            }
            if (isset($data['image'])) {

                if ($scheme->image && file_exists(public_path($scheme->image))) {
                    unlink(public_path($scheme->image));
                }

                $file = $data['image'];
                $filename = time() . '_' . $file->getClientOriginalName();

                $destinationPath = public_path('uploads');

                $file->move($destinationPath, $filename);

                $data['image'] = 'uploads/' . $filename;
            }

            $scheme->update($data);

            return $scheme;
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $scheme = $this->find($id);
            if (!$scheme) {
                return false;
            }

            if ($scheme->image && file_exists(public_path($scheme->image))) {
                unlink(public_path($scheme->image));
            }

            return $scheme->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
