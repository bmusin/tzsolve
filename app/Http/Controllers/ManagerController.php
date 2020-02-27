<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

use App\User;
use App\Util;

class ManagerController extends Controller
{
    public function updateManagerEmail(Request $request) : View
    {
        if ($request->email && filter_var(
            $request->email,
            FILTER_VALIDATE_EMAIL
        )) {
            return Util::notify_and_redirect_view(
                User::updateManagerEmail($request->email)
            );
        } else {
            return Util::notify_and_redirect_view('Sent invalid e-mail value.');
        }
    }
}
