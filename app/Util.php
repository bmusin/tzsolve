<?php

declare(strict_types = 1);

namespace App;

use Illuminate\View\View;

use FilesystemIterator;

class Util
{
    public static function notify_and_redirect_view(string $message) : View
    {
        return view('notify-and-redirect', ['message' => $message]);
    }

    public static function delete_dir_recursively(
        string $dir,
        bool $remove_itself = true
    ) : void {
        foreach ((new FilesystemIterator($dir)) as $f) {
            $pathname = $f->getPathname();
            if (is_dir($pathname)) {
                Util::delete_dir_recursively($pathname);
            } else {
                unlink($pathname);
            }
        }
        if ($remove_itself) {
            rmdir($dir);
        }
    }

    public static function RELOGIN_AS_MANAGER__REDIRECT() : View
    {
        return Util::notify_and_redirect_view(
            config('constants.msg_relogin_as_non_manager')
        );
    }

    public static function RELOGIN_AS_CLIENT__REDIRECT() : View
    {
        return Util::notify_and_redirect_view(
            config('constants.msg_relogin_as_non_manager')
        );
    }

    public static function TOO_OFTEN__REDIRECT() : View
    {
        return Util::notify_and_redirect_view(
            config('constants.msg_client_submits_too_often')
        );
    }
}
