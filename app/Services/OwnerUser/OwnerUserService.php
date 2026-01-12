<?php

namespace App\Services\OwnerUser;

use App\Http\Resources\OwnerUserResource;
use App\Models\OwnerUser\OwnerUser;
use App\Services\Service;
use Illuminate\Http\UploadedFile;

class OwnerUserService extends Service
{
    protected $uploadPath = 'owner-user';

    protected $ownerUser;

    public function __construct(OwnerUser $ownerUser)
    {
        $this->ownerUser = $ownerUser;
    }

    public function paginate($limit = 25, $request, $ownerUser)
    {
        $adminUsers = $this->ownerUser->where(function ($query) use ($request) {

            if ($request->filled('name')) {
                $query->where('first_name', 'like', '%' . $request->name . '%')
                    ->orWhere('last_name', 'like', '%' . $request->name . '%')
                    ->orWhere('email', 'like', '%' . $request->name . '%');
            }

            if ($request->filled('user_type')) {
                $query->whereUserType($request->user_type);
            }

            if ($request->filled('is_active')) {
                $query->whereIsActive($request->is_active);
            }
        })->orderBy('id', "DESC");

        $adminUsers = $adminUsers->whereNotIn('id', [$ownerUser->id])->paginate($limit);

        return OwnerUserResource::collection($adminUsers);
    }


    public function store($data)
    {
        try {
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
            return $this->ownerUser->create($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($userId, $resource = true)
    {
        $ownerUser = $this->ownerUser->find($userId);
        if ($ownerUser) {
            return $resource ? new OwnerUserResource($ownerUser) : $ownerUser;
        }
        return null;
    }

    public function update($id, $data)
    {
        try {
            $ownerUser = $this->find($id);
            if ($ownerUser->is_mfa_enable == 0 && isset($data['is_email_authentication_enabled']) && !$data['is_email_authentication_enabled']) {
                $data['mfa_secret_code'] = null;
                $data['mfa_authentication_image'] = null;
            }
            if (isset($data['file']) && $data['file'] !== null) {
                 if (!empty($ownerUser->image)) {
                    $this->deleteFile($this->uploadPath, $ownerUser->image);
                }
                $data['image'] = $this->uploadFile($data['file'], $this->uploadPath, 'image');
            }
            if (isset($data['doc_one_file']) && $data['doc_one_file'] !== null) {
                 if (!empty($ownerUser->doc_one)) {
                    $this->deleteFile($this->uploadPath, $ownerUser->doc_one);
                }
                $data['doc_one'] = $this->uploadFile($data['doc_one_file'], $this->uploadPath, 'doc/one', 'private');
            }
            if (isset($data['doc_two_file']) && $data['doc_two_file'] !== null) {
                 if (!empty($ownerUser->doc_two)) {
                    $this->deleteFile($this->uploadPath, $ownerUser->doc_two);
                }
                $data['doc_two'] = $this->uploadFile($data['doc_two_file'], $this->uploadPath, 'doc/two', 'private');
            }
            if (isset($data['doc_three_file']) && $data['doc_three_file'] !== null) {
                 if (!empty($ownerUser->doc_three)) {
                    $this->deleteFile($this->uploadPath, $ownerUser->doc_three);
                }
                $data['doc_three'] = $this->uploadFile($data['doc_three_file'], $this->uploadPath, 'doc/three', 'private');
            }
            return $ownerUser->update($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $ownerUser = $this->find($id);
            return $ownerUser->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function getUserForLogin($email, $password)
    {
        $ownerUser = $this->ownerUser->whereEmail($email)->orWhere('username', $email)->first();
        if (empty($ownerUser))
            return false;

        if (Hash::check($password, $ownerUser->password))
            return $ownerUser;

        return false;
    }

    public function updatePassword($id, $data)
    {
        try {
            $ownerUser = $this->find($id);
            if (Hash::check($data['current_password'], $ownerUser->password)) {
                if (isset($data['autoGenerate']) && $data['autoGenerate'] == 1) {
                    $newPassword = getRandomString(10);
                    $data['password'] = getHashedPassword($newPassword);
                } else {
                    if ($data['current_password'] == $data['new_password']) {
                        return "sa";
                    } else {
                        $data['password'] = getHashedPassword($data['new_password']);
                    }
                }
                $ownerUser->update(['password' => $data['password']]);
                return $ownerUser;
            }
            return "InvalidPassword";
        } catch (\Exception $ex) {
            return false;
        }
    }
}
