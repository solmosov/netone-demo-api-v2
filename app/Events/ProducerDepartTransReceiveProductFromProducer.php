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
    public function __construct(public User $senderDepartUser, public User $receiverProducer, public ProducerProductTrans $producerProductTrans)
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
        return [
            new PrivateChannel("user.{$this->receiverProducer->id}")
        ];
    }

    public function broadcastAs(): string
    {
        return 'department-received-product-from-producer';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'data' => [
                'data' => [
                    'id' => $this->producerProductTrans->id,
                ],
                'sender' => $this->senderDepartUser,
                'type' => 'department-received-product-from-producer'
            ],
            'message' => "{$this->senderDepartUser->name} - foydalanuvchi yuborilgan yukni qabul qildi",
            'created_at' => now()->toISOString()
        ];
    }
}
