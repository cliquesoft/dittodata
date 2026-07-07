#!/bin/sh
# web.us_robust.sh	Sets up the persistent "server" side FIFO of web.us
#
# Created	2018/01/12 by Dave Henderson
# Updated	2022/01/14 by Dave Henderson
#
# To send to FI:	echo hello > /tmp/in
# To read from FO:	while true; do read LINE </tmp/out && echo $LINE; done
#
# Example:
#	inotify.sh	this is a sample executable that needs to communicate with the GUI
#	web.us.sh	this is the bus script
#
#	/tmp/web.us.sh		starts the bus
#	inotify.sh 'SID|open'	requests a new fifo for this script; NOTE: SID is a unique string for communication between fifo's 'cli' and 'out' so multiple calls know which response to process
#
#	inotify.sh 'ID|close'	requests that an existing fifo get closed; NOTE: ID is the number behind the 'cli_' prefix (e.g. cli_1234 > 1234)
#
#
# https://bash.cyberciti.biz/guide/Putting_functions_in_background




# Variable Declarations
PID=''
CLI=''
GUI=''
ISO=''
TMP=''
DIR='/tmp'
LOG="/var/log/web.us.log"
FI="${DIR}/web.de/cli"
FO="${DIR}/web.de/gui"
RE="${DIR}/web.de/out"									# for replies

# Function Declarations
toCLI() {
# sends commands to the CLI interface
	while true; do
		if read LINE; then
			if [ "$LINE" == 'quit' ]; then
				break
			elif [ "${LINE%%|*}" == 'close' ]; then				# Syntax: echo 'close|1234|a32b' >/tmp/cli	NOTE: '1234' is the id behind the 'cli_' prefix (e.g. /tmp/cli_1234), 'a32b' is the PID
				TMP="$(echo "$LINE" | cut -d '|' -f 2)"			# store the id value (e.g. 1234)
				echo -n "Closing a FIFO: [ID] [${TMP}]"
				[ ! -e "${FI}_${TMP}" ] && {				# return 'id|0' on failure
					echo "${TMP}|0" >>$RE
					echo "ERROR: web.us FIFO [${FI}_${TMP}] does NOT currently exist!" >>$LOG
					continue
				}
				echo -n " [PID] [${LINE##*|}]"
				kill ${LINE##*|} || {
					echo "${TMP}|0" >>$RE
					echo "ERROR: web.us code [${TMP}] can NOT be killed!" >>$LOG
					continue
				}
				rm "${FI}_${TMP}" >/dev/null 2>>"$LOG" || {
					echo "${TMP}|0" >>$RE
					echo "ERROR: web.us FIFO [${FI}_${TMP}] can NOT be deleted!" >>$LOG
					continue
				}
				echo "${TMP}|1" >>$RE					# return 'id|1' on success
				echo ' [done]'
				break
			elif [ "${LINE%%|*}" == 'open' ]; then				# Syntax: echo 'open|a32b' >/tmp/cli	NOTE: 'a32b' is a unique SID to request a new 'cli_####' fifo
				echo -n "Opening a FIFO: [SID] [${LINE#*|}]"
				TMP=$RANDOM
				[ -e "${FI}_${TMP}" ] && { echo "${LINE#*|}|0" >>$RE; continue; }	# return 'sid|0' on failure
				mknod -m 700 ${FI}_${TMP} p >/dev/null 2>>"$LOG" || { echo "${LINE#*|}|0" >>$RE; continue; }
				toISO &
				ISO=$!
				echo -n " [ID] [${TMP}] [PID] [${ISO}]"
				echo "${LINE#*|}|${TMP}|${ISO}" >>$RE													# return 'sid|id|pid' on success
				echo ' [done]'
			else
				# NOTE: this should only be javascript function calls (e.g. reloadShares('xyz','123');)
				echo $LINE >>$FI
			fi
		fi
	# WARNING - the '< $FO' belongs after the 'read' call above, not at the end of the 'while' loop!!!!
	done < $FO
}

toGUI() {
# sends commands to the GUI interface
	while true; do
		if read LINE; then
			if [ "$LINE" == 'quit' ]; then
				break
			else
				echo $LINE >>$FO
			fi
		fi
	done < $FI
}

toISO() {
# sends commands to the ISOLATED interface
	while true; do
		if read LINE; then
			if [ "$LINE" == 'quit' ]; then
				break
			else
				echo $LINE >>${FO}_${TMP}
			fi
		fi
	done < ${FI}_${TMP}
}




# Process any passed parameters
[ ! "$1" ] && { echo "You must pass either 'cli' or 'gui' as a parameter."; exit 0; }
[ "$1" == '--help' ] && {
	echo
	echo "Usage syntax: ${0##*/} {cli|gui}"
	echo
	echo "	cli	starts an instance to monitor the CLI fifo"
	echo "	gui	starts an instance to monitor the GUI fifo"
	echo
	exit 0
}

# Setup the fifo's for this script to process
[ ! -e $FI ] && mknod -m 700 $FI p
[ ! -e $FO ] && mknod -m 700 $FO p
[ ! -e $RE ] && mknod -m 700 $RE p

# remove the fifo's on exit of this script
trap "[ \"$CLI\" ] && kill $CLI; [ \"$GUI\" ] && kill $GUI; rm -f $FI $FO $RE" EXIT




# Start reading from the fifo's
if [ "$1" == 'cli' ]; then
	echo 'Starting CLI fifo...'
	toCLI
	exit 0
elif [ "$1" == 'gui' ] || [ "$1" == '' ]; then
	echo 'Starting GUI fifo...'
	toGUI
	exit 0
fi
