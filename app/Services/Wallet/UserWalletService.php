<?php

namespace App\Services\Wallet;

use App\Models\User;
use App\Models\UserWallet;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;

class UserWalletService
{
  public function findAll()
  {
    // Auth User
    $authUser = Auth::user();

    $query = UserWallet::with(['user', 'wallet.currency']);

    if (!$authUser->isAdmin()) {
      $query->where('user_id', $authUser->id);
    }

    $data = $query->get();

    return [
      'data' => $data,
      'total_count' => $data->count(),
    ];
  }
  public function findOne(int $id)
  {
    // Auth User
    $authUser = Auth::user();

    $query = UserWallet::with('user', 'wallet.currency');

    if (!$authUser->isAdmin()) {
      $query->where('user_id', $authUser);
    }

    return $query->findOrFail($id);
  }
  public function create(array $data)
  {
    // Req body
    $userId = $data['user_id'];
    $walletId = $data['wallet_id'];

    $user = User::findOrFail($userId);
    $wallet = Wallet::findOrFail($walletId);

    $user->wallets()->attach([$walletId], [
      'created_at' => now(),
      'updated_at' => now()
    ]);

    return [
      'message' => "$user->name uchun yangi `{$wallet->name}` hisob ochildi",
      'data' => $user
    ];
  }

  public function increment($userWalletId, $amount)
  {
    $authUser = Auth::user();

    $query = UserWallet::with('wallet');

    if (!$authUser->isAdmin) {
      $query->where('user_id', $authUser->id);
    }

    $userWallet = $query->findOrFail($userWalletId);

    $userWallet->wallet->increment('amount', $amount);
  }
  public function decrement($userWalletId, $amount)
  {
    $authUser = Auth::user();

    $query = UserWallet::with('wallet');

    if (!$authUser->isAdmin) {
      $query->where('user_id', $authUser->id);
    }

    $userWallet = $query->findOrFail($userWalletId);

    $userWallet->wallet->decrement('amount', $amount);
  }
}
