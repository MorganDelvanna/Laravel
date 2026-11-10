<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () { return view('web.welcome'); })->name('home');
Route::get('/rules', function () { return view('web.rules'); });
Route::get('/schedule', function () { return view('web.schedule'); });
Route::get('/membership', function () { return view('web.membership'); });
Route::get('/faq', function () { return view('web.faq'); });
Route::get('/history', function () { return view('web.history'); });
Route::get('/location', function () { return view('web.location'); });
Route::get('/newmember', function () { return view('web.newmember'); });
Route::get('/renewal', function () { return view('web.renewal'); });
Route::get('/shows', function () { return view('web.shows'); });
Route::get('/links', function () { return view('web.links'); });
Route::get('/calendar', function () { return view('web.calendar'); })->name('calendar');
Route::get('/news', function () { return view('web.news'); })->name('news');
Route::get('/indoors', function () { return view('web.indoors'); });
Route::get('/outdoors', function () { return view('web.outdoors'); });
Route::get('/courses', function () { return view('web.courses'); });
Route::get('/property', function () { return view('web.property'); });
Route::get('/sections', function () { return view('web.sections'); });
Route::get('/contact', function () { return view('web.contact'); });
Route::get('/directions', function () { return view('web.directions'); });
Route::get('/directions', function () { return view('web.directions'); });

Route::get('/action', function () { return view('sections.action'); });
Route::get('/archery', function () { return view('sections.archery'); });
Route::get('/handgun', function () { return view('sections.handgun'); });
Route::get('/rifles', function () { return view('sections.rifle'); });
Route::get('/smallbore', function () { return view('sections.smallbore'); });
Route::get('/juniors', function () { return view('sections.juniors'); });

Route::post('/contact', [App\Http\Controllers\ContactController::class, 'send'])->name('contact.send');


// include admin routes
if (file_exists(__DIR__ . '/admin.php')) {
    require __DIR__ . '/admin.php';
}
// include news Routes
if (file_exists(__DIR__ . '/news.php')) {
    require __DIR__ . '/news.php';
}
// include calendar routes
if (file_exists(__DIR__ . '/calendar.php')) {
    require __DIR__ . '/calendar.php';
}
// include membership routes
if (file_exists(__DIR__ . "/membership.php")) {
    require __DIR__ . '/membership.php';
}

