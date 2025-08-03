<?php

namespace App\Events;

use App\Http\Resources\DepartTransProcessResource;
use App\Models\DepartTransProcess;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Str;

class DepartTransProcessCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public DepartTransProcess $departTransProcess;
    public User $senderDepartmentUser;
    /** @var int[] */
    public array $receiverDepartmentUsersIds;

    /**
     * Create a new event instance.
     */
    public function __construct(DepartTransProcess $departTransProcess, User $senderDepartmentUser, array $receiverDepartmentUsersIds)
    {
        $this->departTransProcess = $departTransProcess;
        $this->senderDepartmentUser = $senderDepartmentUser;
        $this->receiverDepartmentUsersIds = $receiverDepartmentUsersIds;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [];

        foreach ($this->receiverDepartmentUsersIds as $receiverUserId) {
            $channels[] = new PrivateChannel('user.' . $receiverUserId);
        }

        return $channels;
    }

    public function broadcastAs()
    {
        return 'department-transfer-process-created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'data' => [
                'department_transfer_process' => $this->departTransProcess,
                'sender' => $this->senderDepartmentUser,
                'type' => 'department-transfer-process-created'
            ],
            'message' => "Sizga boshqa bo'limdan yuk yuborildi",
            'created_at' => now()->toISOString()
        ];
    }
}
