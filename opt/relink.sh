#!/bin/sh
# relink.sh	re-links the system to the dittodata install
#		if a firmware update gets installed on the
#		WD Nas.
#		NOTE: we write all the info to the external
#		drive, not the eprom (since it gets erased).

PREFIX='/DataVolume/dittodata'
DIRCONF='/etc/dittodata'
DIRCRON='/etc/cron.d'

echo -n "Symlinking config directory:"
[ ! -e "${DIRCONF}" ] && ln -s "${PREFIX}${DIRCONF}" "${DIRCONF}"
echo " [done]"

echo -n "Symlinking crontab file:"
[ ! -e "${DIRCRON}/dittodata" ] && ln -s "${PREFIX}${DIRCRON}/dittodata" "${DIRCRON}/dittodata"
echo " [done]"

echo -n "Symlinking the executables:"
for FILE in $(ls -1 "${PREFIX}/bin"); do
	[ "${FILE}" == 'relink.sh' ] && continue	# skip this file

	echo -n " [${FILE}]"
	[ ! -e "/usr/local/bin/${FILE}" ] && ln -s "${PREFIX}/bin/${FILE}" "/usr/local/bin/${FILE}" || echo -n " [exists]"
done
echo " [done]"

echo
echo "Congrats! The software is re-linked and ready for use!"
echo

