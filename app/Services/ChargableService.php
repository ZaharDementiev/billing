<?php

namespace App\Services;

interface ChargableService
{
    public function charge($user);

    public function setup(string $email);

    public function notify(array $request);
}
