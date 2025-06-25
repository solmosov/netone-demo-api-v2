<?php


use App\Events\TestPublicEvent;
use Illuminate\Support\Facades\Route;

Route::get('/test-pusher', function(){
    $message = "Salom, Pusher! Hozir soat: ".now()->toTimeString();

    event(new TestPublicEvent($message));

    return "Event '".$message."' xabari bilan 'test-channel'ga yuborildi!";
});

Route::get('/test-private-pusher', function(){
    $user = \App\Models\User::find(1);

    if(!$user){
        return 'User not found';
    }

    $message = "Salom, ".$user->name."! Sizga maxsus, shaxsiy bildirishnoma keldi";

    event(new \App\Events\PrivateNotificationEvent($user,$message));

    return "Maxfiy event '".$message."' xabari bilan `user.".$user->id."' kanaliga yuborildi!";
});
