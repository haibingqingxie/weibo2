<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

use App\Models\Status;

class StatusPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    // 删除策略，只能删除自己的微博
    public function destroy(User $currentUser, Status $status)
    {
        return $currentUser->id === $status->user_id;
    }

}
