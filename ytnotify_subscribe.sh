#!/bin/bash

curl -X POST https://pubsubhubbub.appspot.com/subscribe \
  -d'hub.mode=subscribe' \
  -d'hub.topic=https://www.youtube.com/xml/feeds/videos.xml?channel_id=YOUR_CHANNEL_ID' \
  -d'hub.callback=YOUR_CALLBACK_URL' \
  -d'hub.verify=sync'