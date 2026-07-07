<?php
# _comments.php	provides the relevant page IO
#
# Created	2020/08/19 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
# Updated	2020/08/25 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
#
# Unless a valid Cliquesoft Private License (CPLv1) has been purchased for your
# device, this software is licensed under the Cliquesoft Public License (CPLv2)
# as found on the Cliquesoft website at www.cliquesoft.org.
#
# This program is distributed in the hope that it will be useful, but WITHOUT
# ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
# FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.
#
# Requires the following database table:
#
#	id		BIGINT, AUTO-INCREMENT	The unique id of each comment
#	post_id		BIGINT			The foreign key of the post id (e.g. product/service that the comment will be associated with)
#	customer_id	BIGINT			The foreign key of the customer account who made the comment
#	title		VARCHAR(64)		The comment title
#	comment		TEXT			The comment itself
#	createdBy	BIGINT			The foreign key of the user who created the comment (usually the same as 'post_id')
#	createdOn	DATETIME		The epoch when the comment was made


# Constant Definitions
define("MODULE",'DFW');						# the name of this module (NOTE: this can be the same as the PROJECT constant in the envars.php file)
define("SCRIPT",basename($_SERVER['SCRIPT_NAME']));		# the name of this script (for tracing bugs and automated messages)

# Module Requirements						  NOTE: MUST come below Module Constant Definitions
require_once('../../sqlaccess');
require_once('../data/config.php');
if (file_exists('../data/config.'.strtolower(MODULE).'.php')) { require_once('../data/config.'.strtolower(MODULE).'.php'); }
require_once('_mimemail.php');
require_once('_global.php');

# Start or resume the PHP session				  NOTE: gains access to $_SESSION variables in this script
session_start();




# format the dates in UTC
$_ = gmdate("Y-m-d H:i:s",time());				# used this mannor so all the times will be the exact same (also see http://php.net/manual/en/function.gmdate.php)


function genRandom($length = 40)				# generates random string (40 chars) for the login SID
    {return substr(sha1(rand()), 0, $length);}			# also see http://stackoverflow.com/questions/853813/how-to-create-a-random-string-using-php




# define general info for any error generated below
$gbl_info['name'] = 'Unknown';
$gbl_info['contact'] = 'Unknown';
$gbl_info['other'] = 'n/a';




# create the header for any processing below...
if ($_POST['action'] != '') {
	header('Content-Type: text/xml; charset=utf-8');
	echo "<?xml version='1.0' encoding='UTF-8'?>\n\n";
}




if ($_POST['action'] == 'load' && $_POST['target'] == 'comments') {
	if ($_POST['username'] == '') {
		echo "<s><xml></xml></s>";
		exit();
	}

	# validate all submitted data
	if (! validate($_POST['SID'],40,'[^a-zA-Z0-9]')) { exit(); }
	if (! validate($_POST['username'],64,'[^a-zA-Z0-9@\._\-]')) { exit(); }
	# -----
	if (! validate($_POST['nAccountNo'],20,'[^0-9]')) { exit(); }
	if (! validate($_POST['nPostNo'],20,'[^0-9]')) { exit(); }

	# obtain the employee information of the person WHO SAVED THE RECORD
	if (USERS == '')					#    IF we need to access the native application DB table, then...
		{ if (! loadUser(TIMEOUT,'gbl_user','ro','*',PREFIX.'Employees','username','s|'.$_POST['username'],'sid','s|'.$_POST['SID'])) {exit();} }
	else							#    OTHERWISE, we have mapped DB values, so pull the values from that table
		{ if (! loadUser(TIMEOUT,'gbl_user','ro','*',PREFIX.USERS,USERNAME,'s|'.$_POST['username'],SES,'s|'.$_POST['SID'])) {exit();} }


	# 1. Obtain all the deals matching the search criteria
	$gbl_errs['error'] = "Failed to find the associated comments in the database when loading the deal.";
	$gbl_info['command'] = "SELECT id,title,comment,createdOn FROM ".PREFIX."Comments WHERE post_id=?";
	$gbl_info['values'] = '[s] '.$_POST['nPostNo'];
	$Comment = $linkDB->prepare($gbl_info['command']);
#$error = $linkDB->errno . ' ' . $linkDB->error;
#file_put_contents('debug.txt', $error."\n", FILE_APPEND);
	$Comment->bind_param('s', $_POST['nPostNo']);
	$Comment->execute();
	$Comment = $Comment->get_result();


	$XML =	"<s>\n" .
		"   <xml>\n";
	while ($comment = $Comment->fetch_assoc())
		{ $XML .= "	<comment id='".$comment['id']."' date='".$comment['createdOn']."' title=\"".safeXML($comment['title'])."\">".safeXML($comment['comment'])."</comment>"; }
	$XML .=	"   </xml>\n" .
		"</s>";
	echo $XML;
	exit();


} else if ($_POST['action'] == 'save' && $_POST['target'] == 'comment') {
	if ($_POST['username'] == '') {
		echo "<s><xml></xml></s>";
		exit();
	}

	# validate all submitted data
	if (! validate($_POST['SID'],40,'[^a-zA-Z0-9]')) { exit(); }
	if (! validate($_POST['username'],64,'[^a-zA-Z0-9@\._\-]')) { exit(); }
	# -----
	if (! validate($_POST['nAccountNo'],20,'[^0-9]')) { exit(); }
	if (! validate($_POST['nPostNo'],20,'[^0-9]')) { exit(); }
	if (! validate($_POST['sTitle'],64,'![=<>;]')) { exit(); }
	if (! validate($_POST['sComment'],3076,'![=<>;]')) { exit(); }

	# obtain the employee information of the person WHO SAVED THE RECORD
	if (USERS == '')					#    IF we need to access the native application DB table, then...
		{ if (! loadUser(TIMEOUT,'gbl_user','rw','*',PREFIX.'Employees','username','s|'.$_POST['username'],'sid','s|'.$_POST['SID'],"\$gbl_user['status']!='active'|\$gbl_user['disabled']==1","Your account is currently not active. If you just created it, please check your email address to complete this process.|Your account is currently disabled. If you just created it, please check your email address to complete this process, otherwise please reach out to our staff for assistence.")) {exit();} }
	else							#    OTHERWISE, we have mapped DB values, so pull the values from that table
		{ if (! loadUser(TIMEOUT,'gbl_user','rw','*',PREFIX.USERS,USERNAME,'s|'.$_POST['username'],SES,'s|'.$_POST['SID'],"\$gbl_user['status']!='active'|\$gbl_user['disabled']==1","Your account is currently not active. If you just created it, please check your email address to complete this process.|Your account is currently disabled. If you just created it, please check your email address to complete this process, otherwise please reach out to our staff for assistence.")) {exit();} }


	# 1. check that there is not a comment already associated with their account (e.g. more than one comment per deal from the user)
	$gbl_errs['error'] = "Failed to find an existing saved comment in the database.";
# LEFT OFF - get the below working instead of the simply 'query()' call (for security)
#	$gbl_info['command'] = "SELECT id FROM ".PREFIX."Cities WHERE deals_id='".$_POST['nDealNo']."' AND city=\"".$city."\" LIMIT 1";
#	$gbl_info['values'] = '[i] '.$_POST['nDealNo'].', [s] '.$city;
#	$Cities = $linkDB->prepare($gbl_info['command']);
#	$Cities->bind_param('is', $_POST['nDealNo'], $city);
#	$Cities->execute();
	$gbl_info['command'] = "SELECT id FROM ".PREFIX."Comments WHERE post_id='".$_POST['nPostNo']."' AND customer_id=\"".$_POST['nAccountNo']."\" LIMIT 1";
	$gbl_info['values'] = 'None';
	$Second = $linkDB->query($gbl_info['command']);
	if ($Second->num_rows !== 0) {					# if any matching prior deals have been saved, then...
		echo "<s><msg>You have already left a comment with this item.</msg></s>";
		exit();
	}

	# 2. check that the comment is not being made by the vendor themselves (e.g. to make themselves sound good)
	$gbl_errs['error'] = "Failed to check if the comment is being made by the vendor.";
# LEFT OFF - get the below working instead of the simply 'query()' call (for security)
#	$gbl_info['command'] = "SELECT id FROM ".PREFIX."Cities WHERE deals_id='".$_POST['nDealNo']."' AND city=\"".$city."\" LIMIT 1";
#	$gbl_info['values'] = '[i] '.$_POST['nDealNo'].', [s] '.$city;
#	$Cities = $linkDB->prepare($gbl_info['command']);
#	$Cities->bind_param('is', $_POST['nDealNo'], $city);
#	$Cities->execute();
	$gbl_info['command'] = "SELECT id FROM ".PREFIX."Deals WHERE id='".$_POST['nPostNo']."' AND ca_id=\"".$_POST['nAccountNo']."\" LIMIT 1";
	$gbl_info['values'] = 'None';
	$Self = $linkDB->query($gbl_info['command']);
	if ($Self->num_rows !== 0) {					# if any matching prior deals have been saved, then...
		echo "<s><msg>You can not leave a comment on your own item.</msg></s>";
		exit();
	}

	# 3. add the deal to the database
	$gbl_errs['error'] = "Failed to create a new comment in the database.";
	$gbl_info['command'] = "INSERT INTO ".PREFIX."Comments (post_id,customer_id,title,comment,createdBy,createdOn) VALUES (?,?,?,?,'".$gbl_user['id']."','".$_."')";
	$gbl_info['values'] = '[i] '.$_POST['nPostNo'].', [i] '.$_POST['nAccountNo'].', [s] '.$_POST['sTitle'].', [s] '.$_POST['sComment'];
	$stmt = $linkDB->prepare($gbl_info['command']);
	$stmt->bind_param('iiss', $_POST['nPostNo'], $_POST['nAccountNo'], $_POST['sTitle'], $_POST['sComment']);
	$stmt->execute();

	echo "<s><msg>The comment has been added to the item successfully!</msg></s>";
	exit();



} else {					// otherwise, something malicious appears to be happening
	echo "<f><msg>An invalid request has occurred, our staff has been notified.</msg></f>";
	sendMail($gbl_emailCrackers,$gbl_nameCrackers,$gbl_emailNoReply,SCRIPT.' script','*** Possible Cracking Attempt ***',"<html>\n<body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' offset='0' bgcolor='#ffffff'>\n<table width='100%'>\n<tr>\n<td>&nbsp;</td>\n<td width='500'>\n<img src='".$gbl_uriProject."/themes/default/images/default/webbooks.email_alert.png' border='0' style='float:right; padding-left: 5px;' />\n<h1 style='padding: 50px 0 10px 0; font-size: 32px; font-variant: small-caps; color: #92bfe5;'>".PROJECT."</h1><br />\n<h2 style='margin-bottom: 5px; font: 12pt verdana bold; color: #808080;'>Possible Cracking Attempt</h2><br />\n<p style='font: 12px/17px verdana; color: #808080; text-align: justify;'><br />\nTeam,<br />\n<br />\nOne of the users was interacting with our '".PROJECT."' project, but encountered the listed error in the process of doing so.  Please investigate and correct this problem as soon as possible.  If the problem warrants contacting the end user, please do so as well by referencing the relevant information below:<br />\n<br />\n<br />\nProject: ".PROJECT."<br />\nModule: ".MODULE."<br />\nScript: ".SCRIPT."<br />\n<br />\nDB Host: ".DBHOST."<br />\nDB Name: ".DBNAME."<br />\nDB Prefix: ".PREFIX."<br />\n<br />\nOur Error: A user is attempting to pass an invalid 'action' or 'target' values.<br />\n<br />\nVar Dump:<br />\n".print_r($_GET, true)."<br />\n".print_r($_POST, true)."<br />\n</p>\n</td>\n<td>&nbsp;</td>\n</tr>\n<table>\n</body>\n</html>");

}
?>
