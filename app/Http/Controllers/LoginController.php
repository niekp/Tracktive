<?php

namespace App\Http\Controllers;

use App\Actions\SendNtfyAction;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

final class LoginController
{
    private SendNtfyAction $send_ntfy_action;

    public function __construct(
        SendNtfyAction $send_ntfy_action
    ) {
        $this->send_ntfy_action = $send_ntfy_action;
    }

    public function __invoke(Request $request)
    {
        if (!$request->isMethod('POST')) {
            return view('login.login');
        }

        if (in_array($request->ip(), config('auth.trusted_ips'))) {
            return $this->login($request);
        }

        ($this->send_ntfy_action)(
            'Verzoek tot login',
            null,
            [
                'view, Login, ' . URL::temporarySignedRoute('login.set-token', now()->addMinutes(30))
            ]
        );

        return view('login.requested');
    }

    public function login(Request $request)
    {
        $request->session()->put('login', new User());
        return redirect()->route('activities.index');
    }
}
