<?php

namespace App\Policies;

use App\Models\ProducerProductTrans;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProducerProductTransPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAnyProducer(User $user): bool
    {
        return $user->isProducer();
    }

    public function viewAnyDepart(User $user): bool
    {
        return $user->isDepartUser();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function viewProducer(User $user): bool
    {
        return $user->isProducer();
    }

    public function viewDepart(User $user): bool
    {
        return $user->isDepartUser();
    }

    public function sendProductToDepart(User $user): bool
    {
        return $user->isProducer();
    }
    public function sendProductToProducer(User $user): bool
    {
        return $user->isDepartUser();
    }

    /**
     * Determine whether the user can update the model.
     */


    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ProducerProductTrans $producerProductTrans): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ProducerProductTrans $producerProductTrans): bool
    {
        return false;
    }
}
