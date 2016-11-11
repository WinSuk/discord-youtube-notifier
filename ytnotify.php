<?php

/// ~ Change these values! ~ ///

// YouTube channel ID
const CHANNELID = "REPLACE_WITH_CHANNEL_ID";

// Secret - must match ytnotify_subscribe script; should be reasonably hard to guess
const SECRET = "REPLACE_WITH_UNIQUE_SECRET";

// Discord webhook URL
const WEBHOOKURL = "REPLACE_WITH_WEBHOOK_URL";

///   ///   ///  ///   ///   ///



// Respond to verification at time of subscribe
$challenge = $_GET['hub_challenge'];
if (isset($challenge)) {
    if ($_GET['hub_topic'] == "https://www.youtube.com/xml/feeds/videos.xml?channel_id=" . CHANNELID) {
        // Topic is correct, die with challenge reply
        die($challenge);
    } else {
        // We did not request this topic, die with no data
        die();
    }
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

$link = $xml->entry->link['href'];
$published = $xml->entry->published;
$latest = file_get_contents($LATEST_FILE);

$notify = false;
if ($published != "") {
    if ($latest == "") {
        // No last known video, so send the notification and hope for the best D:
        $notify = true;
    } else {
        // Test dates
        $pubdate = date_create($published);
        $latestdate = date_create($latest);
        if ($pubdate > $latestdate) {
            // It's newer, notify!
            $notify = true;
        }
    }
}

if ($notify && $link != "") {
    // Prepare the POST input
    $data = json_encode(array(
        'content' => "\xf0\x9f\x8e\x9e **NEW VIDEO!** \xf0\x9f\x8e\x9e\n$link"
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
    
    // Save latest to file
    file_put_contents($LATEST_FILE, $published);
}

?>