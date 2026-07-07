#!/bin/sh
# data.sh	functions pertaining to the 'Data' tab.
#
# created	2022/05/05 by Dave Henderson (support@cliquesoft.org)
# updated	2022/05/10 by Dave Henderson (support@cliquesoft.org)
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
#	tr		busybox/coreutils
#
# NOTES




# variable declarations
[ -e /etc/dittodata/global.cfg ] && . /etc/dittodata/global.cfg
TEMP=''
DATETIME="$(date +"%Y-%m-%d %H:%M:%S")"				# sets a default epoch so in the event of failure, a full backup should be performed via this date for safety
SHOST=''
SLOGIN=''
SSETSID=''
SOURCESSH=''




# the below line generates the variables used below (e.g. $_p, $_t, etc)
eval $(./urlgetopt -p _ -l "$QUERY_STRING")




# do some work

echo -e "Content-type: text/xml\n\n"

if [ "$_T" == 'data' ]; then
	# read in the job variable values
	[ -e "${DIRCONF}/${_job}.cfg" ] && . "${DIRCONF}/${_job}.cfg"
	[ -e "${DIRHOME}/${_job}.cfg" ] && . "${DIRHOME}/${_job}.cfg"

	if [ "$_A" == 'save' ]; then
		# write the values to disk
		if [ "$INCLUDE" != '' ]; then
			echo "$_cmbIncludeList" | tr '|' '\n' >"${INCLUDE}" 2>>"${DIRLOGS}/${SYSLOG}" || {
				echo -n "${DATETIME}	" >>"${DIRLOGS}/${SYSLOG}"
				echo "ERROR: The included data can NOT be written to disk, exiting. [UI]" >>"${DIRLOGS}/${SYSLOG}"
				echo "Calling: echo \"$_cmbIncludeList\" | tr '|' '\n' >\"${INCLUDE}\"" >>"${DIRLOGS}/${SYSLOG}"
				echo "<f></f>"
				exit 1
			}
		fi
		if [ "$EXCLUDE" != '' ]; then
			echo "$_cmbExcludeList" | tr '|' '\n' >"${EXCLUDE}" 2>>"${DIRLOGS}/${SYSLOG}" || {
				echo -n "${DATETIME}	" >>"${DIRLOGS}/${SYSLOG}"
				echo "ERROR: The excluded data can NOT be written to disk, exiting. [UI]" >>"${DIRLOGS}/${SYSLOG}"
				echo "Calling: echo \"$_cmbExcludeList\" | tr '|' '\n' >\"${EXCLUDE}\"" >>"${DIRLOGS}/${SYSLOG}"
				echo "<f></f>"
				exit 1
			}
		fi

		echo "<s></s>"

	elif [ "$_A" == 'load' ]; then
		echo "<s>"
		echo "   <xml>"

		if [ "${INCLUDE}" != '' ]; then					# if an 'include' file exists, then parse it!
			while read LINE; do
				echo "	<i>$LINE</i>"
			done <"${INCLUDE}"
		else
			echo "	<i>.</i>"
		fi
		if [ "${EXCLUDE}" != '' ]; then					# same for the 'exclude' file!
			while read LINE; do
				echo "	<e>$LINE</e>"
			done <"${EXCLUDE}"
		fi

		echo "   </xml>"
		echo "</s>"
	fi
fi

