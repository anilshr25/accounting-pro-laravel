<?php

namespace App\Services\OwnerUser;

use App\Http\Resources\OwnerUser\OwnerUserResource;
use App\Models\OwnerUser\OwnerUser;
use App\Services\Service;
use App\Models\Business\Business;
use Illuminate\Support\Facades\DB;

class OwnerUserService extends Service
{
    protected $uploadPath = 'owner-user';

    protected $ownerUser;

    public function __construct(OwnerUser $ownerUser)
    {
        $this->ownerUser = $ownerUser;
    }

    public function paginate($limit = 25, $request)
    {
        $ownerUsers = $this->ownerUser
            ->with(['businesses'])
            ->when($request->filled('name'), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->where('first_name', 'like', '%' . $request->name . '%')
                        ->orWhere('last_name', 'like', '%' . $request->name . '%')
                        ->orWhere('email', 'like', '%' . $request->name . '%');
                });
            })
            ->when($request->filled('is_active'), function ($query) use ($request) {
                $query->where('is_active', $request->is_active);
            })
            ->orderByDesc('id')
            ->paginate($limit);

        return OwnerUserResource::collection($ownerUsers);
    }

    public function store($data)
    {
        try {
            return DB::transaction(function () use ($data) {

                $businessIds = $data['business_ids'];

                $businesses = Business::whereIn('id', $businessIds)->get();

                if ($businesses->count() !== count($businessIds)) {
                    return false;
                }

                unset($data['business_ids']);

                if (isset($data['file']) && $data['file'] !== null) {
                    $data['image'] = $this->uploadFile($data['file'], $this->uploadPath, 'image');
                }
                if (isset($data['doc_one_file']) && $data['doc_one_file'] !== null) {
                    $data['doc_one'] = $this->uploadFile($data['doc_one_file'], $this->uploadPath, 'doc/one', 'private');
                }
                if (isset($data['doc_two_file']) && $data['doc_two_file'] !== null) {
                    $data['doc_two'] = $this->uploadFile($data['doc_two_file'], $this->uploadPath, 'doc/two', 'private');
                }
                if (isset($data['doc_three_file']) && $data['doc_three_file'] !== null) {
                    $data['doc_three'] = $this->uploadFile($data['doc_three_file'], $this->uploadPath, 'doc/three', 'private');
                }
                $data['password'] = getHashedPassword($data['password']);

                $data['approved_by'] = auth('admin')->id();
                $data['is_active'] = true;
                $data['status'] = 'approved';

                $ownerUser = $this->ownerUser->create($data);

                $ownerData = [];

                foreach (array_values(array_unique($businessIds)) as $index => $businessId) {
                    $ownerData[$businessId] = [
                        'is_primary' => $index === 0,
                    ];
                }

                $ownerUser->businesses()->sync($ownerData);

                return $ownerUser->load('businesses');
            });
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($userId, $resource = true)
    {
        $ownerUser = $this->ownerUser
            ->with('businesses')
            ->find($userId);

        if ($ownerUser) {
            return $resource
                ? new OwnerUserResource($ownerUser)
                : $ownerUser;
        }

        return null;
    }

    public function update($id, $data)
    {
        try {
            return DB::transaction(function () use ($id, $data) {

                $ownerUser = $this->find($id, false);

                if (!$ownerUser) {
                    return false;
                }

                if (array_key_exists('business_ids', $data)) {

                    $businessIds = array_values(
                        array_unique($data['business_ids'])
                    );

                    $businesses = Business::whereIn('id', $businessIds)->get();

                    if ($businesses->count() !== count($businessIds)) {
                        return false;
                    }

                    unset($data['business_ids']);

                    $ownerData = [];

                    foreach ($businessIds as $index => $businessId) {
                        $ownerData[$businessId] = [
                            'is_primary' => $index === 0,
                        ];
                    }

                    $ownerUser->businesses()->sync($ownerData);
                }

                if (!empty($data['password'])) {
                    $data['password'] = getHashedPassword($data['password']);
                } else {
                    unset($data['password']);
                }

                if (isset($data['file']) && $data['file'] !== null) {
                    if (!empty($ownerUser->image)) {
                        $this->deleteFile(
                            $this->uploadPath,
                            $ownerUser->image
                        );
                    }

                    $data['image'] = $this->uploadFile(
                        $data['file'],
                        $this->uploadPath,
                        'image'
                    );
                }

                if (isset($data['doc_one_file']) && $data['doc_one_file'] !== null) {
                    if (!empty($ownerUser->doc_one)) {
                        $this->deleteFile(
                            $this->uploadPath,
                            $ownerUser->doc_one
                        );
                    }

                    $data['doc_one'] = $this->uploadFile(
                        $data['doc_one_file'],
                        $this->uploadPath,
                        'doc/one',
                        'private'
                    );
                }

                if (isset($data['doc_two_file']) && $data['doc_two_file'] !== null) {
                    if (!empty($ownerUser->doc_two)) {
                        $this->deleteFile(
                            $this->uploadPath,
                            $ownerUser->doc_two
                        );
                    }

                    $data['doc_two'] = $this->uploadFile(
                        $data['doc_two_file'],
                        $this->uploadPath,
                        'doc/two',
                        'private'
                    );
                }

                if (isset($data['doc_three_file']) && $data['doc_three_file'] !== null) {
                    if (!empty($ownerUser->doc_three)) {
                        $this->deleteFile(
                            $this->uploadPath,
                            $ownerUser->doc_three
                        );
                    }

                    $data['doc_three'] = $this->uploadFile(
                        $data['doc_three_file'],
                        $this->uploadPath,
                        'doc/three',
                        'private'
                    );
                }

                unset(
                    $data['file'],
                    $data['doc_one_file'],
                    $data['doc_two_file'],
                    $data['doc_three_file']
                );

                $ownerUser->update($data);

                return $ownerUser->load('businesses');
            });
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $ownerUser = $this->find($id, false);
            return $ownerUser->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
