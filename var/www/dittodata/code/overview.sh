#!/bin/sh
# overview.sh	functions pertaining to the 'Overview' tab.
#
# created	2012/05/15 by Dave Henderson (support@cliquesoft.org)
# updated	2022/05/04 by Dave Henderson (support@cliquesoft.org)
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
#	awk		busybox
#	cat		busybox/coreutils
#	cut		busybox/coreutils
#	date		busybox/coreutils
#	echo		busybox/coreutils
#	egrep		busybox/grep
#	grep		busybox/grep
#	ls		busybox/coreutils
#	rm		busybox/coreutils
#	sed		busybox/sed
#	tail		busybox/coreutils
#	tar		busybox/tar
#	tr		busybox/coreutils
#
# NOTES
#  cat ?.log | pv --rate-limit 100 | cat - > test.txt		used to create a slow write for 'lsof' testing




# variable declarations
[ -e /etc/dittodata/global.cfg ] && . /etc/dittodata/global.cfg
TEMP=''




# the below line generates the variables used below (e.g. $_p, $_t, etc)
eval $(./urlgetopt -p _ -l "$QUERY_STRING")




# do some work

if [ "$_A" == 'load' ]; then
	echo -e "Content-type: text/xml\n\n"

	case "$_log" in
		'scheduled')
			echo "<s>"
			echo "   <xml>"
			echo "	<log>"

			IFS=$'\n'
			for JOB in $(grep -A1 ^'# \[' "${DIRCRON}/dittodata" | tail -n +4 | sed 's/^# \[//;s/\]$/ |/;/^--$/d;s/^#.*/disabled;/;s/^[0-9\*].*/enabled;/' | tr '\n' ' ' | sed 's/; /;/g' | tr ';' '\n'); do
				( echo "$JOB" | grep -q ' | disabled'$ ) && continue		# skip disabled jobs
				JOB="$(echo "$JOB" | sed 's/ | enabled//')"

				TEMP=$( ( grep -A1 ^"# \[${JOB}\]"$ "${DIRCRON}/dittodata" | tail -1 | egrep -o ^'([0-9,*/\-]{1,}	){5}' | grep -q '/' ) && echo 'interval' || echo '')
				if [ "$TEMP" == 'interval' ]; then										# if we're dealing with an interval level, then...
					TEMP=$(grep -A1 ^"# \[${JOB}\]"$ "${DIRCRON}/dittodata" | tail -1 | egrep -o ^'([0-9,*/\-]{1,}	){5}')	# store the entire line and process each field below to figure out which interval definition was made
					if (echo $TEMP | grep -q ^'*/'); then
						TEMP="interval|$(echo "$TEMP" | cut -d '	' -f 1 | sed 's|*/||')m|||"
					elif (echo $TEMP | cut -d ' ' -f 2 | grep -q ^'*/'); then
						TEMP="interval|$(echo "$TEMP" | cut -d '	' -f 2 | sed 's|*/||')h||$(echo "$TEMP" | cut -d '	' -f 1)"
					elif (echo $TEMP | cut -d ' ' -f 3 | grep -q ^'*/'); then
						TEMP="interval|$(echo "$TEMP" | cut -d '	' -f 3 | sed 's|*/||')d|$(echo "$TEMP" | cut -d '	' -f 2)|$(echo "$TEMP" | cut -d '	' -f 1)"
					else
						TEMP="interval|$(echo "$TEMP" | cut -d '	' -f 4 | sed 's|*/||')M|$(echo "$TEMP" | cut -d '	' -f 2)|$(echo "$TEMP" | cut -d '	' -f 1)"
					fi
				else														# otherwise we are dealing with days of the month or days of the week
					TEMP=$(grep -A1 ^"# \[${JOB}\]"$ "${DIRCRON}/dittodata" | tail -1 | sed 's/backup .*//;s/\*/\\\*/g')
					[ "$(echo "$TEMP" | cut -d '	' -f 3)" != '\*' ] && TEMP="monthly|$(echo "$TEMP" | cut -d '	' -f 3)|$(echo "$TEMP" | cut -d '	' -f 2)|$(echo "$TEMP" | cut -d '	' -f 1)" || TEMP="daily|$(echo "$TEMP" | cut -d '	' -f 5)|$(echo "$TEMP" | cut -d '	' -f 2)|$(echo "$TEMP" | cut -d '	' -f 1)"
				fi
				FREQ="$(echo "$TEMP" | cut -d '|' -f 1)"
				LIST="$(echo "$TEMP" | cut -d '|' -f 2)"
				HOUR="$(echo "$TEMP" | cut -d '|' -f 3)"
				MIN="$(echo "$TEMP" | cut -d '|' -f 4 | sed 's/^#//' | awk '{printf("%02d", $1)}')"

				[ $HOUR -gt 12 ] && { HOUR=$(($HOUR - 12)); MIN="${MIN} pm"; } || MIN="${MIN} am"
				if ( echo "$LIST" | grep -q 'm'$ ); then
					LIST=$(echo "$LIST" | sed 's/m/ mins/')
				elif ( echo "$LIST" | grep -q 'h'$ ); then
					LIST=$(echo "$LIST" | sed 's/h/ hours/')
				elif ( echo "$LIST" | grep -q 'd'$ ); then
					LIST=$(echo "$LIST" | sed 's/d/ days/')
				elif ( echo "$LIST" | grep -q 'M'$ ); then
					LIST=$(echo "$LIST" | sed 's/M/ months/')
				elif [ "$FREQ" == 'daily' ]; then
					LIST=$(echo "$LIST" | sed 's/1/Mon/;s/2/Tue/;s/3/Wed/;s/4/Thr/;s/5/Fri/;s/6/Sat/;s/7/Sun/')
				fi

				echo "${JOB} @ ${HOUR}:${MIN}, every ${LIST}"
			done

			echo "	</log>"
			echo "   </xml>"
			echo "</s>"
			;;

		'completed')
			echo "<s>"
			echo "   <xml>"
			echo "	<log>"

			for JOB in $(ls -1rt "${DIRLOGS}/${ERRLOG%/*}/"*.err); do
				[ -s "${JOB}" ] && continue					# only process the jobs that don't have an .err log
				TEMP=$(echo "${JOB}" | sed 's|.*/||;s|\..*||')			# strip everything but the job number

				echo "[&lt;a href='#' onClick=\"Logs('load','${TEMP}','scn3'); adjTabs2('aTabSel','tab','scn',3,'adjButtons(3)');\"&gt;${TEMP}&lt;/a&gt; @ $(date -r "${JOB}")]"	# " < this is to stop the coloring in the text editor and can be ignored otherwise
				cat "${JOB}" | sed 's/\&/\&amp;/g;s/</\&lt;/g;s/>/\&gt;/g'
			done

			echo "	</log>"
			echo "   </xml>"
			echo "</s>"
			;;

		'errors')
			echo "<s>"
			echo "   <xml>"
			echo "	<log>"

			echo "[GLOBAL]"
			cat "${DIRLOGS}/${SYSLOG}"

			for JOB in $(ls -1rt "${DIRLOGS}/${ERRLOG%/*}/"*.err); do
				[ -s "${JOB}" ] || continue					# only process the jobs that have an .err log
				TEMP=$(echo "${JOB}" | sed 's|.*/||;s|\..*||')			# strip everything but the job number

				echo
				echo "[&lt;a href='#' onClick=\"Logs('load','${TEMP}','scn3'); adjTabs2('aTabSel','tab','scn',3,'adjButtons(3)');\"&gt;${TEMP}&lt;/a&gt; @ $(date -r "${JOB}")]"	# " < this is to stop the coloring in the text editor and can be ignored otherwise
				cat "${JOB}" | sed 's/\&/\&amp;/g;s/</\&lt;/g;s/>/\&gt;/g'
			done

			echo "	</log>"
			echo "   </xml>"
			echo "</s>"
			;;

		*)
			echo "<s>"
			echo "   <xml>"
			echo "	<log>"

			[ -s "${DIRLOGS}/${ERRLOG%/*}/${_log}.err" ] && {			# if there is an error log, then show it's contents
				echo "[ERRORS]"
				cat "${DIRLOGS}/${ERRLOG%/*}/${_log}.err" | sed 's/&/&amp;/g;s/</&lt;/g;s/>/&gt;/g'
				echo
				echo "[LOG]"
			}
			cat "${DIRLOGS}/${ERRLOG%/*}/${_log}.log" | sed 's/\&/\&amp;/g;s/</\&lt;/g;s/>/\&gt;/g'

			echo "	</log>"
			echo "   </xml>"
			echo "</s>"
			;;
	esac
	exit 0

elif [ "$_A" == 'compress' ] && [ "$_T" == 'logs' ]; then
	echo -e "Content-type: text/xml\n\n"

	TEMP="\"${DIRLOGS}/${SYSLOG}\" \"${DIRLOGS}/${JOBLOG%/*}\""
	[ "${JOBLOG%/*}" != "${ERRLOG%/*}" ] && TEMP="${TEMP} \"${DIRLOGS}/${ERRLOG%/*}\""	# if the location of the two logs is different, then add it to the list to backup

	[ -e "${DIRTEMP}/logs.tgz" ] && rm "${DIRTEMP}/logs.tgz"				# if there is an existing logs.tgz file, then erase it before continuing

	eval tar czf "${DIRTEMP}/logs.tgz" $TEMP 2>/tmp/debug.txt && echo "<s></s>" || echo "<f><msg>The creation of the logs archived failed, check the logs.</msg></f>"
	exit 0

elif [ "$_A" == 'download' ] && [ "$_T" == 'tarball' ]; then
	echo "Content-type: application/x-gzip"
	echo "Content-disposition: attachment; filename=\"logs.tgz\""				# NOTE: the filename MUST be surrounded by double quotes so the value gets parsed correctly in javascript;
	echo											#	also note that there are no trailing '\n\n' characters (only 1 is needed as this will be added to the file contents)

	cat "${DIRTEMP}/logs.tgz"
	exit 0
else
	echo "<f><msg>The action passed is invalid.</msg></f>";
	exit 1
fi

