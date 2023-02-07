<?php
namespace App\Traits;

use App\Models\Uri;

trait Uriable {

    public $cached_uris = null;

    public function uriableCache() {
        if(!is_null($this->cached_uris)) return;
        $this->cached_uris = [];
        foreach($this->uris()->with('pointable')->get() as $uri) {
            $this->cached_uris[$uri->site_id][$uri->language] = $uri;
        }
    }

    public function clearUriableCache() {
        $this->cached_uris = null;
    }

    public static function bootUriable() {
        static::deleting( static::class.'@onDeleting_uris' );
    }

    public function defaultUri($language = null, $site_id = null, $prepareLevel = null) { //just for default
        return strtolower(basename(static::class) ).'/'.$this->id;
    }

    public function uris() {
        return $this->morphMany(Uri::class, 'uriable');
    }

    public function pointings() {
        return $this->morphMany(Uri::class, 'pointable');
    }

    public function onDeleting_uris($model) {
        $model->uris()->delete();
    }

    public function getUriIns($language = null, $site_id = null, $fallback = null) {
        $fallback = $fallback?? config('marinar_uriable.use_fallback');
        $this->uriableCache();
        $return = $this->cached_uris[$site_id ?? app()->make('Site')->id][$language ?? app()->getLocale()]?? null;
        if(!$fallback || !is_null($return) || $language == config('app.fallback_locale')) return $return;
        return $this->getUriIns(config('app.fallback_locale'), $site_id, false);
    }

    public function pointUriToIns($language = null, $site_id = null) {
        if( !($uri =  $this->getUriIns($language, $site_id)) ) return;
        if (!($pointTo = $uri->pointable)) return;
        return $pointTo;
    }

    public function pointUriToLastIns($language = null, $site_id = null) {
        if($pointTo = $this->pointUriToIns($language, $site_id)) {
            return $pointTo->pointUriToLastIns($language, $site_id);
        }
        return $this;
    }

    public function getUriSlug($language = null, $site_id = null, $prepareLevel = null) {
        if(!($uriIns = $this->getUriIns($language, $site_id))) {
            return $this->defaultUri($language, $site_id, $prepareLevel);
        }
        if($uriIns->is_link) {
            return $uriIns->slug;
        }
        if(($pointTo = $uriIns->pointable) && method_exists($pointTo, 'getUri')) {
            return $pointTo->getUriSlug($language, $site_id);
        }
        return (method_exists($this, 'prepareSlug'))?
            $this->prepareSlug($uriIns->slug, $prepareLevel) :
            $uriIns->slug;
    }

    public function getUri($language = null, $site_id = null) {
        $language = $language?? app()->getLocale();
        $slug = $this->getUriSlug($language, $site_id);
        if(filter_var($slug, FILTER_VALIDATE_URL) !== false) return $slug;
        return route((config('app.fallback_locale') == $language? 'fallback' : 'i18n_fallback'), $slug);
    }

    public function setUri($slug, $type, $attributes = []) {
        $this->clearUriableCache();
        $language = $attributes['language']?? app()->getLocale();
        $site_id = $attributes['site_id']?? app()->make('Site')->id;
        if($type == 'default') {
            if($slug === '' || is_null($slug)) {
                $this->uris()->where([
                    'language' => $language,
                    'site_id' => $site_id,
                ])->delete();
                return;
            }
            $attributes['pointable_type'] = $attributes['pointable_id'] = null;
            $attributes['is_link'] = false;
            $attributes['slug'] = Uri::prepareSlug($slug);
        } elseif($type == 'link') {
            $attributes['pointable_type'] = $attributes['pointable_id'] = null;
            $attributes['is_link'] = true;
            $attributes['slug'] = $slug;
        } else {
            $attributes['pointable_type'] = $type;
            $attributes['pointable_id'] = (int)$slug;
            $attributes['is_link'] = false;
            $attributes['slug'] = null;
        }

        $this->uris()->updateOrCreate([
            'language' => $language,
            'site_id' => $site_id,
        ], $attributes);
    }

    public static function findBySlug($slug, $uriWheres = []) {
        return Uri::modelBySlug($slug, array_merge([
            'uriable_type' => static::class,
        ], $uriWheres));
    }




}
