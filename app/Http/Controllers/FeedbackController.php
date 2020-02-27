<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

use Carbon\Carbon;
use Ulid\Ulid;

use App\Client;
use App\Http\Controllers\Auth\LoginController as LC;
use App\Jobs\RequestSentJob;
use App\Request as ClientRequest;
use App\Util;

class FeedbackController extends Controller
{
    public function create()
    {
        if (LC::logged_in_as_manager()) {
            return Util::RELOGIN_AS_CLIENT__REDIRECT();
        } elseif (LC::logged_in_as_client()) {
            return $this->feedbackView();
        } else {
            return LC::REDIRECT_BACK_TO_LOGIN();
        }
    }

    public function store(Request $request)
    {
        if (LC::logged_in_as_manager()) {
            return Util::RELOGIN_AS_CLIENT__REDIRECT();
        } elseif (LC::logged_in_as_client()) {
            if ($this->is_too_frequent()) {
                return Util::TOO_OFTEN__REDIRECT();
            } else {
                return $this->storeFeedback($request);
            }
        } else {
            return LC::REDIRECT_BACK_TO_LOGIN();
        }
    }

    private function storeFeedback(Request $request) : View
    {
        if (!$request->subject || !$request->description) {
            return $this->feedbackView();
        }

        $file = $request->file('file');
        $filename = null;
        if ($file) {
            $filename = $file->getClientOriginalName();
        }

        // new request (feedback) to store in DB
        $nr = new ClientRequest;
        $nr->subject         = $request->subject;
        $nr->description     = $request->description;
        $nr->user_id         = Client::authId();
        $nr->attachment_name = $filename;
        $nr->save();

        $nrId            = $nr->id;
        $attachment_path = null;
        $stored_as       = null;
        if ($file) {
            $stored_as = $nrId.'_attachment.'.$file->extension();
            $file->storeAs('requests', $stored_as);
            $parent_dir = Ulid::generate(true);
            $sp = storage_path("app/public/download/$parent_dir/");
            mkdir($sp);
            $attachment_path = $sp.$filename;
            symlink(storage_path("app/requests/$stored_as"), $attachment_path);
        }
        RequestSentJob::dispatch($nrId, $attachment_path);
        return Util::notify_and_redirect_view('You request was submitted.');
    }

    private function is_too_frequent() : bool
    {
        if (config('constants.one_request_per_day')) {
            $now    = new Carbon;
            $recent = new Carbon(ClientRequest::where(
                'user_id',
                Client::authId(),
            ) ->max('created_at'));
            if ($now->day   == $recent->day &&
                $now->month == $recent->month &&
                $now->year  == $recent->year) {
                return true;
            }
        } else {
            return false;
        }
    }

    private function feedbackView() : View
    {
        return view('feedback');
    }
}
