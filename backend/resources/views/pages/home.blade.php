@extends('layouts.app')

@section('title', 'Feed Notizie')
@section('requires_auth', true)

@section('content')
    <div
        data-vue-component="NewsFeed"
        data-adsense-publisher="{{ config('ads.adsense_publisher_id') }}"
        data-adsense-feed-slot="{{ config('ads.feed_ad_slot') }}"
        data-adsense-frequency="{{ config('ads.feed_ad_frequency') }}"
    ></div>
@endsection
