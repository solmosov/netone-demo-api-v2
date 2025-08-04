<?php

namespace App\Events;

use App\Models\ProducerProductTrans;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProducerDepartTransReceiveProductFromProducer implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;



    /**
     * Create a new event instance.
     */
    public function __construct(public User $senderProducerUser, public array $departmentsUsersId, public ProducerProductTrans $producerProductTrans)
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [];

        foreach ($this->departmentsUsersId  as $userId) {
            $channels[] = new PrivateChannel("user.{$userId}");
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'received-product-from-producer';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'data' => [
                'data' => [
                    'id' => $this->producerProductTrans->id,
                ],
                'sender' => $this->senderProducerUser,
                'type' => 'received-product-from-producer'
            ],
            'message' => "{$this->senderProducerUser->name} - foydalanuvchi yuborilgan yukni qabul qildi",
            'created_at' => now()->toISOString()
        ];
    }
}
