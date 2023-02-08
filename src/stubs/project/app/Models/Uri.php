<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Uri extends Model {

    protected $guarded = ['id'];

    protected $with = ['pointable'];

    // @HOOK_TRAITS

    /**
     * @var array
     * Uriable classes
     * class => translation key
     */
    public static $uriableClasses = [

        // @HOOK_URIABLE_CLASSES
    ];

    public function uriable() {
        return $this->morphTo();
    }

    public function pointable() {
        return $this->morphTo();
    }

    public static function findBySlug($slug, $uriWheres = []) {
        $site_id = $uriWheres['site_id']?? app()->make('Site')->id;
        $language = $uriWheres['language']?? app()->getLocale();
        $fallbackLocale = config('app.fallback_locale');
        unset($uriWheres['language'], $uriWheres['site_id']);
        if(config('marinar_uriable.use_fallback')) {
            return static::where('site_id', $site_id)
                ->with(['uriable'])
                ->whereIn('language', [$language, $fallbackLocale])
                ->orderByRaw("language = '{$fallbackLocale}' DESC, language = '{$language}' DESC")
                ->where(array_merge([
                    'slug' => $slug,
                ], $uriWheres))
                ->first();
        }
        return static::where('site_id', $site_id)
            ->with(['uriable'])
            ->whereIn('language', [$language])
            ->where(array_merge([
                'slug' => $slug,
            ], $uriWheres))
            ->first();
    }

    public static function modelBySlug($slug, $uriWheres = []) {
        $uri = static::findBySlug($slug, $uriWheres);
        return $uri?->uriable;
    }

    public static function prepareSlug($slug, $language = null, $dictionary = []) {
        return Str::slug($slug, '-', $language?? app()->getLocale(), array_merge([
            '@' => 'at', '&' => 'and', '!' => 'not'
        ], $dictionary));
    }

    public static function validationRules($inputBag, $uriable = null, $excludeTypes = [], $addTypes = []) {
        $typeOptions = [];
        if(!in_array('default', $excludeTypes)) $typeOptions[] = 'default';
        if(!in_array('link', $excludeTypes)) $typeOptions[] = 'link';
        foreach(static::$uriableClasses as $class => $translationKey) {
            if(in_array($class, $excludeTypes)) continue;
            $typeOptions[] = $class;
        }
        $typeOptions = implode(',', array_merge($typeOptions, $addTypes));
        return [
            'uri.slug' => ['nullable', 'max:255', function($attribute, $value, $fail) use ($inputBag, $uriable) {
                $pointableType = request("{$inputBag}.uri.pointable_type", "default");
                if($pointableType !== 'default' && $value === '') {
                    return $fail( trans('admin/uriable/uriable.validation.uri.slug.required') );
                }
                if($pointableType === 'default') {
                    $value = static::prepareSlug($value);
                    if(($foundUriable = static::modelBySlug($value)) && !$foundUriable->is($uriable) ) {
                        return $fail( trans('admin/uriable/uriable.validation.uri.slug.already_used') );
                    }
                    return;
                }
                if($pointableType === 'link') {
                    if(filter_var($value, FILTER_VALIDATE_URL) === false) {
                        return $fail( trans('admin/uriable/uriable.validation.uri.slug.not_a_link') );
                    }
                    return;
                }
                if(!($foundUriable = ($pointableType)::find((int)$value))) {
                    return $fail( trans('admin/uriable/uriable.validation.uri.slug.not_found') );
                }
                if($uriable && ($uriable->is($foundUriable) || $foundUriable->pointUriToLastIns()->is($uriable))) {
                    return $fail(trans('admin/uriable/uriable.validation.uri.slug.recursion'));
                }
            }],
            'uri.pointable_type' => ['required', "in:{$typeOptions}", 'max:255']
        ];
    }

    public static function validated(&$validatedData) {
        if($validatedData['uri']['pointable_type'] == 'default') {
            $validatedData['uri']['pointable_type'] = $validatedData['uri']['pointable_id'] = null;
            $validatedData['uri']['is_link'] = false;
            return;
        }
        if($validatedData['uri']['pointable_type'] == 'link') {
            $validatedData['uri']['pointable_type'] = $validatedData['uri']['pointable_id'] = null;
            $validatedData['uri']['is_link'] = true;
            return;
        }
        $validatedData['uri']['pointable_id'] = (int)$validatedData['uri']['slug'];
        $validatedData['uri']['is_link'] = false;
        $validatedData['uri']['slug'] = null;
    }
}
