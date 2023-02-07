<?php

namespace Sync\Helpers;

use App\Models\User;

class UpdateTokensHelper
{
    public function updateTokens()
    {
        $users = User::all();
        var_dump($users);
    }
}