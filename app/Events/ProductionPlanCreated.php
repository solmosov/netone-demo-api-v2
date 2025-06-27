<?php

namespace App\Events;

use App\Models\ProductionPlan;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ProductionPlanCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $productionPlan;
    public $producer;
    public $departmentUsers;

    /**
     * Create a new event instance.
     */
    public function __construct(ProductionPlan $productionPlan, User $producer, $departmentUsers)
    {
        $this->productionPlan = $productionPlan;
        $this->producer = $producer;
        $this->departmentUsers = $departmentUsers;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [];

        foreach ($this->departmentUsers as $departmentUser) {
            $channels[] = new PrivateChannel('user.' . $departmentUser->id);
        }

        return $channels;
    }

    public function broadcastAs()
    {
        return 'production-plan-created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => Str::uuid()->toString(),
            'production_pan' => $this->productionPlan,
            'producer' => $this->producer,
            'message' => "Sizga '{$this->producer->name}'dan yangi '{$this->productionPlan->title}' topshiriq keldi",
            'type' => 'production_plan_created',
            'created_at' => now()->toISOString(),
        ];
    }
}
