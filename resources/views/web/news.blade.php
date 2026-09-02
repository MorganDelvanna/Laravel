@extends('layouts.app')

@section('content')
    <div class="container app">
        <div class="row">
            <div class="col-12">
                <h1>News</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-12">                
                @include('partials.news-viewer')
            </div>
        </div>
    </div>
@endsection