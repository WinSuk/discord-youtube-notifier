#!/bin/bash

### ~ Change these values! ~ ###

# YouTube channel ID
CHANNELID="REPLACE_WITH_CHANNEL_ID"

# Public callback URL
CALLBACKURL="REPLACE_WITH_CALLBACK_URL"

###   ###   ###  ###   ###   ###



curl -X POST https://pubsubhubbub.appspot.com/subscribe \
  -d"hub.mode=subscribe" \
  -d"hub.topic=https://www.youtube.com/xml/feeds/videos.xml?channel_id=$CHANNELID" \
  -d"hub.callback=$CALLBACKURL" \
  -d"hub.verify=sync"