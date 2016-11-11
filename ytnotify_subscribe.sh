#!/bin/bash

### ~ Change these values! ~ ###

# YouTube channel ID
CHANNELID="REPLACE_WITH_CHANNEL_ID"

# Public callback URL
CALLBACKURL="REPLACE_WITH_CALLBACK_URL"

# Secret - must match ytnotify.php; should be reasonably hard to guess
SECRET="REPLACE_WITH_UNIQUE_SECRET"

###   ###   ###  ###   ###   ###



curl -X POST https://pubsubhubbub.appspot.com/subscribe \
  -d"hub.mode=subscribe" \
  -d"hub.topic=https://www.youtube.com/xml/feeds/videos.xml?channel_id=$CHANNELID" \
  -d"hub.callback=$CALLBACKURL" \
  -d"hub.secret=$SECRET" \
  -d"hub.verify=sync"