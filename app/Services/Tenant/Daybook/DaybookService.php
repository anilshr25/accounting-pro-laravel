<?php

namespace App\Services\Tenant\Daybook;

use App\Models\Tenant\Daybook\Daybook;
use App\Http\Resources\Tenant\Daybook\DaybookResource;

class DaybookService
{
    protected $daybook;

    public function __construct(Daybook $daybook)
    {
        $this->daybook = $daybook;
    }

    public function paginate($request, $limit = 25)
    {
        $daybook = $this->daybook->paginate($request->limit ?? $limit);
        return DaybookResource::collection($daybook);
    }

    public function store($data)
    {
        try {
            return $this->daybook->create($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id)
    {
        return $this->daybook->find($id);
    }

    public function update($id, $data)
    {
        try {
            $daybook = $this->find($id);
            if (!$daybook) {
                return false;
            }
            return $daybook->update($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $daybook = $this->find($id);
            if (!$daybook) {
                return false;
            }
            return $daybook->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
