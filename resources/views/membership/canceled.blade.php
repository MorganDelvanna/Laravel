@extends('layouts.app')

@section('content')
    <div class="row">
      <div class="col-12 center">
          <img alt="Peterborough Fish & Game Association" src="../images/header.gif" />
      </div>
    </div>
    <div class="row">
        <div class="col-12 app">
            <menu-control></menu-control>
        </div>
    </div>
    <div class="sr-root">
      <div class="sr-main">
        <div class="sr-payment-summary completed-view">
          <h1>Your payment was canceled</h1>
        </div>
        <div class="sr-section">
          <p>If you cancelled by mistake you may re-submit your application.</p>
        </div>
      </div>
    </div>
@endsection