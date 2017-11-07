<?php

/// ~ Change these values! ~ ///

// YouTube channel ID(s)
// Can be multiple channels - eg: `array("aaaaaaaaaaaaaaaaaaaa", "bbbbbbbbbbbbbbbbbbbb")`
const CHANNELIDS = array("REPLACE_WITH_CHANNEL_ID");

// Public callback URL
const CALLBACKURL = "REPLACE_WITH_CALLBACK_URL";

// Secret - must match ytnotify.php; should be reasonably hard to guess
const SECRET = "REPLACE_WITH_UNIQUE_SECRET";

///   ///   ///  ///   ///   ///


foreach (CHANNELIDS as $chid) {
    echo "Subscribing to $chid...\n";

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://pubsubhubbub.appspot.com/subscribe",
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => array(
            'hub.mode' => 'subscribe',
            'hub.topic' => 'https://www.youtube.com/xml/feeds/videos.xml?channel_id=' . $chid,
            'hub.callback' => CALLBACKURL,
            'hub.secret' => SECRET,
            'hub.verify' => 'sync'
        ),
        CURLOPT_RETURNTRANSFER => TRUE
    ));
    $response = curl_exec($curl);
    curl_close($curl);

    echo "$response\n";
}

echo "Done.\n";

?>
