#!/bin/sh
# reinstall.sh	re-installs the software used by dittodata
#		if a firmware update gets installed on the
#		WD Nas.

PREFIX='/DataVolume/dittodata'
DIRLOG='/var/log'

cd "${PREFIX}/opt"
echo "Installing fuse..."
dpkg -i ../opt/libfuse2_2.8.4-1.1_powerpc.deb ../opt/fuse-utils_2.8.4-1.1_powerpc.deb 2>&1 >"${DIRLOG}/dittodata.log" || {
	echo
	echo "ERROR: The fuse package can NOT be installed, exiting."
	exit 1
}

echo
echo "Installing sshpass..."
dpkg -i ../opt/sshpass_1.01-2_powerpc.deb 2>&1 >"${DIRLOG}/dittodata.log" || {
	echo
	echo "ERROR: The sshpass package can NOT be installed, exiting."
	exit 1
}

echo
echo "Installing sshfs..."
dpkg -i ../opt/sshfs_2.1-1_powerpc.deb 2>&1 >"${DIRLOG}/dittodata.log" || {
	echo
	echo "ERROR: The sshfs package can NOT be installed, exiting."
	exit 1
}

echo
echo "Installing at scheduler..."
dpkg -i ../opt/at_3.1.10.2_powerpc.deb 2>&1 >"${DIRLOG}/dittodata.log" || {
	echo
	echo "ERROR: The at scheduler package can NOT be installed, exiting."
	exit 1
}

echo
echo "Installing pv..."
dpkg -i ../opt/pv_1.1.4-1_powerpc.deb 2>&1 >"${DIRLOG}/dittodata.log" || {
	echo
	echo "ERROR: The pv package can NOT be installed, exiting."
	exit 1
}

echo
echo "Installing numfmt..."
[ ! -e "/usr/local/bin/numfmt" ] && {
	( cp ../opt/numfmt "/usr/local/bin" 2>&1 >"${DIRLOG}/dittodata.log" ) || {
		echo
		echo "ERROR: The numfmt binary can NOT be installed, exiting."
		exit 1
	}
}

echo
echo "Congrats! Your software is installed and ready for use!"
echo

