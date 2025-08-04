<?php

namespace App\Events;

use App\Models\ProducerProductTrans;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProducerProductTransReceiveFromDepart implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public User $receiveProducerUser,
        public array $receiverDepartmentUsersIds,
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

        foreach ($this->receiverDepartmentUsersIds as $userId) {
            $channels[] = new PrivateChannel("user.{$userId}");
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return "producer-receive-product-from-department";
    }

    public function broadcastWith(): array
    {
        return [
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'data' => [
                'data' => [
                    "id" => $this->producerProductTrans->id,
                ],
                'receive' => $this->receiveProducerUser,
                'type' => 'producer-receive-product-from-department'
            ],
            'message' => "{$this->receiveProducerUser->name} producerdan sizga yuk yubordi",
            'created_at' => now()->toISOString()
        ];
    }
}
