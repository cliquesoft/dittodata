<?php
# default.php	the "boot strapper" for this project.
#
# created	2012/05/15 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
# updated	2019/10/15 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
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


# Module Requirements				  NOTE: MUST come below Module Constant Definitions
#require_once('data/config.php');
#require_once('data/config.MODULE.php');

#https://stackoverflow.com/questions/49547/how-do-we-control-web-page-caching-across-all-browsers
header("Cache-Control: no-cache, no-store, must-revalidate");					# HTTP 1.1
header("Pragma: no-cache");									# HTTP 1.0
header("Expires: 0");										# Proxies




if (isset($_COOKIE['username'])) { $user=$_COOKIE['username']; } else { $user='guest'; }


if( $_GET['p'] != '' && @file_exists("home/".$user."/look/".$_GET['p'])){			# IF we have a specific file to display (and it exists), then...
	header('Content-Type: text/html; charset=utf-8');

	$page = @fopen("home/".$user."/look/".$_GET['p'], "r");
	while ($LINE = fgets($page)) {
		# Psuedo snippets for dynamic content using shell-style-variables in the .html file
		if (strpos($LINE, '${UN}') !== false) { $LINE = str_replace('${UN}', $user, $LINE); }
		if (strpos($LINE, '${WIKI}') !== false) { $LINE = str_replace('${WIKI}', WIKI, $LINE); }
		echo "$LINE";
	}
	exit();

} else {											# OTHERWISE, this was being called without any specific page so load the default page...
	header('Content-Type: text/html; charset=utf-8');

	$page = @fopen("home/".$user."/look/default.html", "r");
	while ($LINE = fgets($page)) {
		# Psuedo snippets for dynamic content using shell-style-variables in the .html file
		if (strpos($LINE, '${UN}') !== false) { $LINE = str_replace('${UN}', $user, $LINE); }
		if (strpos($LINE, '${WIKI}') !== false) { $LINE = str_replace('${WIKI}', WIKI, $LINE); }
		echo "$LINE";
	}
	exit();
}
?>

