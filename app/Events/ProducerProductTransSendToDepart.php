<?php

namespace App\Events;

use App\Models\ProducerProductTrans;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProducerProductTransSendToDepart implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public User $senderProducerUser,
        public array $receiveDepartmentUsersIds,
        public ProducerProductTrans $producerProductTrans
    ) {
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

        foreach ($this->receiveDepartmentUsersIds as $userId) {
            $channels[] = new PrivateChannel("user.{$userId}");
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'producer-send-product-to-department';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'data' => [
                'data' => [
                    "id" => $this->producerProductTrans->id,
                ],
                'sender' => $this->senderProducerUser,
                'type' => 'producer-send-product-to-department'
            ],
            'message' => "{$this->senderProducerUser->name} producerdan sizga yuk yubordi",
            'created_at' => now()->toISOString()
        ];
    }
}
