<?php

/// ~ Change these values! ~ ///

// YouTube API key
const APIKEY = "REPLACE_WITH_API_KEY";

// YouTube channel ID(s)
// Can be multiple channels - eg: `array("aaaaaaaaaaaaaaaaaaaa", "bbbbbbbbbbbbbbbbbbbb")`
const CHANNELIDS = array("REPLACE_WITH_CHANNEL_ID");

// Secret - must match ytnotify_subscribe script; should be reasonably hard to guess
const SECRET = "REPLACE_WITH_UNIQUE_SECRET";

// Discord webhook URL
const WEBHOOKURL = "REPLACE_WITH_WEBHOOK_URL";

///   ///   ///  ///   ///   ///



/// Optionally change these values ///

// Use a gaming.youtube.com link for livestreams instead of normal youtube
const PREFER_GAMING_LINK = true;

// Send a notification for livestreams that have just ended, with a link to watch
const NOTIFY_COMPLETED_LIVESTREAMS = true;

///   ///   ///  ///   ///   ///




// Respond to verification at time of subscribe
if (array_key_exists('hub_challenge', $_GET)) {
    foreach (CHANNELIDS as $chid) {
        if ($_GET['hub_topic'] == "https://www.youtube.com/xml/feeds/videos.xml?channel_id=$chid") {
            // Topic is correct, die with challenge reply
            die($_GET['hub_challenge']);
        }
    }
    // We did not request this topic, die with no data
    die();
}

// File to save the last publish time to
$LATEST_FILE = "ytnotify.latest";

$data = file_get_contents("php://input");

// Verify signature
$sig = $_SERVER['HTTP_X_HUB_SIGNATURE'];
if ($sig && strpos($sig, "sha1=") === 0) {
    // Trim sha1= from start
    $sig = substr($sig, 5);
    // Compute what the signature should be
    $goodsig = hash_hmac('sha1', $data, SECRET);
    // Finally, die if they don't match
    if ($sig !== $goodsig) {
        die();
    }
} else {
    die();
}

$xml = simplexml_load_string($data) or die("Error: Cannot create object");
$id = $xml->entry->children("http://www.youtube.com/xml/schemas/2015")->videoId;

// First, determine if this is a livestream or not, and the status of the livestream
$url = "https://www.googleapis.com/youtube/v3/videos?part=liveStreamingDetails&id=$id&maxResults=1&key=" . APIKEY;
$json = json_decode(file_get_contents($url), true);
$item = $json['items'][0];
$isFinishedLiveStream = false;
$isInProgressLiveStream = false;
if (array_key_exists('liveStreamingDetails', $item)) {
    $stream = $item['liveStreamingDetails'];
    if ($stream['actualStartTime'] != null) {
        // This is/was a livestream
        if ($stream['actualEndTime'] != null) {
            // This was a livestream that is now finished
            $isFinishedLiveStream = true;
        } else {
            // This is a livestream that is currently LIVE
            $isInProgressLiveStream = true;
        }
    } else {
        // This is an upcoming livestream that hasn't gone live yet.
        // It can be very dangerous: when a stream ends, a new ID will be
        // generated for the next stream, with the publish time set to NOW.
        // Discard completely.
        die();
    }
}
$isLiveStream = ($isFinishedLiveStream || $isInProgressLiveStream);


$inputdate = "";
if ($isInProgressLiveStream) {
    $inputdate = $stream['actualStartTime'];
} else if ($isFinishedLiveStream) {
    if (NOTIFY_COMPLETED_LIVESTREAMS) {
        $inputdate = $stream['actualEndTime'];
    }
} else {
    $inputdate = $xml->entry->published;
}

$notify = false;
if ($inputdate != "") {
    $latest = file_get_contents($LATEST_FILE);
    if ($latest == "") {
        // No last known video, so send the notification and hope for the best D:
        $notify = true;
    } else {
        // Test dates
        $pubdate = date_create($inputdate);
        $latestdate = date_create($latest);
        if ($pubdate > $latestdate) {
            // It's newer, notify!
            $notify = true;
        }
    }
}

if ($notify) {
    // Prepare the POST input
    $msg = "";
    if ($isInProgressLiveStream) {
        $msg = "\xf0\x9f\x94\xb4 **Livestream started!** \xf0\x9f\x94\xb4";
    } else if ($isFinishedLiveStream) {
        $msg = "A finished livestream is now available as a video:";
    } else {
        $msg = "\xf0\x9f\x8e\x9e **NEW VIDEO!** \xf0\x9f\x8e\x9e";
    }

    if ($isInProgressLiveStream && PREFER_GAMING_LINK) {
        $msg .= "\nhttps://gaming.youtube.com/watch?v=$id";
    } else {
        $msg .= "\nhttps://www.youtube.com/watch?v=$id";
    }

    $data = json_encode(array(
        'content' => $msg
    ));

    // cURL away!
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => WEBHOOKURL,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json;charset=UTF-8'
        ),
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_RETURNTRANSFER => TRUE
    ));
    $response = curl_exec($curl);
    curl_close($curl);

    // Save latest date to file
    file_put_contents($LATEST_FILE, $inputdate);
}

?>
