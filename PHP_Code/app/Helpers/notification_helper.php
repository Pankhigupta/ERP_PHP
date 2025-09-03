<?php

use App\Models\Settings;
use App\Models\User;

function send_notification($user, $title, $body, $type, $image)
{
    $FcmToken1 = User::where('fcm_id', '!=', '')->whereIn('id', $user)->where('device_type', '=' ,'android')->get()->pluck('fcm_id');
    $FcmToken2 = User::where('fcm_id', '!=', '')->whereIn('id', $user)->where('device_type', '=' ,'ios')->get()->pluck('fcm_id');
    $device_type = User::whereIn('id', $user)->pluck('device_type');

    $url = 'https://fcm.googleapis.com/fcm/send';
    $serverKey = Settings::select('message')->where('type', 'fcm_server_key')->pluck('message')->first();

    $notification_data1 = [
        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        "title" => $title,
        "body" => $body,
        "type" => $type,
        "image" => $image,
    ];
    $notification_data2 = [
        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        "title" => $title,
        "body" => $body,
        "type" => $type,
        "image" => $image,
    ];



    if ($device_type->contains('android')) {
        $androidFcmTokens = $FcmToken1->toArray();

        $data1 = [
            "registration_ids" => $androidFcmTokens,
            "data" => $notification_data2,
            "priority" => "high"
        ];

        $encodedData1 = json_encode($data1);

        $result1 = sendNotificationToFCM($url, $serverKey, $encodedData1);
    }

    // Send notification to iOS users
    if ($device_type->contains('ios')) {
        $iosFcmTokens = $FcmToken2->toArray();

        $data2 = [
            "registration_ids" => $iosFcmTokens,
            "notification" => $notification_data1,
            "data" => $notification_data2,
            "priority" => "high"
        ];

        $encodedData2 = json_encode($data2);
        $result2 = sendNotificationToFCM($url, $serverKey, $encodedData2);

    }


}
function sendNotificationToFCM($url, $serverKey, $encodedData) {
    $headers = [
        'Authorization:key=' . $serverKey,
        'Content-Type: application/json',
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

    // Disabling SSL Certificate support temporarly
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);

    // Execute post
    $result = curl_exec($ch);
    if ($result == FALSE) {
        die('Curl failed: ' . curl_error($ch));
    }

    // Close connection
    curl_close($ch);
}
