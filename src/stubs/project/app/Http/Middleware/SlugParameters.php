<?php

namespace App\Http\Middleware;

use Closure;

class SlugParameters
{
    /**
     * Handle an incoming request.
     * @example \App\Http\Middleware\SlugParameters::class.":".Infopage::class.',info|chInfopage,info2|chInfopage2'
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $class, ...$parameters) {
        $slugParameters  = [];
        foreach($parameters as $slugParameter) {
            $slugParameter = explode('|', $slugParameter);
            $slugParameters[$slugParameter[0]] = $slugParameter[1];
        }
        foreach($slugParameters as $slugParameter => $slugReplace) {
            if(!($slug = request()->route($slugParameter)) || !is_string($slug)) continue;
            if((is_numeric($slug) && ($model = ($class)::find((int)$slug))) ||
                ($model = ($class)::findBySlug($slug))
            ) {
                if($uriIns = $model->getUriIns()) {
                    if(!is_null($uriIns->pointable_type) || $uriIns->is_link) {
                        return redirect($model->getUri());
                    }
                }
                $request->route()->forgetParameter($slugParameter);
                $request->route()->setParameter($slugReplace, $model);
                continue;
            }
            abort(404);
        }
        return $next($request);
    }
}
