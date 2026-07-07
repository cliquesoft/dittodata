#!/bin/sh
# status.sh	Sets up the server-side web.us simple interface for each ajax connection
#
# Created	2018/01/18 by Dave Henderson
# Updated	2022/05/19 by Dave Henderson
#
# Unless a valid Cliquesoft Private License (CPLv1) has been purchased for your
# device, this software is licensed under the Cliquesoft Public License (CPLv2)
# as found on the Cliquesoft website at www.cliquesoft.org.
#
# This program is distributed in the hope that it will be useful, but WITHOUT
# ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
# FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.
#
# Requirements:		Works With:
#	[		busybox/coreutils
#	date		busybox/coreutils
#	echo		busybox/coreutils
#	grep		busybox/grep
#	mknod		busybox/coreutils
#	ps		busybox/procps
#	rm		busybox/coreutils
#	wc		busybox/coreutils
#
# NOTE:
# https://unix.stackexchange.com/questions/195318/safely-exiting-while-loops-in-bash




# variable declarations
[ -e /etc/dittodata/global.cfg ] && . /etc/dittodata/global.cfg
TEMP=''
DATETIME="$(date +"%Y-%m-%d %H:%M:%S")"				# sets a default epoch so in the event of failure, a full backup should be performed via this date for safety
GUI="${DIRTEMP}/gui"




# the below line generates the variables used below (e.g. $_p, $_t, etc)
eval $(./urlgetopt -p _ -l "$QUERY_STRING")




if [ ! -z ${_A+x} ]; then					# https://stackoverflow.com/questions/3601515/how-to-check-if-a-variable-is-set-in-bash
	echo -e "Content-type: text/xml\n\n"

	if [ "$_A" == 'check' ] && [ "$_T" == 'webus' ]; then
		# if only two instances are running (this one checking and the subshell below), then we need to start it, otherwise it is already running, so...
		[ $(ps x -o pid,command | grep '/bin/sh [s]tatus.sh' | wc -l) -eq 2 ] && echo "<s><data start='true' /></s>" || echo "<s><data start='false' /></s>"

	elif [ "$_A" == 'cleanup' ] && [ "$_T" == 'job' ]; then
		[ -e "${DIRTEMP}/${_rjn}" ] && rm -f "${DIRTEMP}/${_rjn}" 2>>"${DIRLOGS}/${SYSLOG}"
		echo "<s></s>"

	elif [ "$_A" == 'cleanup' ] && [ "$_T" == 'job' ]; then
		[ -e "${GUI}" ] && echo quit >"${GUI}"
		echo "<s></s>"
	fi
	exit 0
fi




# if we've made it here, then we need to start web.us
# NOTE: this will will automatically close if communication stops with the browser (e.g. the window gets closed); the FIFO will remain present however.

echo -e "Content-Type: text/plain; charset=UTF-8\n\n"

if [ ! -e "$GUI" ]; then
	mknod -m 700 "$GUI" p 2>>"${DIRLOGS}/${SYSLOG}" || {
		echo -n "${DATETIME}	" >>"${DIRLOGS}/${SYSLOG}"
		echo "ERROR: web.us FIFO can NOT be created!" | tee -a "${DIRLOGS}/${SYSLOG}"
		echo "QUIT"
		exit 0
	}
fi


while true; do
	if read LINE <"$GUI" 2>>"${DIRLOGS}/${SYSLOG}"; then
		[ "$LINE" == 'quit' ] && break
		[ "${LINE#*|}" == '' ] && continue		# skip all blank lines

		LINE="${LINE//\"/\\\"}"				# escape all quotes
# LEFT OFF - if LINE=numbers, then move a percent meter, otherwise just post the text
		echo "Status('post',${LINE%|*},\"${LINE#*|}\");"
	fi
done

echo "QUIT" && rm -f "$GUI"

