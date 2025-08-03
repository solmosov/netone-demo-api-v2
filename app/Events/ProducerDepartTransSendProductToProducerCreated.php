<?php

namespace App\Events;

use App\Models\Depart;
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

class ProducerDepartTransSendProductToProducerCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    /**
     * Create a new event instance.
     */
    public function __construct(public Depart $depart, public User $producer, public ProducerProductTrans $producerProductTrans) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user' . $this->producer->id),
        ];
    }

    public function broadcastAs()
    {
        return 'send-product-to-producer-created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'data' => [
                'send_product_to_producer' => $this->producerProductTrans,
                'sender' => $this->depart,
                'type' => 'send-product-to-producer-created'
            ],
            'message' => "{$this->depart->name} - bo'limdan sizga yuk yuborildi",
            'created_at' => now()->toISOString()
        ];
    }
}
