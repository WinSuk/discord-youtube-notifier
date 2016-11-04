<?

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => "https://pubsubhubbub.appspot.com/subscribe",
    CURLOPT_POST => 1,
    CURLOPT_POSTFIELDS => array(
        'hub.mode' => 'subscribe',
        'hub.topic' => 'https://www.youtube.com/xml/feeds/videos.xml?channel_id=YOUR_CHANNEL_ID',
        'hub.callback' => 'YOUR_CALLBACK_URL',
        'hub.verify' => 'sync'
    )
));
$response = curl_exec($curl);
curl_close($curl);

?>