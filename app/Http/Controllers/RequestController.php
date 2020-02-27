<?php

declare(strict_types = 1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as LAP;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

use App\Client;
use App\Http\Controllers\Auth\LoginController as LC;
use App\Request as ClientRequest;
use App\User;
use App\Util;

use Ulid\Ulid;

class RequestController extends Controller
{
    public function index(Request $request)
    {
        if (LC::logged_in_as_client()) {
            return Util::RELOGIN_AS_MANAGER__REDIRECT();
        } elseif (LC::logged_in_as_manager()) {
            return $this->getRequestsView($request);
        } else {
            return LC::REDIRECT_BACK_TO_LOGIN();
        }
    }

    private function getRequestsView(Request $request) : View
    {
        $crs_to_display = [];
        foreach (ClientRequest::all() as $cr) {
            $attachment_path  = '';
            $parent_dir       = '';
            if ($cr->attachment_name) {
                if (!$this->found_attachment(
                    $attachment_path,
                    $parent_dir,
                    $cr
                )) {
                    $cr->attachment_name = null;
                    $cr->save();
                }
            }
            $client = $cr->client;
            $item = [
                'id'          => $cr->id,
                'subject'     => $cr->subject,
                'description' => $cr->description,
                'name'        => $client->name,
                'email'       => $client->email,
                'time'        => $cr->created_at,
            ];
            if ($attachment_path) {
                $item['att_name'] = $cr->attachment_name;
                $item['att_link'] = Storage::url(
                    "download/$parent_dir/$cr->attachment_name"
                );
            }
            $crs_to_display[] = $item;
        }
        return $this->page($request, $crs_to_display);
    }

    public function removeAll() : View
    {
        ClientRequest::truncate();
        Util::delete_dir_recursively(
            public_path('storage/download/'),
            false
        );
        Util::delete_dir_recursively(storage_path('app/requests/'), false);
        return Util::notify_and_redirect_view(
            'All requests have been removed.'
        );
    }

    private function found_attachment(
        string &$attachment_path,
        string &$parent_dir,
        $cr
    ) : bool {
        $att_files = scandir(storage_path('app/requests'));
        foreach ($att_files as $file) {
            $pattern = '/^'.$cr->id.'_attachment[.].+$/';
            if (preg_match($pattern, basename($file))) {
                $parent_dir = Ulid::generate(true);
                $sp = storage_path("app/public/download/$parent_dir/");
                mkdir($sp);
                $attachment_path = $sp.$cr->attachment_name;
                symlink(
                    storage_path("app/requests/$file"),
                    $attachment_path
                );
                return true;
            }
        }
        return false;
    }

    private function page(Request $request, array $list) : View
    {
        $curPage = LAP::resolveCurrentPage();
        $rpp     = config('constants.records_per_page');

        $records = new Collection($list);
        $slice   = $records->slice(
            ($curPage - 1) * $rpp,
            $rpp
        )->all();

        $pg = new LAP($slice, count($records), $rpp);
        $pg->setPath($request->url());
        return view('requests', [
            'results' => $pg,
            'email' => User::getManagerEmail()
        ]);
    }
}
