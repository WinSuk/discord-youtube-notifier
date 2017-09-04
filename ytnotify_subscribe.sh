#!/bin/bash

### ~ Change these values! ~ ###

# YouTube channel ID(s)
# Can be multiple channels - eg: `CHANNELIDS=("aaaaaaaaaaaaaaaaaaaa" "bbbbbbbbbbbbbbbbbbbb")`
CHANNELIDS=("REPLACE_WITH_CHANNEL_ID")

# Public callback URL
CALLBACKURL="REPLACE_WITH_CALLBACK_URL"

# Secret - must match ytnotify.php; should be reasonably hard to guess
SECRET="REPLACE_WITH_UNIQUE_SECRET"

###   ###   ###  ###   ###   ###



for chid in "${CHANNELIDS[@]}"
do
    echo "Subscribing to $chid..."

    curl -X POST https://pubsubhubbub.appspot.com/subscribe \
      -d"hub.mode=subscribe" \
      -d"hub.topic=https://www.youtube.com/xml/feeds/videos.xml?channel_id=$chid" \
      -d"hub.callback=$CALLBACKURL" \
      -d"hub.secret=$SECRET" \
      -d"hub.verify=sync"
done

echo "Done."