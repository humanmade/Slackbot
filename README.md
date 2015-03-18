# Slackbot

## How to run

1. Enable [hm-slack](https://github.com/humanmade/hm-slack) first, then enable
   this plugin as well.

   (HM-Slack contains utilities, such as escaping functions.)

2. Head over to [your Slack integrations page](https://slack.com/services), and
   create a new bot user.

   This will give you an API token, which you'll need in a second.

3. For full features, you'll also need an Incoming Webhook integration, so set
   one of those up too.

3. Configure hm-slackbot in your `wp-config.php`:

```php
// Used for sending arbitrary messages from WP in hm-slack
define( 'HM_SLACK_INCOMING_URL', 'https://hooks.slack.com/services/your/incoming' );

// Slack bot token
define( 'HM_SLACK_BOT_TOKEN', 'my-bot-token' );

// GitHub integration token for issues
define( 'HM_SLACK_GITHUB_TOKEN', 'myexampletokenfromgithub' );

// Set up the bot's name
define( 'HM_SLACK_BOT_NAME', 'rmbot' );
define( 'HM_SLACK_BOT_ID',   'U03M7H4V5' );

// Set up bot admin (user ID)
define( 'HM_SLACK_BOT_ADMIN_ID', 'U03BWLTDD' );
```

4. Run the bot
```bash
$ wp hm-slackbot run
```
