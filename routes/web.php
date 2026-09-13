<?php

use Illuminate\Support\Facades\Route;

Route::get('/{any?}', function () {
    return view('admin');
})->where('any', '.*');
Route::get('/test-mail', function () {

    Mail::raw('This is a test email from Poriseba.', function ($message) {
        $message->to('karunadri@yopmail.com')
                ->subject('Poriseba Test Email');
    });

    return 'Mail sent';
});
