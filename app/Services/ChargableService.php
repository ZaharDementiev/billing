<?php

namespace App\Services;

interface ChargableService
{
    public function charge($user);

    public function setup(bool $authCheck, $user);

    public function notify(array $request);
}
