<fieldset>
    @php
        $translations = trans('admin/uriable/uriable');
        $langs = isset($lang_prefix)?
            transOrOther($lang_prefix, 'admin/uriable/uriable', array_keys($translations)) : $translations;

        $uri = (isset($uriable) && $uriable)? $uriable->getUriIns(app()->getLocale(), app()->make('Site')->id) : null;
        $excludeTypes = $excludeTypes?? [];
        $typeOptions = [];
        if(!in_array('default', $excludeTypes)) $typeOptions['default'] = $langs['type']['default'];
        if(!in_array('link', $excludeTypes)) $typeOptions['link'] = $langs['type']['link'];
        foreach(\App\Models\Uri::$uriableClasses as $class => $translationKey) {
            if(in_array($class, $excludeTypes)) continue;
            $typeOptions[$class] = trans($translationKey);
        }
        foreach(($addTypes?? []) as $class => $translationKey) {
            $typeOptions[$class] = trans($translationKey);
        }
    @endphp
    <div class="form-row">
        <div class="form-group col-md-5">
            <label for="{{$inputBag}}[uri][slug]">{{$langs['label']}}</label>
            <input type="text"
                   id="{{$inputBag}}[uri][slug]"
                   name="{{$inputBag}}[uri][slug]"
                   @isset($defaultUri) placeholder="{{$defaultUri}}" @endisset
                   value="{{ old("{$inputBag}.uri.slug", ($uri? ($uri->pointable_id? $uri->pointable_id : $uri->slug) : '') ) }}"
                   class="form-control @if($errors->$inputBag->has('uri.slug') || $errors->$inputBag->has("uri.pointable_id")) is-invalid @endif" />
        </div>
        <div class="form-group col-md-3">
            @php
                $oldPointableType = old("{$inputBag}.uri.pointable_type",
                    ($uri? ($uri->is_link? 'link' : $uri->pointable_type) : 'none'));
            @endphp
            <label for="{{$inputBag}}[uri][pointable_type]">{{$langs['types']}}</label>
            <select id="{{$inputBag}}[uri][pointable_type]"
                    name="{{$inputBag}}[uri][pointable_type]"
                    class="form-control @if($errors->$inputBag->has("uri.pointable_type")) is-invalid @endif">
                @foreach($typeOptions as $type => $translation)
                    <option value="{{$type}}"
                        @if($type === $oldPointableType) selected="selected" @endif
                    >{{$translation}}</option>
                @endforeach
            </select>
        </div>
    </div>
</fieldset>
