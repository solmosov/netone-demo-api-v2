<?php

namespace App\Services\Department;

use App\Exceptions\ServerErrorException;
use App\Models\Depart;
use App\Models\Department\DepartStatus;
use App\Models\DepartTransPermProperty;
use App\Models\Polka;
use App\Models\TransPerm;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DepartService
{
    public function findAll(): array
    {
        $data = Depart::with([
            'users',
            'departStock.polka',
            'departStock.product',
            'departStatus',
            'polka'
        ])->get();

        return [
            'data' => $data,
        ];
    }

    public function findAllByTransPerm(array $queryParams)
    {
        $permCode = $queryParams['perm_code'];
        $departId = $queryParams['depart_id'];

        $permId = TransPerm::where('code', $permCode)
            ->firstOrFail()
            ->id;
        $propertyId = DepartTransPermProperty::where('trans_perm_id', $permId)
            ->where('depart_id', $departId)
            ->firstOrFail()
            ->id;

        return Depart::whereHas('departTransPropertyDetails', function ($q) use ($propertyId) {
            $q->where('property_id', $propertyId);
        })
            ->get();
    }

    public function findOne(int $id): Depart
    {
        return Depart::with([
            'users',
            'departStock.polka',
            'departStock.product',
            'departStatus',
            'transPermProperty',
            'polka',
        ])->findOrFail($id);
    }


    public function create(array $data): array
    {
        // Req Data
        $userList = $data['user_list'];
        $polkaList = $data['polka_list'];
        $name = $data['name'];
        $comment = $data['comment'] ?? null;
        $statusName = $data['status_name'];

        DB::beginTransaction();
        try {
            // New Depart
            $newDepart = Depart::create([
                'name' => $name,
                'comment' => $comment,
            ]);

            // Attach User
            $newDepart->users()->attach($userList);

            // Change owner of polka
            foreach ($polkaList as $polkaId) {
                $polka = Polka::findOrFail($polkaId);
                $polka->owner_id = $newDepart->id;
                $polka->save();
            }

            // Department Status
            DepartStatus::create([
                'depart_id' => $newDepart->id,
                'name' => $statusName,
            ]);

            DB::commit();

            return [
                'message' => "Yangi bo'lim qo'shildi",
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ServerErrorException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function assignPerm(array $data): array
    {
        // Req Data
        $permId = $data['perm_id'];
        $ownerDepartId = $data['owner_depart_id'];
        $departList = $data['depart_list'];

        // Owner Depart
        $ownerDepart = Depart::findOrFail($ownerDepartId);

        $property = DepartTransPermProperty::where('trans_perm_id', $permId)->where('depart_id', $ownerDepartId)->first();

        DB::beginTransaction();
        try {


            if (!$property) {
                // Create Property
                $property = DepartTransPermProperty::create([
                    'depart_id' => $ownerDepart->id,
                    'trans_perm_id' => $permId
                ]);
            }

            // Create Property Details
            $transPermDetailsList = [];
            foreach ($departList as $departId) {
                $transPermDetailsList[] = [
                    'property_id' => $property->id,
                    'depart_id' => $departId,
                ];
            }
            $property->details()->createMany($transPermDetailsList);

            // Finish
            DB::commit();

            return [
                'message' => "$ownerDepart->name bo'limga ruxsat qo'shildi",
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ServerErrorException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function assignNewUser(array $data)
    {
        // Req Data
        $departId = $data['depart_id'];
        $userId = $data['user_id'];

        $attachUser = User::findOrFail($userId);
        $department = $this->findOne($departId);

        $department->users()->attach($attachUser->id);

        return [
            'depart' => $department,
            'attach_user' => $attachUser
        ];
    }

    public function removeAssignUser(array $reqData)
    {
        DB::table('depart_user')
            ->where('depart_id', $reqData['depart_id'])
            ->where('user_id', $reqData['user_id'])
            ->delete();

        // Department
        $attachUser = User::findOrFail($reqData['depart_id']);
        $department = $this->findOne($reqData['user_id']);

        return [
            'depart' => $department,
            'attach_user' => $attachUser
        ];
    }

    public function update(array $data, int $id): array
    {
        // Req Data
        $userList = $data['user_list'] ?? [];
        $polkaId = $data['polka_id'] ?? null;

        DB::beginTransaction();

        try {
            $depart = Depart::with('users', 'departStock')->findOrFail($id);
            $depart->update($data);

            // User
            if (!empty($userList)) {
                $depart->users()->detach();
                $depart->users()->attach($userList);
            }

            // Polka
            if ($polkaId) {
                // Update Before Polka owner_id
                Polka::where('id', $depart->polka_id)->update(['owner_id' => null]);

                // Find New Polka
                $polka = Polka::whereNull('owner_id')
                    ->whereNull('owner_id')
                    ->findOrFail($id);

                $polka->owner_id = $depart->id;
                $polka->save();
            }

            DB::commit();

            return [
                'message' => "$depart->name yangilandi",
                'data' => $depart
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ServerErrorException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function delete(int $id): array
    {
        $depart = Depart::with('users')->findOrFail($id);

        return [
            'message' => "$depart->name o'chirildi",
            'data' => $depart
        ];
    }
}
