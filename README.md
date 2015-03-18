# Slackbot

## How to run

1. Enable hm-slack
2. Enable hm-slackbot
3. Configure hm-slackbot in your `wp-config.php`:

```php
// Used for sending arbitrary messages from WP in hm-slack
define( 'HM_SLACK_INCOMING_URL', 'https://hooks.slack.com/services/your/incoming' );

// Slack bot token
define( 'HM_SLACK_BOT_TOKEN', 'my-bot-token' );

// GitHub integration token for issues
define( 'HM_SLACK_GITHUB_TOKEN', 'myexampletokenfromgithub' );
```

4. Run the bot
```bash
$ wp hm-slackbot run
```
