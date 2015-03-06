PIDFILE="$HOME/.slackbot.pid"

start-stop-daemon --stop --pidfile $PIDFILE --make-pidfile && rm $PIDFILE
