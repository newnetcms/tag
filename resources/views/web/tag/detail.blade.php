@extends('master')

@section('meta_title', $tag->name)

@section('body-class', 'tag-detail')

@section('content')
    @include('tag::web.tag.content')
@stop
