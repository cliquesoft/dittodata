#!/bin/sh
# default.sh	the "boot strapper" for this project.
#
# created	2012/05/15 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
# updated	2022/05/19 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
#
# Unless a valid Cliquesoft Proprietary License (CPLv1) has been purchased
# for this device, this software is licensed under the Cliquesoft Public
# License (CPLv2) as found on the Cliquesoft website at www.cliquesoft.org
#
# This program is distributed in the hope that it will be useful, but WITHOUT
# ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
# FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.
#
# NOTES
#  $line =~ s/&/&amp;/g;		we may need to add string modification for XML to be transmitted correctly
#
#  $filepath = "/tmpphp/dmbigmail.file";		how to securely look for the file - LEFT OFF
#  if (!file_exists($filepath) || !is_file($filepath)) {
#	echo "$filepath not found or it is not a file."; exit; //return; //die();
#  }
#  if ($file_handle = fopen($filepath, "r")) {
#  ...




# no-cache headers
HEADER=''
#HEADER="${HEADER}Cache-Control: no-cache, no-store, must-revalidate\n"				# HTTP 1.1
#HEADER="${HEADER}Pragma: no-cache\n"								# HTTP 1.0
#HEADER="${HEADER}Expires: 0\n"									# Proxies




# the below line generates the variables used below (e.g. $_p, $_t, etc)
_p=''
_USER='guest'
_WIKI=''
eval $(code/urlgetopt -p _ -l "$QUERY_STRING")
[ ! -z ${_UN+x} ] && $_USER="$_UN" || $_USER='guest'						# https://stackoverflow.com/questions/3601515/how-to-check-if-a-variable-is-set-in-bash


if [ "$_p" != '' ] && [ -e "home/${_USER}/look/${_p}" ]; then					# IF we have a specific file to display (and it exists), then...
	echo -e "Content-Type: text/html; charset=UTF-8\n${HEADER}\n"
	while read LINE; do
		# Psuedo snippets for dynamic content using shell-style-variables in the .html file
		( echo "$LINE" | grep -q '${UN}' ) && LINE=${LINE/\$\{UN\}/$_USER}
		( echo "$LINE" | grep -q '${WIKI}' ) && LINE=${LINE/\$\{WIKI\}/$_WIKI}
		echo "$LINE"
	done < "home/${_USER}/look/${_p}"
	exit 0

else												# OTHERWISE, this was being called without any specific page so load the default page...
	echo -e "Content-Type: text/html; charset=UTF-8\n${HEADER}\n"
	while read LINE; do
		# Psuedo snippets for dynamic content using shell-style-variables in the .html file
		( echo "$LINE" | grep -q '${UN}' ) && LINE=${LINE/\$\{UN\}/$_USER}
		( echo "$LINE" | grep -q '${WIKI}' ) && LINE=${LINE/\$\{WIKI\}/$_WIKI}
		echo "$LINE"
	done < "home/${_USER}/look/default.html"						# NOTE: we use the below directory since the webservers' root is /var/www/clapi
	exit 0

fi

