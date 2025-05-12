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
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProducerProductTrans $producerProductTrans): bool
    {
        return true;
    }

    public function sendProductToDepart(User $user): bool
    {
        return $user->isProducer();
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
