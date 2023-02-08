<?php

namespace App\Http\Controllers;

use App\Models\Infopage;
use App\Models\Uri;
use Illuminate\Http\Request;

class FallbackController extends Controller
{
    public static $map = [];

    public function slugs(Request $request) {
        $pureUrl = config('app.dir')? config('app.url').'/'.config('app.dir') : config('app.url');
        $foundModel = false;
        $site_id = app()->make('Site')->id;
        $language = app()->getLocale();
        $fallbackLocale = config('app.fallback_locale');
        foreach(request()->segments() as $index => $segment) {
            if($uri = Uri::with(['uriable'])
                ->where('slug', $segment)
                ->where('site_id', $site_id)
                ->whereIn('language', [$language, $fallbackLocale])
                ->orderByRaw("language = '{$fallbackLocale}' DESC, language = '{$language}' DESC")
                ->first()) {

                // @HOOK_INSTANCE_CHECK

                if(!$foundModel) {
                    $pureUrl .= '/'.$uri->uriable->defaultUri();
                    $foundModel = true;
                    continue;
                }
                $pureUrl .= '/'.$uri->uriable->id;
                continue;
            }
            $pureUrl .= '/'.$segment;
        }
//        dd($pureUrl);


        $request2 = Request::create($pureUrl,
            $request->method(),
            $request->input(),
            $request->cookies->all(),
            $request->allFiles(),
            $request->server(),
            $request->getContent()
        );
        $route = app('router')->getRoutes()->match($request2);
        if(!$route || in_array($route->getName(), ['i18n_fallback', 'fallback'])) {
            abort(404);
        }

//        opcache_reset();
        //FOR WHERE_I_AM
        \App\Providers\MarinarBeforeServiceProvider::$where_i_am = null;
        \App\Providers\MarinarBeforeServiceProvider::$main_segments = null;
        \App\Providers\MarinarBeforeServiceProvider::$route_segments = null;
        \App\Providers\MarinarBeforeServiceProvider::$route_use_locale = false;

        return app()->handle($request2);
    }
}
