<?php

namespace App\Services\Tenant\Kye;

use App\Models\Tenant\Kye\Kye;
use App\Http\Resources\Tenant\Kye\KyeResource;


class KyeService
{
    protected $kye;
    public function __construct(Kye $kye)
    {
        $this->kye = $kye;
    }
    public function paginate($request, $limit = 25)
    {
        $kye = $this->kye
            ->with([
                'addresses',
                'educations',
                'emergencyContacts',
                'experiences',
                'services'
            ])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($sub) use ($request) {
                    $info = $request->search;
                    $sub->where('full_name', 'like', "%{$info}%")
                        ->orWhere('marital_status', 'like', "%{$info}%")
                        ->orWhere('gender', 'like', "%{$info}%")
                        ->orWhere('blood_group', 'like', "%{$info}%")
                        ->orWhere('citizenship_number', 'like', "%{$info}%")
                        ->orWhere('issue_date', 'like', "%{$info}%")
                        ->orWhere('issue_district', 'like', "%{$info}%");
                });
            })
            ->when($request->filled('marital_status'), function ($query) use ($request) {
                $query->where('marital_status', $request->marital_status);
            })
            ->when($request->filled('gender'), function ($query) use ($request) {
                $query->where('gender', $request->gender);
            })
            ->when($request->filled('blood_group'), function ($query) use ($request) {
                $query->where('blood_group', $request->blood_group);
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->where('date_of_birth_ad', '>=', $request->date_from);
            })
            ->when($request->filled('date_upto'), function ($query) use ($request) {
                $query->where('date_of_birth_ad', '<=', $request->date_upto);
            })
            ->paginate($request->limit ?? $limit);
        return KyeResource::collection($kye);
    }

    public function store($data)
    {
        try {
            if (isset($data['front_image'])) {
                $file = $data['front_image'];

                $filename = time() . '_' . $file->getClientOriginalName();

                $destinationPath = public_path('uploads/kye/');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                $file->move($destinationPath, $filename);

                $data['front_image'] = 'uploads/kye/' . $filename;
            }

            if (isset($data['back_image'])) {
                $file = $data['back_image'];

                $filename = time() . '_' . $file->getClientOriginalName();

                $destinationPath = public_path('uploads/kye/');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                $file->move($destinationPath, $filename);

                $data['back_image'] = 'uploads/kye/' . $filename;
            }
            return $this->kye->create($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $kye = $this->kye
            ->with([
                'addresses',
                'educations',
                'emergencyContacts',
                'experiences',
                'services'
            ])
            ->find($id);

        if (!$kye) {
            return null;
        }
        return $resource ? new KyeResource($kye) : $kye;
    }

    public function update($id, $data)
    {
        try {
            $kye = $this->kye
                ->with([
                    'addresses',
                    'educations',
                    'emergencyContacts',
                    'experiences',
                    'services'
                ])
                ->find($id);
            if (!$kye) {
                return null;
            }

            if (isset($data['front_image'])) {

                if ($kye->front_image && file_exists(public_path($kye->front_image))) {
                    unlink(public_path($kye->front_image));
                }

                $file = $data['front_image'];
                $filename = time() . '_' . $file->getClientOriginalName();

                $destinationPath = public_path('uploads/kye/');

                $file->move($destinationPath, $filename);

                $data['front_image'] = 'uploads/kye/' . $filename;
            }

            if (isset($data['back_image'])) {

                if ($kye->back_image && file_exists(public_path($kye->back_image))) {
                    unlink(public_path($kye->back_image));
                }

                $file = $data['back_image'];
                $filename = time() . '_' . $file->getClientOriginalName();

                $destinationPath = public_path('uploads/kye/');

                $file->move($destinationPath, $filename);

                $data['back_image'] = 'uploads/kye/' . $filename;
            }
            $kye->update($data);

            return $kye->fresh();
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $kye = $this->find($id);
            if (!$kye) {
                return false;
            }
            return $kye->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
