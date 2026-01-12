<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;

class UserActivityEvent
{
    use Dispatchable, SerializesModels;

    public Model $actor;
    public string $action;
    public ?Model $subject;
    public ?string $description;
    public array $changes;

    public function __construct(
        Model $actor,
        string $action,
        ?Model $subject = null,
        ?string $description = null,
        array $changes = []
    ) {
        $this->actor = $actor;
        $this->action = $action;
        $this->subject = $subject;
        $this->description = $description;
        $this->changes = $changes;
    }
}
