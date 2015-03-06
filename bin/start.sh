WP_CLI_BIN=${WP_CLI_BIN:-"$(which wp)"}
ARGS="hm-slackbot run"
PIDFILE="$HOME/.slackbot.pid"

start-stop-daemon --start --background --pidfile $PIDFILE --make-pidfile --exec $WP_CLI_BIN -- $ARGS
