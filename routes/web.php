<?php

use Illuminate\Support\Facades\Route;


use Kreait\Firebase\Factory;

Route::get('/firebase-test', function () {
    try {
        $credentialsPath = config('firebase.projects.app.credentials');

        if (!$credentialsPath) {
            return ["status" => "error", "message" => "Credentials null"];
        }

        $absolutePath = base_path($credentialsPath);

        if (!file_exists($absolutePath)) {
            return [
                "status"      => "error",
                "message"     => "File tidak ditemukan",
                "path_dicari" => $absolutePath
            ];
        }

        $factory   = (new Factory)->withServiceAccount($absolutePath);
        $messaging = $factory->createMessaging();

        return ["status" => "success", "message" => "Firebase Messaging Connected"];
    } catch (\Throwable $e) {
        return ["status" => "error", "message" => $e->getMessage()];
    }
});


Route::get('/', function () {
    return redirect()->route('filament.admin.auth.login');
});

use App\Events\TestBroadcast;

Route::get('/test-broadcast', function () {
    broadcast(new TestBroadcast("Hello Reverb 🚀"));
    return "Event sent!";
});
