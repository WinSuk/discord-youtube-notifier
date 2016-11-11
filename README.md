## YouTube upload notification via Discord webhook

**Demo:**

![Demo Image](https://raw.githubusercontent.com/WinSuk/discord-ytupload-notifier/master/demo.png)

Usually triggers about 5 minutes before an email is sent about the same upload


**Requirements:**
- PHP webserver with curl
  * (could easily be converted to some other server/language though)
- Permission for PHP to create a file in the same directory
  * I assumed this was pretty standard, but I ran into it recently so I'm listing it here. After a notification shows in Discord, there should be a `ytnotify.latest` file on the webserver - if not, something is broken.


**Setup:**
- Create a webhook on Discord (edit a text channel > Webhooks > Create Webhook), and copy the webhook URL
- Edit ytnotify.php with a text editor:
  * Change REPLACE_WITH_CHANNEL_ID to your YouTube channel ID (more info: https://developers.google.com/youtube/v3/guides/working_with_channel_ids)
  * Change REPLACE_WITH_WEBHOOK_URL to your Discord webhook URL
- Upload ytnotify.php to a public location on your webserver
- Edit ytnotify_subscribe.sh/php:
  * Change REPLACE_WITH_CHANNEL_ID to your YouTube channel ID
  * Change REPLACE_WITH_CALLBACK_URL to the public URL of ytnotify.php (including http[s]://)

ytnotify_subscribe needs to be run regularly - the subscription times out after a set time (432000 seconds/5 days last I checked).
This is best done with a cronjob on the server - I have mine run at 5am every Monday and Friday.

Since it falls back to notifying when there's no last known publish date, the first notification could be from a title or description change.


**Known issues:**
- No notification when a livestream is started. I'm not sure if YouTube even has a way of doing this - I couldn't find anything. However:
- When a livestream ends, the video of it gets pushed as a new upload, even though the video is unlisted.
  * I'm not sure if this always happens, or if it's with a specific livestreaming setup as I haven't really tested it.
- Keeping track of the last publish time with a file is probably not the best