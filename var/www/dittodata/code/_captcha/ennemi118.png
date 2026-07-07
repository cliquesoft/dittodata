#!/bin/sh
# web.us_simple.sh	Sets up the server-side web.us simple interface for each ajax connection
#
# Created	2018/01/18 by Dave Henderson
# Updated	2018/02/24 by Dave Henderson
#
# NOTE:
# https://unix.stackexchange.com/questions/195318/safely-exiting-while-loops-in-bash




GUI="/tmp/web.de/gui"
LOG="/var/log/web.us.log"




echo -e "Content-Type: text/plain; charset=UTF-8\n\n"

if [ ! -e "$GUI" ]; then
	mknod -m 700 "$GUI" p || {
		echo "ERROR: web.us FIFO can NOT be created!"
		echo "QUIT"
		exit 0
	}
fi


while true; do
	if read LINE <"$GUI" 2>>"$LOG"; then
		[ "$LINE" == 'quit' ] && break
		echo $LINE
	fi
done

echo "QUIT" | tee -a "$LOG"
rm -f "$GUI"
