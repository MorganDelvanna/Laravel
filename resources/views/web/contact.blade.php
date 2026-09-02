@extends('layouts.app')

@section('content')
    <div class="container app">
        <div class="row">
            <div class="col-12">
                <h1>Contact Information</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <b>Mailing Address</b> <br /><br />
                Peterborough Fish and Game Association <br />
                608 Division Rd <br />
                Peterborough, Ontario <br />
                K9J 6Y1 <br />
            </div>
        </div>
        <div class="row">
            <div class="col">
                Membership Information:&nbsp; <span class="emails">/membership|pfga:ca</span>
            </div>
        </div>
        <div class="row">
            <div class="col">
                Contact the webmaster for website Info:&nbsp; <span class="emails">/dawebguy2|pfga.ca</span>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function () {
            let el = $('.emails');
            el.each(function () {
                let data = $(this).text();
                $(this).text(data.replace('|', '@').replace('/', '').replace(':', '.'));
            });
        });
    </script>
@endsection