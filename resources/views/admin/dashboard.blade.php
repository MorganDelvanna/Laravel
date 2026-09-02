@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <h2>Admin Dashboard</h2>
        <p>Welcome, {{ auth()->user() ? auth()->user()->username : 'Guest' }}.</p>
    </div>
</div>
@endsection