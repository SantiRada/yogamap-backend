<?php
function sendNotification($to, $title, $body, $data = [])
{
    // Obtener la clave desde variables de entorno
    $serverKey = getenv('FCM_SERVER_KEY');

    if (!$serverKey) {
        return ['success' => false, 'error' => 'FCM server key not found.'];
    }

    $url = 'https://fcm.googleapis.com/fcm/send';

    $payload = [
        'to' => $to,
        'notification' => [
            'title' => $title,
            'body' => $body,
            'sound' => 'default',
        ],
        'data' => $data
    ];

    $headers = [
        'Authorization: key=' . $serverKey,
        'Content-Type: application/json',
    ];

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

    $result = curl_exec($ch);

    if ($result === false) {
        return ['success' => false, 'error' => curl_error($ch)];
    }

    curl_close($ch);

    return json_decode($result, true);
}
?>
