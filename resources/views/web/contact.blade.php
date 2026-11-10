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
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        <p>
            <h1>Contact Form</h1>
            <form action="{{ route('contact.send') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-12">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" name="name">
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <label for="from">Email</label>
                        <input type="email" class="form-control" id="from" name="from">
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <label for="email">To</label>
                        <select class="form-control" id="email" name="email">
                            <option value="membership">Membership - inquiries regarding membership, renewals, and applications.</option>
                            <option value="treasurer">Treasurer - inquiries regarding payments, invoices, and financial matters.</option>
                            <option value="dawebguy">Web Master - inquiries regarding the website, or issues with the website.</option>
                            <option value="action">Action Shooting - inquiries regarding action shooting.</option>
                            <option value="archery">Archery - inquiries regarding archery events.</option>
                            <option value="handgun">Handgun - inquiries regarding handgun events.</option>
                            <option value="rifle">Rifle/Shotgun - inquiries regarding rifle/shotgun.</option>
                            <option value="smallbore">Smallbore Rifle - inquiries regarding smallbore rifle, air rifle/air pistol events.</option>
                            <option value="juniors">Junior Rifle - inquiries regarding the junior rifle program.</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <label for="subject">Subject</label>
                        <input type="text" class="form-control" id="subject" name="subject" maxlength="50">
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <label for="message">Message</label>
                        <textarea class="form-control" id="message" name="message" rows="5"></textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Send</button>
                    </div>
                </div>
            </form>
        </p>
    </div>
@endsection
