<?php


use App\Events\TestPublicEvent;
use Illuminate\Support\Facades\Route;

Route::get('/test-pusher', function(){
    $message = "Salom, Pusher! Hozir soat: ".now()->toTimeString();

    event(new TestPublicEvent($message));

    return "Event '".$message."' xabari bilan 'test-channel'ga yuborildi!";
});
