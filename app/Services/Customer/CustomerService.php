<?php

namespace App\Services\Customer;

use App\Events\Customer\CustomerCreatedEvent;
use App\Events\Customer\CustomerDeletedEvent;
use App\Events\Customer\CustomerTelegramAddedEvent;
use App\Events\Customer\CustomerTelegramRemoveEvent;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;

class CustomerService
{

  public function findAll(array $queryParams): Collection
  {
    // Query Params
    $personCategoryId = $queryParams['person_category_id'] ?? null;
    $startBalance = $queryParams['start_balance'] ?? null;
    $endBalance = $queryParams['end_balance'] ?? null;

    $query =  Customer::with('personCategory');

    if ($personCategoryId) {
      $query->where('person_category_id', $personCategoryId);
    }

    if ($startBalance && $endBalance) {
      $query->whereBetween('balance', [(float)$startBalance, (float)$endBalance]);
    }

    $data = $query->get();

    return $data;
  }

  public function create(array $data): Customer
  {
    // New Customer
    $newCustomer = Customer::create($data);

    // Event
    CustomerCreatedEvent::dispatch($newCustomer);

    // Finish
    return $newCustomer;
  }

  public function findOne(int $id): Customer
  {
    $customer = Customer::with('personCategory')->findOrFail($id);

    return $customer;
  }

  public function update(int $id, array $data): Customer
  {
    // Data
    $phone = $data['phone'] ?? null;
    $telegram = $data['telegram'] ?? null;

    // Customer
    $customer = Customer::with('personCategory')->findOrFail($id);


    // Check if Phone and Telegram already exist
    if ($phone) {
      $phoneExists = Customer::where('phone', $phone)->where('id', '<>', $customer->id)->exists();
      if ($phoneExists) abort(422, "Bu telefon raqam allaqachon mavjud!");
    }
    if ($telegram) {
      $telegramExists = Customer::where('telegram', $telegram)->where('id', '<>', $customer->id)->exists();
      if ($telegramExists) abort(422, "Bu telegram allaqachon mavjud!");
    }

    // Event
    if (!$customer->telegram && $telegram) {
      $customer->telegram = $telegram;
      CustomerTelegramAddedEvent::dispatch($customer);
    } else if ($customer->telegram && !$telegram) {
      CustomerTelegramRemoveEvent::dispatch($customer);
    }

    // Update
    $customer->update($data);

    return $customer;
  }

  public function delete(int $id): Customer
  {
    $customer = $this->findOne($id);


    // Delete Customer
    $customer->delete();

    // Event
    CustomerDeletedEvent::dispatch($customer);

    return $customer;
  }
}
