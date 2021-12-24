<?php

namespace App\Http\Controllers;

use App\Jobs\SendEmailJob;
use App\Models\Email;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function sendToUser(Request $request)
    {
        $email = Email::where('week', $request->input('week'))->first();

        SendEmailJob::dispatch($request->input('email'), $email)->onQueue('mailing');
    }
}
