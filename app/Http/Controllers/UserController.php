<?php
declare(strict_types = 1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;

use FilesystemIterator;
use Validator;

use Carbon\Carbon;
use Ulid\Ulid;

use App\Jobs\RequestSentJob;

define('CONFIG_JSON', 'config.json');
define('MSG_RELOGIN_AS_NON_MANAGER', 'Re-login using non-manager account.');

class UserController extends Controller
{
    public function showFeedbackForm()
    {
        if ($this->restrict_frequency_of_users_requests(Carbon::now())) {
            return $this->notify_and_redirect_view(
                "You've already submitted request today."
            );
        }

        return $this->logged_in_as_non_manager()
            ? view('feedback-form')
            : $this->notify_and_redirect_view(MSG_RELOGIN_AS_NON_MANAGER);
    }

    public function processFeedbackForm(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        if (!$this->logged_in_as_non_manager()) {
            return $this->notify_and_redirect_view(MSG_RELOGIN_AS_NON_MANAGER);
        }
        if (!$request->subject || !$request->description) {
            return redirect()->route('feedback-form');
        }

        $nowDt = Carbon::now();
        if ($this->restrict_frequency_of_users_requests($nowDt)) {
            return $this->notify_and_redirect_view(
                "You've already submitted request today."
            );
        }

        $file = $request->file('file');
        $filename = null;
        if ($file) {
            $filename = $file->getClientOriginalName();
        }
        $newRequestId = DB::table('requests')->insertGetId([
          'subject'         => $request->subject,
          'description'     => $request->description,
          'user_id'         => Auth::id(),
          'attachment_name' => $filename,
          'created_at'      => $nowDt,
          'updated_at'      => $nowDt,
        ]);
        $recentRequest = DB::table('requests')
            ->where('id', $newRequestId)->get();
        $attachment_path = null;
        $stored_as = null;
        if ($file) {
            $stored_as = $newRequestId.'_attachment.'.$file->extension();
            $file->storeAs('requests', $stored_as);
            $parent_dir = Ulid::generate(true);
            $sp = storage_path("app/public/download/$parent_dir/");
            mkdir($sp);
            $attachment_path = $sp.$filename;
            symlink(storage_path("app/requests/$stored_as"), $attachment_path);
        }
        RequestSentJob::dispatch($newRequestId, $attachment_path);
        return $this->notify_and_redirect_view('You request was submitted.');
    }

    public function requests(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        if (!$this->logged_in_as_manager()) {
            return $this->notify_and_redirect_view(
                'You are not allowed to view this content.'
            );
        }

        $requests = [];
        $requestsRaw = DB::table('requests')->get();
        foreach ($requestsRaw as $requestRaw) {
            $attachments_dir = storage_path('app/requests');
            $files = scandir($attachments_dir);

            $attachment_path = null;
            $parent_dir = null;
            if ($requestRaw->attachment_name) {
                foreach ($files as $file) {
                    $pattern = '/^'.$requestRaw->id.'_attachment[.].+$/';
                    if (preg_match($pattern, basename($file))) {
                        $parent_dir = Ulid::generate(true);
                        $sp = storage_path("app/public/download/$parent_dir/");
                        mkdir($sp);
                        $attachment_path = $sp.$requestRaw->attachment_name;
                        symlink(
                            storage_path("app/requests/$file"),
                            $attachment_path
                        );
                        break;
                    }
                }
            }
            $associated_user = DB::table('users')->find($requestRaw->user_id);
            $item = [
                'id'          => $requestRaw->id,
                'subject'     => $requestRaw->subject,
                'description' => $requestRaw->description,
                'name'        => $associated_user->name,
                'email'       => $associated_user->email,
                'time'        => $requestRaw->created_at,
            ];
            if ($attachment_path) {
                $item['filename'] = $requestRaw->attachment_name;
                $item['download_link'] = Storage::url(
                    "download/$parent_dir/$requestRaw->attachment_name"
                );
            }
            $requests[] = $item;
        }

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $all_records = new Collection($requests);
        $rpp = env('RECORDS_PER_PAGE');
        $slicedout_for_pagedisplay = $all_records->slice(
            ($currentPage - 1) * $rpp,
            $rpp
        )->all();
        $pg = new LengthAwarePaginator(
            $slicedout_for_pagedisplay,
            count($all_records),
            $rpp
        );
        $pg->setPath($request->url());
        return view('requests', [
            'results' => $pg,
            'email' => $this->getManagerEmail()
        ]);
    }

    public function removeAllRequests()
    {
        DB::table('requests')->truncate();
        $this->delete_dir_recursively(
            public_path('storage/download/'),
            false
        );
        $this->delete_dir_recursively(storage_path('app/requests/'), false);
        return $this->notify_and_redirect_view(
            'All requests have been removed.'
        );
    }

    public function setManagerEmail(Request $req)
    {
        if ($req->email && filter_var($req->email, FILTER_VALIDATE_EMAIL)) {
            $config_path = resource_path(CONFIG_JSON);
            $json = json_decode(file_get_contents($config_path, false));
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->notify_and_redirect_view(
                    'Configuration file is corrupted.'
                );
            }
            $json->email = $req->email;
            $json_str = json_encode($json);
            $h = fopen($config_path, 'w');
            fwrite($h, $json_str);
            fclose($h);
            return $this->notify_and_redirect_view(
                "Manager's e-mail was updated."
            );
        } else {
            return $this->notify_and_redirect_view('Invalid e-mail value.');
        }
    }

    public static function getManagerEmail() : string
    {
        $config_path = resource_path(CONFIG_JSON);
        $json = json_decode(file_get_contents($config_path, false));
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->notify_and_redirect_view(
                'Configuration file is corrupted.'
            );
        }
        return $json->email;
    }

    private function delete_dir_recursively($dir, $remove_itself = true)
    {
        foreach ((new FilesystemIterator($dir)) as $f) {
            $pathname = $f->getPathname();
            if (is_dir($pathname)) {
                $this->delete_dir_recursively($pathname);
            } else {
                unlink($pathname);
            }
        }
        if ($remove_itself) {
            rmdir($dir);
        }
    }

    private function notify_and_redirect_view(string $message)
    {
        return view('notify-and-redirect', ['message' => $message]);
    }

    private function restrict_frequency_of_users_requests($nowDt)
    {
        if (env('ONE_REQUEST_IN_DAY')) {
            $recent = DB::table('requests')
                ->where('user_id', Auth::id())->max('created_at');
            if ($recent) {
                $recentDt = new Carbon($recent);
                if ($nowDt->day === $recentDt->day
                    && $nowDt->month === $recentDt->month
                    && $nowDt->year === $recentDt->year) {
                    return true;
                }
            }
        }
        return false;
    }

    private function logged_in_as_non_manager() : bool
    {
        return Auth::check() && Auth::user()->name !== env('MANAGER_USERNAME');
    }

    private function logged_in_as_manager() : bool
    {
        return Auth::check() && Auth::user()->name === env('MANAGER_USERNAME');
    }
}
