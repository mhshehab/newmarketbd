@extends('layouts.app')

@section('title', 'Home - ' . \App\Models\Setting::getValue('website_name', 'Default Name'))

@section('content')
    @include('partials.home-slider')

    @include('partials.category-grid')

    @include('partials.featured-products')

    @include('partials.special-offers')

    @include('partials.testimonials')

    @include('partials.brands')
@endsection