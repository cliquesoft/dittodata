<?php
# _logout.php	a standard module that provides the relevant page IO for
#		logging users out of the project.
#
# Created	2013/01/14 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
# Updated	2020/07/16 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
#
# Unless a valid Cliquesoft Private License (CPLv1) has been purchased for your
# device, this software is licensed under the Cliquesoft Public License (CPLv2)
# as found on the Cliquesoft website at www.cliquesoft.org.
#
# This program is distributed in the hope that it will be useful, but WITHOUT
# ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
# FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.
#
# NOTES:
# - the SQL calls in this script only deal with the DB table specific to Tracker
#   since there are fields in there that are adjusted below.
# - if this project is embedded in another, there shouldn't be any calls to this
#   script or the logout.php script since that would be handled by the "wrapper"
#   projects scripts.


# Constant Definitions
define("MODULE",'DFW');						# the name of this module (NOTE: this can be the same as the PROJECT constant in the envars.php file)
define("SCRIPT",basename($_SERVER['SCRIPT_NAME']));		# the name of this script (for tracing bugs and automated messages)
define("TABLE",'CustomerAccounts');				# the name of the table containing the user accounts

# Module Requirements						  NOTE: MUST come below Module Constant Definitions
require_once('../../sqlaccess');
require_once('../data/config.php');
if (file_exists('../data/config.'.strtolower(MODULE).'.php')) { require_once('../data/config.'.strtolower(MODULE).'.php'); }
require_once('_mimemail.php');
require_once('_global.php');

# Start or resume the PHP session				  NOTE: gains access to $_SESSION variables in this script
session_start();




// format the dates in UTC
$_ = gmdate("Y-m-d H:i:s",time());				# used this mannor so all the times will be the exact same (also see http://php.net/manual/en/function.gmdate.php)

header('Content-Type: text/xml; charset=utf-8');
echo "<?xml version='1.0' encoding='UTF-8'?>";




if ($_POST['action'] == 'logout' && $_POST['target'] == 'username') {		// Process the logout!
	# validate all submitted data
	if (! validate($_POST['username'],32,'[^a-zA-Z0-9@\._\-]')) { exit(); }
	if (! validate($_POST['SID'],40,'[^a-z0-9]')) { exit(); }

# REMOVED 2020/07/15 - we can allow logging in, just not accout creation
#	if (USERS != '') {			# WARNING: we do NOT add accounts to non-native Tracker DB tables - this will be the responsibility of the wrapper project!
#		echo "<f><msg>Logging out with your account in a non-native ".PROJECT." database table is not allowed. Please use the logout section of the parent project.</msg></f>";
#		exit();
#	}

#	if (! loadUser(TIMEOUT,'gbl_user','rw','id',PREFIX.'Accounts','username','s|'.$_POST['username'],'sid','s|'.$_POST['SID'])) { exit(); }
	if (! loadUser(TIMEOUT,'gbl_user','rw','id,sid',PREFIX.TABLE,'username','s|'.$_POST['username'])) { exit(); }

	# NOTE: since we are using a single SID for all logins, if the user logs out on one device then it will be on all devices - so we have to handle logouts in this fashion instead of a single loadUser() validation call
	if ($gbl_user['sid'] != '') {
		if ($gbl_user['sid'] != $_POST['SID']) {
			$gbl_errs['error'] = "The account SID and passed SID do not match.";
			$gbl_info['command'] = $gbl_user['sid']." != ".$_POST['SID'];
			$gbl_info['values'] = 'None';
			$errno = 0;
			$errstr = '';
			$errfile = SCRIPT;
			$errline = 65;
			echo "<f><msg>There was an error processing your request and our staff has been notified.  Please try again in a few minutes.</msg></f>";
			sendMail($gbl_emailCrackers,$gbl_nameCrackers,$gbl_emailNoReply,$gbl_nameNoReply,'!!! Possible Cracking Attempt !!!',"<html>\n<body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' offset='0' bgcolor='#ffffff'>\n<table width='100%'>\n<tr>\n<td>&nbsp;</td>\n<td width='500'>\n<img src='".$gbl_uriProject."/home/guest/imgs/email_error.png' border='0' style='float:right; padding-left: 5px;' />\n<h1 style='padding: 50px 0 10px 0; font-size: 32px; font-variant: small-caps; color: #92bfe5;'>".PROJECT."</h1><br />\n<h2 style='margin-bottom: 5px; font: 12pt verdana bold; color: #808080;'>Possible Cracking Attempt</h2><br />\n<p style='font: 12px/17px verdana; color: #808080; text-align: justify;'>\nTeam,<br />\n<br />\nOne of our users was attempting to interact with our site, but encountered an error which has been included below.  Please investigate and correct this problem as soon as possible.  If the problem warrants contacting the end user, please do so as well by referencing the relevant information below:<br />\n<br />\n<u>Date:</u> ".gmdate("Y-m-d H:i:s",time())." GMT<br />\n<u>From:</u> ".$_SERVER['REMOTE_ADDR']."<br />\n<br />\n<u>Project:</u> ".PROJECT."<br />\n<u>Module:</u> ".MODULE."<br />\n<u>Script:</u> ".SCRIPT."<br />\n<br />\n<u>DB Host:</u> ".DBHOST."<br />\n<u>DB Name:</u> ".DBNAME."<br />\n<u>DB Prefix:</u> ".PREFIX."<br />\n<br />\n<u>Name:</u> ".$gbl_info['name']."<br />\n<u>Contact:</u> ".$gbl_info['contact']."<br />\n<u>Other:</u> ".$gbl_info['other']."<br />\n<br />\n<u>Summary:</u> ".$gbl_errs['error']."<br />\n<u>Error:</u> (".$errno.") ".$errstr."<br />\n<u>Command:</u> ".$gbl_info['command']."<br />\n<u>Values:</u> ".$gbl_info['values']."<br />\n<u>File:</u> ".$errfile."<br />\n<u>Line:</u> ".$errline."<br />\n<br />\n<br />\nSincerely,<br />\n".PROJECT." Staff\n<br />\n<br />\nVar Dump:<br />\n</p>\n<pre>_POST\n".print_r($_POST, true)."</pre><br />\n<pre>_GET\n".print_r($_GET, true)."</pre><br />\n</td>\n<td>&nbsp;</td>\n</tr>\n</table>\n</body>\n</html>");
			exit();
		}

		$gbl_errs['error'] = "The logout could not be recorded in the database.";
		$gbl_info['command'] = "UPDATE ".PREFIX.TABLE." SET sid='',logout='".$_."' WHERE username=?";
		$gbl_info['values'] = '[s] '.$_POST['username'];
		$stmt = $linkDB->prepare($gbl_info['command']);
		$stmt->bind_param('s', $_POST['username']);
		$stmt->execute();
	}

	echo "<s><msg>You have been logged out successfully!</msg></s>";
	exit();




} else {					// otherwise, we need to indicate that an invalid request was made

	echo "<f></msg>An invalid request has occurred, our staff has been notified.</msg></f>";
	sendMail($gbl_emailHackers,$gbl_nameHackers,$gbl_emailNoReply,$gbl_nameNoReply,'*** Possible Cracking Attempt ***',"<html>\n<body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' offset='0' bgcolor='#ffffff'>\n<table width='100%'>\n<tr>\n<td>&nbsp;</td>\n<td width='500'>\n<img src='".$gbl_uriProject."/home/guest/imgs/email_error.png' border='0' style='float:right; padding-left: 5px;' />\n<h1 style='padding: 50px 0 10px 0; font-size: 32px; font-variant: small-caps; color: #92bfe5;'>".PROJECT."</h1>\n<h2 style='margin-bottom: 5px; font: 12pt verdana bold; color: #808080;'>Possible Cracking Attempt</h2>\n<p style='font: 12px/17px verdana; color: #808080; text-align: justify;'>\nTeam,<br />\n<br />\nWe might have had a possible cracking attempt made on ".$_." due to someone attempting to pass an invalid value for the 'layout' variable.  The call was made from ".$_SERVER['REMOTE_ADDR']." while trying to pass a non-associated account record value for an AJAX parameter value (id='".$_POST['id']."' AND source='".$gbl_user['id']."').  Please investigate this issue immediately!  [".SCRIPT."; body]<br />\n<br />\nSincerely,<br />\n".PROJECT." Staff\n</p>\n</td>\n<td>&nbsp;</td>\n</tr>\n<table>\n</body>\n</html>");


}
?>
