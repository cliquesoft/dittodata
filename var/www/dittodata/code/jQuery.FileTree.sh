#!/bin/sh
# jQuery.FileTree.sh	the server-side processor of the jQuery.FileTree.js
#
# created	2012/05/25 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
# updated	2022/05/09 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
#
# Unless a valid Cliquesoft Private License (CPLv1) has been purchased for your
# device, this software is licensed under the Cliquesoft Public License (CPLv2)
# as found on the Cliquesoft website at www.cliquesoft.org.
#
# This program is distributed in the hope that it will be useful, but WITHOUT
# ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
# FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.
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

if [ "$_dir" ]; then								# if we have a fileTree to fill (and the directory to use exists), then...
	_dir=$(echo "$_dir" | ./urldecode)					# used to decode network shares since 'urlgetopt' does not seem to decode
	_job=$(echo "$_job" | ./urldecode)

	[ -e "${DIRCONF}/${_job}.cfg" ] && . "${DIRCONF}/${_job}.cfg"
	[ -e "${DIRHOME}/${_job}.cfg" ] && . "${DIRHOME}/${_job}.cfg"

	echo -e "Content-Type: text/html; charset=UTF-8\n\n"
	echo "<ul class='jqueryFileTree' style='display: none;'>"

	if [ ! -e "${DIRMNTS}/dittodata" ]; then
		mkdir -p "${DIRMNTS}/dittodata" 2>>"${DIRLOGS}/${SYSLOG}" >/dev/null || {
			echo -n "${DATETIME}	" >>"${DIRLOGS}/${SYSLOG}"
			echo "ERROR: The job --source mount point directory can NOT be created, exiting. [UI]" >>"${DIRLOGS}/${SYSLOG}"
			echo "   <li class='fail'>An error was encountered while loading this data. Check the logs for details.</li>"
			exit 1
		}
	fi

	# if we're working with a SOURCE that needs mounting, then...
	if [ "${SOURCE:0:2}" == '//' ]; then				# if we're mounting a Linux NFS share, then...
		# Make variable alterations
		[ "${SOURCEPARM}" != '' ] && {
			[ "${SOURCEPARM:0:1}" == '-' ] && TEMP=' ' || TEMP=','
			SOURCEPARM="${TEMP}${SOURCEPARM}"							# if we've made it here and this variable has a value, prepend a ',' so the mount syntax below works correctly
		}
		[ "${SOURCEUSER}" != '' ] && SOURCEPARM="${SOURCEPARM} -O user=${SOURCEUSER}"			# same with a passed username	https://unix.stackexchange.com/questions/341854/failed-to-pass-credentials-to-nfs-mount
		[ "${SOURCEPASS}" != '' ] && SOURCEPARM="${SOURCEPARM} -O pass='${SOURCEPASS}'"			# same with a passed password

		# if we don't have this SOURCE already mounted, but some other SOURCE mounted, then umount first
		if ( ! mount | grep -q "${SOURCE:2} on ${DIRMNTS}/dittodata type ") && ( mount | grep -q " on ${DIRMNTS}/dittodata type " ); then
			eval ${SUDO}umount -f "${DIRMNTS}/dittodata" 2>>"${DIRLOGS}/${SYSLOG}" >/dev/null || {
				echo -n "${DATETIME}	" >>"${DIRLOGS}/${SYSLOG}"
				echo "ERROR: The existing mounted share can NOT be unmounted, exiting. [UI]" >>"${DIRLOGS}/${SYSLOG}"
				echo "Calling: ${SUDO}umount -f \"${DIRMNTS}/dittodata\"" >>"${DIRLOGS}/${SYSLOG}"					# ' < this character is to stop the incorrect text coloring and can be ignored as an actual comment
				echo "   <li class='fail'>An error was encountered while loading this data. Check the logs for details.</li>"
				exit 1
			}
		fi
		# if we don't have this SOURCE already mounted, then mount it now
		if ( ! mount | grep -q "${SOURCE:2} on ${DIRMNTS}/dittodata type "); then
			eval "${SUDO}mount -t nfs -o ro${SOURCEPARM} \"${SOURCE:2}\" \"${DIRMNTS}/dittodata\"" 2>>"${DIRLOGS}/${SYSLOG}" >/dev/null || {							# https://linuxize.com/post/how-to-mount-an-nfs-share-in-linux/
				echo -n "${DATETIME}	" >>"${DIRLOGS}/${SYSLOG}"
				echo "ERROR: The NFS job --source can NOT be mounted, exiting. [UI]" >>"${DIRLOGS}/${SYSLOG}"
				echo "Calling: ${SUDO}mount -t nfs -o ro$(echo "${SOURCEPARM}"|sed "s/-O pass='[^']*'/-O pass='********'/") \"${SOURCE:2}\" \"${DIRMNTS}/dittodata\"" >>"${DIRLOGS}/${SYSLOG}"	# ' < this character is to stop the incorrect text coloring and can be ignored as an actual comment
				echo "   <li class='fail'>An error was encountered while loading this data. Check the logs for details.</li>"
				exit 1
			}
		fi
		_dir="${DIRMNTS}/dittodata${_dir}"
	elif [ "${SOURCE:0:2}" == '\\' ]; then
		# Make variable alterations
		TEMP=''
		[ "${SOURCEUSER}" != '' ] && TEMP="${TEMP},user=${SOURCEUSER}"
		[ "${SOURCEPASS}" != '' ] && TEMP="${TEMP},pass='${SOURCEPASS}'"
		[ "${SOURCEDOMN}" != '' ] && TEMP="${TEMP},dom=${SOURCEDOMN}"
		if [ "${SOURCEPARM}" != '' ]; then
			[ "${SOURCEPARM:0:1}" == '-' ] && TEMP="${TEMP} ${SOURCEPARM}" || TEMP="${TEMP},${SOURCEPARM}"
		fi
		SOURCEPARM="$TEMP"

		# if we don't have this SOURCE already mounted, but some other SOURCE mounted, then umount first
		if ( ! mount | grep -q "${SOURCE} on ${DIRMNTS}/dittodata type ") && ( mount | grep -q " on ${DIRMNTS}/dittodata type " ); then
			eval ${SUDO}umount -f "${DIRMNTS}/dittodata" 2>>"${DIRLOGS}/${SYSLOG}" >/dev/null || {
				echo -n "${DATETIME}	" >>"${DIRLOGS}/${SYSLOG}"
				echo "ERROR: The existing mounted share can NOT be unmounted, exiting. [UI]" >>"${DIRLOGS}/${SYSLOG}"
				echo "Calling: ${SUDO}umount -f \"${DIRMNTS}/dittodata\"" >>"${DIRLOGS}/${SYSLOG}"					# ' < this character is to stop the incorrect text coloring and can be ignored as an actual comment
				echo "   <li class='fail'>An error was encountered while loading this data. Check the logs for details.</li>"
				exit 1
			}
		fi
		# if we don't have this SOURCE already mounted, then mount it now
		if ( ! mount | grep -q "${SOURCE} on ${DIRMNTS}/dittodata type "); then
			SOURCE="$(echo ${SOURCE} | tr '\\' '/')"			# NOTE: we MUST replace the '\' characters with '/' characters in the SOURCE to work correctly
			eval "${SUDO}mount -t cifs -o ro,uid=$(id -n -u),gid=$(id -n -g)${SOURCEPARM} \"${SOURCE}\" \"${DIRMNTS}/dittodata\"" 2>>"${DIRLOGS}/${SYSLOG}" >/dev/null || {				# https://linuxize.com/post/how-to-mount-cifs-windows-share-on-linux/
				echo -n "${DATETIME}	" >>"${DIRLOGS}/${SYSLOG}"
				echo "ERROR: The CIFS job --source can NOT be mounted, exiting. [UI]" >>"${DIRLOGS}/${SYSLOG}"
				echo "Calling: ${SUDO}mount -t cifs -o ro,uid=$(id -n -u),gid=$(id -n -g)$(echo "${SOURCEPARM}"|sed "s/,pass='[^']*'/,pass='********'/") \"${SOURCE}\" \"${DIRMNTS}/dittodata\"" >>"${DIRLOGS}/${SYSLOG}"	# ' < this character is to stop the incorrect text coloring and can be ignored as an actual comment
				echo "   <li class='fail'>An error was encountered while loading this data. Check the logs for details.</li>"
				exit 1
			}
		fi
		_dir="${DIRMNTS}/dittodata${_dir}"
	elif [ "${SOURCE:0:1}" == '%' ]; then
		# Make variable alterations
		SHOST=$(echo "$SOURCE"|sed 's/^%//;s/:.*//')
		SLOGIN="@${SOURCE:1}"						# NOTE: we MUST replace the preceeding '%' character with an '@' character in the SOURCE to work correctly
		[ "${SOURCEUSER}" != '' ] && SLOGIN="${SOURCEUSER}${SLOGIN}" || SLOGIN="${USER}${SLOGIN}"					# add the correct username to connect to the remote device with to the SOURCE value

		# WARNING: the below lines MUST come in the order they currently are
		[ "${SOURCEPARM:0:1}" == '-' ] && TEMP=' ' || TEMP=','
		[ -e "${DIRSSH}/${SHOST}" ] && {
			SOURCEPARM="IdentityFile=\"${DIRSSH}/${SHOST}\"${TEMP}${SOURCEPARM}"
			SSETSID='setsid '
		}
		[ "${SOURCEPARM:0:1}" == '-' ] && TEMP=' ' || TEMP=','
		[ "${SOURCEPARM}" == '' ] && TEMP=''								# if the value is blank, then ultimately do nothing
		[ "${SOURCEUSER}" != '' ] && SOURCEPARM="allow_other,uid=$(id -u ${SOURCEUSER}),gid=$(id -g)${TEMP}${SOURCEPARM}" || SOURCEPARM="allow_other,uid=$(id -u),gid=$(id -g)${TEMP}${SOURCEPARM}"
		[ "${SOURCEPARM:0:1}" == '-' ] && TEMP=' ' || TEMP=','
		[ "${SOURCEPARM}" != '' ] && [ "${SOURCEPARM:0:1}" != ',' ] && SOURCEPARM="${TEMP}${SOURCEPARM}"
		[ "${SOURCEPARM}" == '' ] && TEMP=',' || TEMP=' -o '						# since this is post-pended, always make it the LAST parameter called (with it's own '-o')
		[ "${SOURCEPASS}" != '' ] && {
			SOURCESSH="echo '${SOURCEPASS}' | ${SUDO}sshfs"
			SOURCEPARM="${SOURCEPARM}${TEMP}password_stdin"						# https://unix.stackexchange.com/questions/337465/username-and-password-in-command-line-with-sshfs
		}

		# if we don't have this SOURCE already mounted, but some other SOURCE mounted, then umount first
		if ( ! mount | grep -q "${SLOGIN} on ${DIRMNTS}/dittodata type ") && ( mount | grep -q " on ${DIRMNTS}/dittodata type " ); then
			eval ${SUDO}umount -f "${DIRMNTS}/dittodata" 2>>"${DIRLOGS}/${SYSLOG}" >/dev/null || {
				echo -n "${DATETIME}	" >>"${DIRLOGS}/${SYSLOG}"
				echo "ERROR: The existing mounted share can NOT be unmounted, exiting. [UI]" >>"${DIRLOGS}/${SYSLOG}"
				echo "Calling: ${SUDO}umount -f \"${DIRMNTS}/dittodata\"" >>"${DIRLOGS}/${SYSLOG}"					# ' < this character is to stop the incorrect text coloring and can be ignored as an actual comment
				echo "   <li class='fail'>An error was encountered while loading this data. Check the logs for details.</li>"
				exit 1
			}
		fi
		# if we don't have this SOURCE already mounted, then mount it now
		if ( ! mount | grep -q "${SLOGIN} on ${DIRMNTS}/dittodata type "); then
			eval "${SOURCESSH} \"${SLOGIN}\" \"${DIRMNTS}/dittodata\" -o ro${SOURCEPARM}" 2>>"${DIRLOGS}/${SYSLOG}" >/dev/null || {				# NOTE: this is after the RSA keys have already been exchanged with the server	www.beginninglinux.com/home/server-administration/openssh-keys-certificates-authentication-pem-pub-crt
				echo -n "${DATETIME}	" >>"${DIRLOGS}/${SYSLOG}"
				echo "ERROR: The SSHFS job --source can NOT be mounted, exiting. [UI]" >>"${DIRLOGS}/${SYSLOG}"
				echo "Calling: $(echo "${SOURCESSH}"|sed "s/'[^']*'/'********'/") \"${SLOGIN}\" \"${DIRMNTS}/dittodata\" -o ro${SOURCEPARM}" >>"${DIRLOGS}/${SYSLOG}"				# ' < this character is to stop the incorrect text coloring and can be ignored as an actual comment
				echo "   <li class='fail'>An error was encountered while loading this data. Check the logs for details.</li>"
				exit 1
			}
		fi
		_dir="${DIRMNTS}/dittodata${_dir}"
	elif [ "${SOURCE:0:1}" == '@' ]; then
		SHOST=$(echo "$SOURCE"|sed 's/^@//;s/:.*//')
		[ "${SOURCEUSER}" != '' ] && SLOGIN="${SOURCEUSER}${SOURCE}" || SLOGIN="${USER}${SOURCE}"	# add the correct username to connect to the remote device with to the SOURCE value

		[ "${SOURCEPARM:0:1}" == '-' ] && TEMP=' ' || TEMP=','
		[ -e "${DIRSSH}/${SHOST}" ] && {
			SOURCEPARM="-o IdentityFile=\"${DIRSSH}/${SHOST}\"${TEMP}${SOURCEPARM}"
			SSETSID='setsid '
		}

		# add the ssh syntax to the command
		if [ "$TYPE" != 'sync' ]; then
			TEMP=''
			SOURCESSH="${SSETSID}ssh ${SLOGIN%%:*} ${SOURCEPARM}"
		else
			TEMP='| '										# this is show we can split the command into two if we are sync'ing
			SOURCESSH="-e \"${SSETSID}ssh ${SOURCEPARM}\" \"${SLOGIN}\""
		fi
		# setup password-less processing if no RSA keys have been exchanged
		if [ ! -e "${DIRSSH}/${SHOST}" ]; then								# if there isn't an IdentityFile for the connection to prevent password prompts, then...
			if [ "$(which sshpass)" != '' ]; then							# if 'sshpass' exists, then...
				SOURCESSH="sshpass -f \"${DIRTEMP}/dittodata_s.pw\" ${TEMP}${SOURCESSH}"	# setup the syntax to use sshpass
				echo "${SOURCEPASS}" >"${DIRTEMP}/dittodata_s.pw"				# create the password file
			else
				export DISPLAY=none:0.0								# export these variables so when 'setsid ssh ...' is called it can use the script to get the password
				export SSH_ASKPASS="${DIRTEMP}/dittodata_s.sh"
				echo '#!/bin/sh' >"${DIRTEMP}/dittodata_s.sh"					# now create the script to give the password to ssh
				echo "echo ${SOURCEPASS}" >>"${DIRTEMP}/dittodata_s.sh"
				chmod 700 "${DIRTEMP}/dittodata_s.sh"
			fi
		fi
	else
		# if we don't have a SOURCE already mounted, then umount first
		if ( mount | grep -q " on ${DIRMNTS}/dittodata type " ); then
			umount -f "${DIRMNTS}/dittodata" 2>>"${DIRLOGS}/${SYSLOG}" >/dev/null || {
				# if we can't unmount without sudo, then try it with sudo
				sudo umount -f "${DIRMNTS}/dittodata" 2>>"${DIRLOGS}/${SYSLOG}" >/dev/null || {
					echo -n "${DATETIME}	" >>"${DIRLOGS}/${SYSLOG}"
					echo "ERROR: The existing mounted share can NOT be unmounted, exiting. [UI]" >>"${DIRLOGS}/${SYSLOG}"
					echo "Calling: ${SUDO}umount -f \"${DIRMNTS}/dittodata\"" >>"${DIRLOGS}/${SYSLOG}"					# ' < this character is to stop the incorrect text coloring and can be ignored as an actual comment
					echo "   <li class='fail'>An error was encountered while loading this data. Check the logs for details.</li>"
					exit 1
				}
			}
		fi
		# store the local directory
		_dir="${SOURCE}${_dir}"
	fi


	IFS=$'\n'
	if [ "${SOURCE:0:1}" == '@' ]; then
		# if we are initializing the divFolder contents, then isolate just the directory from the entire string (otherwise, use the existing value of _dir)
		[ "$_dir" == '/' ] && _dir="$(echo "$SOURCE"|sed 's/.*://')"

		# WARNING: - we can ONLY do 'timeout' calls for a connection, NOT TRANSFERS since the timeout call would abrupt the complete transfer
		TEMP="$(eval "timeout --preserve-status -k 10s --foreground 90s ${SOURCESSH} \"[ -e \\\"${_dir}\\\" ] && ls -1F \\\"${_dir}\\\"\"" 2>>"${DIRLOGS}/${SYSLOG}")"

		# NOTE: this will catch any failure with either of the ssh calls above!
		[ $? -ne 0 ] && {
			echo -n "${DATETIME}	" >>"${DIRLOGS}/${SYSLOG}"
			echo "ERROR: The directory listing can NOT be obtained, exiting. [UI]" >>"${DIRLOGS}/${SYSLOG}"
			echo "Calling: ${SOURCESSH} \"[ -e \\\"${_dir}\\\" ] && ls -1F \\\"${_dir}\\\"\"" >>"${DIRLOGS}/${SYSLOG}"				# " < this character is to stop the incorrect text coloring and can be ignored as an actual comment
			[ -e "${DIRTEMP}/dittodata_s.pw" ] && rm -f "${DIRTEMP}/dittodata_s.pw" 2>>"${DIRLOGS}/${SYSLOG}"
			[ -e "${DIRTEMP}/dittodata_s.sh" ] && rm -f "${DIRTEMP}/dittodata_s.sh" 2>>"${DIRLOGS}/${SYSLOG}"
			exit 1
		}

		# NOTE: the below will show the directories first, then files
		for OBJ in $(echo "$TEMP" | grep '/'$ | sed 's#[\*/=@|]$##'); do
			# NOTE: we MUST include 'collapsed' since the jQuery plugin uses the class names for functionality
			echo "   <li class='folder collapsed'><a href='#' rel=\"${_dir}${OBJ}/\" class='dnd'>${OBJ}</a></li>"
		done
		for OBJ in $(echo "$TEMP" | grep -v '/'$ | sed 's#[\*/=@|]$##'); do
			echo "   <li class='file mime_${OBJ##*.}'><a href='#' rel=\"${_dir}${OBJ}\" class='dnd'>${OBJ}</a></li>"
		done
	elif [ -e "$_dir" ]; then							# IF we're dealing with an existing, local directory, so...
		# NOTE: the below will show the directories first, then files
		for OBJ in $(ls -1F "$_dir" | grep '/'$ | sed 's#[\*/=@|]$##'); do
			# NOTE: we MUST include 'collapsed' since the jQuery plugin uses the class names for functionality
			TEMP="$(echo "$_dir" | sed "s|${DIRMNTS}/dittodata||")"	# remove the mount point from the path
			echo "   <li class='folder collapsed'><a href='#' rel=\"${TEMP}${OBJ}/\" class='dnd'>${OBJ}</a></li>"
		done
		for OBJ in $(ls -1F "$_dir" | grep -v '/'$ | sed 's#[\*/=@|]$##'); do
			TEMP="$(echo "$_dir" | sed "s|${DIRMNTS}/dittodata||")"
			echo "   <li class='file mime_${OBJ##*.}'><a href='#' rel=\"${TEMP}${OBJ}\" class='dnd'>${OBJ}</a></li>"
		done
	else									# OTHERWISE we've encountered a problem with the submitted directory 
		echo "   <li class='fail'>The path \"${_dir}\" does not exist or is inaccessible.</li>"
	fi

	echo "</ul>"
	exit 0

elif [ "$_queryArchive" != '' ]; then						# USED TO FILL THE "Associated archives" LIST ON THE 'Jobs' TAB
# LEFT OFF - once the 'stat' crtime (btime) with EXT4 become more mainstream, modify the below code to obtain that date instead of the times obtained below; can't take it from the .tgz since it was created after the original tarball and the catalog is also generated later in the backup process
   IFS='
'
   echo -e "Content-type: text/xml\n\n"
   echo "<archives>"
   cd "$DIRLOGS"
   for TEMP in $(ls -1rt *[0-9].log | grep -v ^'archive' | xargs grep -l -m 1 ^"Beginning the \"$_queryArchive\" backup job" -); do echo "<a file='${TEMP%.*}'>$(date -r $TEMP)</a>"; done
   cd -
   echo "</archives>"

fi









































# do some work

if [ "$_dir" ]; then								# if we have a fileTree to fill (and the directory to use exists), then...
	_dir=$(echo "$_dir" | ./urldecode)					# used to decode network shares since 'urlgetopt' does not seem to decode
	_source=$(echo "$_source" | ./urldecode)
	_mp=$(echo "$_mp" | ./urldecode)

	echo -e "Content-Type: text/html; charset=UTF-8\n\n"
	echo "<ul class='jqueryFileTree' style='display: none;'>"

#	if ( echo "$_dir" | grep -q ^'//' ) || ( echo "$_dir" | grep -q ^'\\' ); then		# IF we're dealing with a network share, then...
# LEFT OFF - code the following upon finding a resolution to non-root mounting of network shares
#		# if [ no contents of $DIRMNTS ]; then
#			echo "<li class='divFail'>Direct viewing of a network share is unavailable.</li>"
#		# else
#			# show the shares contents
#		# fi
#	elif [ -e "$_dir" ]; then						# ELSE IF we're dealing with an existing, local directory, so...
	if [ -e "$_dir" ]; then							# IF we're dealing with an existing, local directory, so...
		IFS=$'\n'
		# NOTE: the below will show the directories first, then files
		for OBJ in $(ls -1F "$_dir" | grep '/'$ | sed "s#[\*/=@|]##;s/${_mp}//"); do
			# NOTE: we MUST include 'collapsed' since the jQuery plugin uses the class names for functionality
			echo "   <li class='folder collapsed'><a href='#' rel=\"${_dir}${OBJ}/\" class='dnd'>${OBJ}</a></li>"
		done
		for OBJ in $(/bin/ls -1F "$_dir" | grep -v '/'$ | sed "s#[\*/=@|]##;s/${_mp}//"); do
			echo "   <li class='file mime_${OBJ##*.}'><a href='#' rel=\"${_dir}${OBJ}\" class='dnd'>${OBJ}</a></li>"
		done
	else									# OTHERWISE we've encountered a problem with the submitted directory 
		echo "   <li class='divFail'>The path \"${_dir}\" does not exist or is inaccessible.</li>"
	fi

	echo "</ul>"


elif [ "$_queryArchive" != '' ]; then						# USED TO FILL THE "Associated archives" LIST ON THE 'Jobs' TAB
# LEFT OFF - once the 'stat' crtime (btime) with EXT4 become more mainstream, modify the below code to obtain that date instead of the times obtained below; can't take it from the .tgz since it was created after the original tarball and the catalog is also generated later in the backup process
   IFS='
'
   echo -e "Content-type: text/xml\n\n"
   echo "<archives>"
   cd "$DIRLOGS"
   for TEMP in $(ls -1rt *[0-9].log | grep -v ^'archive' | xargs grep -l -m 1 ^"Beginning the \"$_queryArchive\" backup job" -); do echo "<a file='${TEMP%.*}'>$(date -r $TEMP)</a>"; done
   cd -
   echo "</archives>"

fi

