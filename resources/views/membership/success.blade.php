@extends('layouts.app')

@section('content')
    <div class="sr-payment-summary completed-view">
        <h1>Your payment succeeded</h1>
    </div>
    <p>
        You have successfully completed your application(s), new members will be considered at the next Board of Directors meeting and you will be contacted<br />
        You will be contacted sometime after the meeting to inform you if you have been accepted or not.<br />
        
        The following has been emailed to the Membership Secretary and to you:
    </p>
    
    <div class="sr-section completed-view">
        <h2>Application Summary</h2>
        <div class="sr-payment-summary completed-view">
            <div class="alert alert-info mt-2"><h3>Application Details</h3>
            @foreach ($collectedBodyHTML as $line)                
                <div class="body-record">
                    {!! $line !!}   
                </div>
                @if(count($collectedBodyHTML) > 1 && !$loop->last) <hr /> @endif
            @endforeach
            </div>
        </div>
    </div>
          
@endsection