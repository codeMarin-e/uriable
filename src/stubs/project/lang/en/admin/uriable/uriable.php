<?php
return [
    'label' => 'Uri',
    'types' => 'Point To',
    'type.default' => 'Default',
    'type.link' => 'Link',
    'validation' => \Illuminate\Support\Arr::undot([
        'no_data' =>  'There is no data',
        'uri.slug.max' =>  '`Uri Value` field is too long',
        'uri.slug.already_used' =>  '`Uri Value` is already used',
        'uri.slug.not_a_link' =>  '`Uri Value` is not a valid URL',
        'uri.slug.not_found' =>  'Not found object with such `Uri Value` ID',
        'uri.slug.recursion' =>  'The `Uri Value` ID And `Uri Type` makes recursion',
        'uri.pointable_type.required' =>  '`Uri Type` is required',
        'uri.pointable_type.max' =>  '`Uri Type` is too long',
        'uri.pointable_type.in' =>  '`Uri Type` is not correct type',
    ]),
];
