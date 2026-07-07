#!/bin/sh
# jobs.sh	functions pertaining to the 'Jobs' tab.
#
# created	2012/05/15 by Dave Henderson (support@cliquesoft.org)
# updated	2022/05/19 by Dave Henderson (support@cliquesoft.org)
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
#	at		at
#	awk		busybox
#	cat		busybox/coreutils
#	chmod		busybox/coreutils
#	cut		busybox/coreutils
#	crontab		busybox/cron
#	echo		busybox/coreutils
#	grep		busybox/grep
#	head		busybox/coreutils
#	ps		busybox/procps
#	rm		busybox/coreutils
#	sed		busybox/sed
#	sleep		busybox/coreutils
#	tail		busybox/coreutils
#	uniq		busybox/coreutils
#	wc		busybox/coreutils
#
# NOTES
#  ps --ppid $TEMP h -o pid,command				shows all the child processes from the supplied parent PID
#  ps -eH h -o pid,command					shows the entire process list in a "tree" fashion
#  ps h -p $_pollJob &>/dev/null || { ... }			test if the job binary is still running and if not, then exit this function




# variable declarations
[ -e /etc/dittodata/global.cfg ] && . /etc/dittodata/global.cfg
TEMP=''




# the below line generates the variables used below (e.g. $_p, $_t, etc)
eval $(./urlgetopt -p _ -l "$QUERY_STRING")
export HOME=$(eval echo ~$_UN)							# NOTE: these were added for usage by 'paged' or other web servers	WARNING: these MUST come AFTER the 'urlgetopt' call above so the $_UN value is accurate!




# do some work

echo -e "Content-type: text/xml\n\n"

if [ "$_A" == 'load' ] && [ "$_T" == 'jobs' ]; then
	echo "<s>"
	echo "   <xml>"
	echo "	<jobs>"
# LEFT OFF - USE IN LIVE
	TEMP="$(dittodata --show 2>>"${DIRLOGS}/${SYSLOG}")"
#	TEMP="$(dittodata --show --config=/Users/siege/Projects/Scripts/dittodata/_EXTRAS/test.conf 2>>"${DIRLOGS}/${SYSLOG}")"

	IFS=$'\n'
	for JOB in $(echo "$TEMP"); do
		echo "		<job>$(echo -n "$JOB" | sed 's/ | .*//')</job>"
	done

	echo "	</jobs>"
	echo "   </xml>"
	echo "</s>"


elif [ "$_T" == 'job' ]; then
	if [ "$_A" == 'load' ]; then
# LEFT OFF - remove the '--config' value below
		eval $(dittodata --info --name="$_job" 2>>"${DIRLOGS}/${SYSLOG}")
#		eval $(dittodata --info --name="$_job" --config=/Users/siege/Projects/Scripts/dittodata/_EXTRAS/test.conf 2>>"${DIRLOGS}/${SYSLOG}")

		FLAGS=''							# WARNING: this MUST come in the order since TEMP is used multiple times in this section
		[ "$(grep -A1 ^"# \[${NAME}\]"$ "${DIRCRON}/dittodata" | tail -1 | cut -d '	' -f 7)" != '' ] && FLAGS='name'
		TEMP=$(crontab -l 2>/dev/null | grep -Fof "${DIRCRON}/dittodata" | uniq | wc -l)		# https://stackoverflow.com/questions/15059422/finding-contents-of-one-file-in-another-file
		if [ $(cat "${DIRCRON}/dittodata" | sed '/^$/d' | wc -l) -eq $TEMP ]; then			# determines if the users crontab contains the dittodata cron file, then...
			[ "$FLAGS" == '' ] && FLAGS='cron' || FLAGS='both'					#   assign the appropriate value depending on the pre-existing variable value
		fi

		if ( echo "$LIMIT" | grep -q ^'[0-9]*'$ ); then			# if the user wants to keep a certain number of prior backups, then...
			TEMP=0
		elif ( echo "$LIMIT" | grep -q 'MB'$ ); then			# if the user wants to keep the backups up to a given MB, then...
			TEMP=1
		else								# if there's no 'count', then the user is 'manually' handling the backups
			TEMP=2
		fi

		if ( echo "$LIST" | grep -q 'm'$ ); then
			LIST=$(echo "$LIST" | sed 's/m/ Minutes/')
		elif ( echo "$LIST" | grep -q 'h'$ ); then
			LIST=$(echo "$LIST" | sed 's/h/ Hours/')
		elif ( echo "$LIST" | grep -q 'd'$ ); then
			LIST=$(echo "$LIST" | sed 's/d/ Days/')
		elif ( echo "$LIST" | grep -q 'M'$ ); then
			LIST=$(echo "$LIST" | sed 's/M/ Months/')
		elif [ "$FREQ" == 'daily' ]; then
			LIST=$(echo "$LIST" | sed 's/1/Mon/;s/2/Tue/;s/3/Wed/;s/4/Thr/;s/5/Fri/;s/6/Sat/;s/7/Sun/')
		fi

		echo "<s>"
		echo "   <xml>"
		echo "	<job name=\"${NAME}\" batteries=\"0\" enabled=\"${STATUS}\" verify=\"0\" type=\"${TYPE}\" compression=\"${COMPRESSION}\" prescript=\"${PRESCRIPT}\" postscript=\"${POSTSCRIPT}\">"
		echo "		<schedule hour=\"${HOUR}\" min=\"${MIN}\" freq=\"${FREQ}\" list=\"Every ${LIST}\" />"
		echo "		<cron flags=\"${FLAGS}\" />"
		echo "		<storage type=\"${TEMP}\" value=\"${LIMIT%MB*}\" />"
		echo "		<fs source=\"${SOURCE}\" sourceUser=\"${SOURCEUSER}\" sourcePass=\"${SOURCEPASS}\" sourceDomn=\"${SOURCEDOMN}\" sourceParm=\"${SOURCEPARM}\" target=\"${TARGET}\" targetUser=\"${TARGETUSER}\" targetPass=\"${TARGETPASS}\" targetDomn=\"${TARGETDOMN}\" targetParm=\"${TARGETPARM}\" targetDir=\"${DIRTAG}\" sudo=\"${SUDO}\" />"
		echo "		<contact alert=\"${ALERT}\" exec=\"${ALERTEXEC//&/&amp;}\" />"
		echo "		<tags tag1=\"${TAG1}\" tag2=\"${TAG2}\" tag3=\"${TAG3}\" tag4=\"${TAG4}\" />"
		echo "		<params>${CUSTOMPARM}</params>"
		if [ "${INCLUDE}" != '' ]; then					# if an 'include' file exists, then parse it!
			while read LINE; do
				echo "		<i>$LINE</i>"
			done <"${INCLUDE}"
		else
			echo "		<i>.</i>"
		fi
		if [ "${EXCLUDE}" != '' ]; then					# same for the 'exclude' file!
			while read LINE; do
				echo "		<e>$LINE</e>"
			done <"${EXCLUDE}"
		fi
		echo -n "		<notes>"
		[ -e "${DIRCONF}/${NAME}.txt" ] && cat "${DIRCONF}/${NAME}.txt" | sed 's/&/&amp;/g'
		[ -e "${DIRHOME}/${NAME}.txt" ] && cat "${DIRHOME}/${NAME}.txt" | sed 's/&/&amp;/g'
		echo "</notes>"
		echo "	</job>"
		echo "   </xml>"
		echo "</s>"

	elif [ "$_A" == 'save' ]; then
		# make some value substitutions
		[ "${_cmbAMPM}" == 'pm' ] && _txtHour=$(( ${_txtHour} + 12 ))
		_txtSchedule="${_txtSchedule/Every /}"
		if ( echo "$_txtSchedule" | grep -q ' Minutes'$ ); then
			_txtSchedule=$(echo "$_txtSchedule" | sed 's/ Minutes/m/')
		elif ( echo "$_txtSchedule" | grep -q ' Hours'$ ); then
			_txtSchedule=$(echo "$_txtSchedule" | sed 's/ Hours/h/')
		elif ( echo "$_txtSchedule" | grep -q ' Days'$ ); then
			_txtSchedule=$(echo "$_txtSchedule" | sed 's/ Days/d/')
		elif ( echo "$_txtSchedule" | grep -q ' Months'$ ); then
			_txtSchedule=$(echo "$_txtSchedule" | sed 's/ Months/M/')
		elif [ "$_cmbFrequency" == 'daily' ]; then
			_txtSchedule=$(echo "$_txtSchedule" | sed 's/Mon/1/;s/Tue/2/;s/Wed/3/;s/Thr/4/;s/Fri/5/;s/Sat/6/;s/Sun/7/;s/ //g')
		fi

		TEMP="--name=\"${_job}\" --type='${_cmbType}' --compression='${_cmbCompression}' --hour='${_txtHour}' --min='${_txtMinute}' --freq='${_cmbFrequency}' --list='${_txtSchedule}'"

		( [ "${_cmbCron}" == 'name' ] || [ "${_cmbCron}" == 'both' ]) && TEMP="--cronName ${TEMP}"
		( [ "${_cmbCron}" == 'cron' ] || [ "${_cmbCron}" == 'both' ]) && TEMP="--cron ${TEMP}"

		[ "${_cmbSudo}" == 'true' ] && TEMP="--sudo ${TEMP}"

		[ "${_radStoretype}" == '0' ] && TEMP="${TEMP} --limit='${_txtStoreAmt}'"
		[ "${_radStoretype}" == '1' ] && TEMP="${TEMP} --limit='${_txtStoreQuota}MB'"
		[ "${_radStoretype}" == '2' ] && TEMP="${TEMP} --limit=''"

		TEMP="${TEMP} --prescript=\"$(echo "${_txtPrescript}" | sed 's/"/\\\"/g')\""		# ' < used to keep color coding in the editor correct, comment can safely be ignored
		TEMP="${TEMP} --postscript=\"$(echo "${_txtPostscript}" | sed 's/"/\\\"/g')\""		# ' < used to keep color coding in the editor correct, comment can safely be ignored

		TEMP="${TEMP} --alert='${_cmbAlert}'"
		TEMP="${TEMP} --alertExec='${_txtAlertExec//\$/\\\$}'"					# we MUST escape variables
# LEFT OFF - test what happens with 'backup' when the ALERTEXEC vars are escaped

		if [ "${_txtParameters}" != '' ]; then
			TEMP="${TEMP} --customParm=\"$(echo "${_txtParameters}" | sed 's/"/\\\"/g')\""	# ' < used to keep color coding in the editor correct, comment can safely be ignored
		else
			TEMP="${TEMP} --customParm=\"-P --one-file-system --ignore-failed-read --ignore-case\""
		fi

		TEMP="${TEMP} --source=\"${_txtSource}\""
		TEMP="${TEMP} --sourceParm=\"$(echo "${_txtSourceParm}" | sed 's/"/\\\"/g')\""		# ' < used to keep color coding in the editor correct, comment can safely be ignored
		TEMP="${TEMP} --sourceUser=\"${_txtSourceUser}\""
		TEMP="${TEMP} --sourcePass='$(echo "${_txtSourcePass}" | sed "s/'/\\\'/g")'"
		TEMP="${TEMP} --sourceDomn=\"${_txtSourceDomn}\""

		TEMP="${TEMP} --target=\"${_txtTarget}\""
		TEMP="${TEMP} --targetParm=\"$(echo "${_txtTargetParm}" | sed 's/"/\\\"/g')\""		# ' < used to keep color coding in the editor correct, comment can safely be ignored
		TEMP="${TEMP} --targetUser=\"${_txtTargetUser}\""
		TEMP="${TEMP} --targetPass='$(echo "${_txtTargetPass}" | sed "s/'/\\\'/g")'"
		TEMP="${TEMP} --targetDomn=\"${_txtTargetDomn}\""
		TEMP="${TEMP} --dirTag=\"${_txtTargetDir}\""

		TEMP="${TEMP} --tag1=\"$(echo "${_txtTag1}" | sed 's/"/\\\"/g')\""			# ' < used to keep color coding in the editor correct, comment can safely be ignored
		TEMP="${TEMP} --tag2=\"$(echo "${_txtTag2}" | sed 's/"/\\\"/g')\""			# ' < used to keep color coding in the editor correct, comment can safely be ignored
		TEMP="${TEMP} --tag3=\"$(echo "${_txtTag3}" | sed 's/"/\\\"/g')\""			# ' < used to keep color coding in the editor correct, comment can safely be ignored
		TEMP="${TEMP} --tag4=\"$(echo "${_txtTag4}" | sed 's/"/\\\"/g')\""			# ' < used to keep color coding in the editor correct, comment can safely be ignored

# LEFT OFF - do we need to do urldecode here? (see jQuery.FileTree.sh)
		[ "${_txtNotes}" != '' ] && echo -e "${_txtNotes}" >"${DIRCONF}/${_job}.txt"
		[ "${_txtNotes}" == '' ] && [ -e "${DIRCONF}/${_job}.txt" ] && rm -f "${DIRCONF}/${_job}.txt"

		eval dittodata --save $TEMP
		[ "${_cmbStatus}" == 'disabled' ] && dittodata --disable --name="${_job}"		# if the job was going to start out as disabled, then do so now
		echo "<s><data name=\"${_job//&/&amp;}\" /></s>"

	elif [ "$_A" == 'rename' ]; then
		if ( dittodata --rename="$_job" --name="$_new" >>"${DIRLOGS}/${SYSLOG}" ); then
			echo "<s><data name=\"${_new//&/&amp;}\" /></s>"
		else
			echo "<f></f>"
		fi

	elif [ "$_A" == 'copy' ]; then
		if ( dittodata --copy="$_job" --name="$_new" >>"${DIRLOGS}/${SYSLOG}" ); then
			echo "<s><data name=\"${_new//&/&amp;}\" /></s>"
		else
			echo "<f></f>"
		fi

	elif [ "$_A" == 'delete' ]; then
		if ( dittodata --delete --name="$_job" >>"${DIRLOGS}/${SYSLOG}" ); then
			echo "<s></s>"
		else
			echo "<f></f>"
		fi

	elif [ "$_A" == 'run' ]; then
		RJN=$(( $RANDOM + 2 + $RANDOM ))

		# NOTE: we use 'awk' instead of 'sed' due to sed inverting the progress meter and the 'Now backing up...' (or whatever) message
		# https://serverfault.com/questions/72744/command-to-prepend-string-to-each-line
		echo "#!/bin/sh" >"${DIRTEMP}/run.sh"				# create a script so we can use the 'at' command to start the backup job
		echo "exec backup --verbose --delay=5 --name=\"${_job}\" 2>&1 | awk '{print \"${RJN}|\" \$0}' >\"${DIRTEMP}/gui\"" >>"${DIRTEMP}/run.sh"
		chmod 750 "${DIRTEMP}/run.sh"

		at now -f "${DIRTEMP}/run.sh" >/dev/null 2>&1			# attempted to use 'setsid' and 'nohup', but they hung the reply back to the browser
		sleep 2								# sleep for a few seconds to let the backup get underway before attempting to find the one that we're looking for (if we have multiple backups running at once)
		rm -f "${DIRTEMP}/run.sh"					# delete the script now that we've executed it and no longer need it
		PROC="$(ps x -o pid,tty,command 2>>"${DIRLOGS}/${SYSLOG}" | grep [b]ackup | grep " --name=${_job}" | head -1)"	# NOTE: do NOT quote ${_job}!!!	https://unix.stackexchange.com/questions/264522/how-can-i-show-a-terminal-shells-process-tree-including-children
		PROC=${PROC%% \? *}						# remove the 'command' to isolate the 'pid' of the backup script
		PROC=${PROC##* }						# remove any leading spaces from the PID number
		TEMP=$(ps -p $PROC h -o ppid 2>>"${DIRLOGS}/${SYSLOG}")		# get the parent PID for testing below
		TEMP=${TEMP##* }						# remove any leading spaces from the PID number

		echo "$PROC" >"${DIRTEMP}/${RJN}"				# store the job pid in a file (so we can load all currently running jobs the 'Status' page if they are running)
		[ "$TEMP" != '' ] && echo "<s><data rjn='${RJN}' pid='${PROC}' ppid='${TEMP}' date='$(date)' /></s>" || echo "<f></f>"
	fi
	exit 0


elif [ "$_T" == 'keys' ]; then
	if [ "$_A" == 'exchange' ]; then
		if ( dittodata --exchange="$_device" --targetParm="$_params" --targetUser="$_user" --targetPass="$_pass" >>"${DIRLOGS}/${SYSLOG}" ); then
			echo "<s></s>"
		else
			echo "<f></f>"
		fi
	fi
	exit 0


else
	echo "<f><msg>The action passed is invalid.</msg></f>";
	exit 1
fi

