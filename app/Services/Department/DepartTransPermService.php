<?php

namespace App\Services\Department;

use App\Models\TransPerm;

class DepartTransPermService
{
  public function findAll()
  {
    return TransPerm::all();
  }

  public function findById(string $id)
  {
    return TransPerm::findOrFail($id);
  }

  public function findByCode(string $code)
  {
    return TransPerm::where('code', $code)->firstOrFail();
  }

  public static function getReceiveProduct(): TransPerm
  {
    return TransPerm::where('code', 'getProduct')->firstOrFail();
  }

  public static  function getSendProduct(): TransPerm
  {
    return TransPerm::where('code', 'sendProduct')->firstOrFail();
  }
}
