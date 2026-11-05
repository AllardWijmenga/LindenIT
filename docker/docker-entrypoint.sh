#!/bin/sh
# Logt het starten en stoppen van de applicatie (scenario: container handmatig stoppen).
LOG=/logs/app.log
ts() { date '+%Y-%m-%d %H:%M:%S'; }

mkdir -p /logs
touch "$LOG"
chmod 666 "$LOG"

echo "$(ts) INFO Application started" >> "$LOG"

stop() {
    echo "$(ts) INFO Application stopped" >> "$LOG"
    kill -TERM "$pid" 2>/dev/null
    wait "$pid"
    exit 0
}
trap stop TERM INT

apache2-foreground &
pid=$!
wait "$pid"
