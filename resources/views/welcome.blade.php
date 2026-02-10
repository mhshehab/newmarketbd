@extends('layouts.app')

@section('title', 'Home - FreshCart')

@section('content')
    @include('partials.home-slider')

    @include('partials.category-grid')
@endsection