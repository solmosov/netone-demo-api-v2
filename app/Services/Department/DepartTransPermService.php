<?php

namespace App\Services\Department;

use App\Models\TransPerm;

class DepartTransPermService
{
  public static function getReceiveProduct(): TransPerm
  {
    return TransPerm::where('code', 'getProduct')->firstOrFail();
  }

  public static  function getSendProduct(): TransPerm
  {
    return TransPerm::where('code', 'sendProduct')->firstOrFail();
  }
}
