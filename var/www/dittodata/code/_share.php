<?php
# _share.php	a standard module that provides the relevant page IO for
#		sharing this project with others.
#
# Created	2012/11/05 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
# Updated	2019/10/15 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
#
# Unless a valid Cliquesoft Private License (CPLv1) has been purchased for your
# device, this software is licensed under the Cliquesoft Public License (CPLv2)
# as found on the Cliquesoft website at www.cliquesoft.org.
#
# This program is distributed in the hope that it will be useful, but WITHOUT
# ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
# FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.
#
# ADDITIONAL:
# https://code.google.com/p/cool-php-captcha/


# Constant Definitions
define("MODULE",'tracker');					# the name of this module (NOTE: this can be the same as the PROJECT constant in the envars.php file)
define("SCRIPT",basename($_SERVER['SCRIPT_NAME']));		# the name of this script (for tracing bugs and automated messages)

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
echo "<?xml version='1.0' encoding='UTF-8'?>\n";




if ($_POST['action'] == 'send' && $_POST['target'] == 'share') {		// Send the referal!
	// make appropriate substitutions
	$_POST['txtContactMsg'] = str_replace('<','&lt;',$_POST['txtContactMsg']);
	$_POST['txtContactMsg'] = str_replace('>','&gt;',$_POST['txtContactMsg']);

	# validate all submitted data
	if (! validate($_POST['username'],32,'[^a-zA-Z0-9_\-]')) { exit(); }
	if (! validate($_POST['txtContact'],64,'[^a-zA-Z0-9_\- ]')) { exit(); }
	if (! validate($_POST['txtCaptcha'],16,'[^a-zA-Z\- ]')) { exit(); }
	if (! validate($_POST['txtContactEmail'],128,'[^a-zA-Z0-9\.@\-_]')) { exit(); }
	if (! validate($_POST['hidType'],8,'{project|product|person|website}')) { exit(); }
	if (! validate($_POST['hidCategory'],11,'{Accessories|Boards|Components|Devices|Elements}')) { exit(); }
	if ($_POST['hidType'] != 'website')
		{ if (! validate($_POST['hidName'],16,'[^a-zA-Z\. ]')) {exit();} }
	else
		{ if (! validate($_POST['hidName'],64,'[^a-zA-Z0-9\.\-_ ]')) {exit();} }
	if ($_POST['hidType'] != 'website')
		{ if (! validate($_POST['hidID'],64,'[^0-9]')) {exit();} }
	else
		{ if (! validate($_POST['hidID'],128,'[^a-zA-Z0-9\.\-_%:/]')) {exit();} }
	#if (! validate($_POST['txtContactMsg'],256,'![=<>;]')) { exit(); }	# NOTE: since this is just an email that gets sent, there is no reason to perform these checks; besides the &lt;/&gt; conversion will still retain a semicolon!

	# check the captcha is valid
	if (empty($_POST['txtCaptcha'])) {
		echo "<f><msg>You must enter the captcha text before notifying your contact.</msg></f>";
		exit();
	} else if (empty($_SESSION['captcha']) || trim(strtolower($_POST['txtCaptcha'])) != $_SESSION['captcha']) {
		echo "<f><msg>You captcha text you entered does NOT match what is found in the graphic, please try again.</msg></f>";
		exit();
	}
	unset($_SESSION['captcha']);

	if (! connect2DB(DBHOST,DBNAME,DBUNRO,DBPWRO)) { exit(); }

	# check if the referral is already a member
	if ($_POST['hidType'] == 'person') {
		$gbl_errs['error'] = "The (existing) user account can not be found in the database.";
		if (USERS == '')						# IF we need to access the native Tracker DB table, then...
			{ $gbl_info['command'] = "SELECT id FROM ".PREFIX."Accounts WHERE email=? LIMIT 1"; }
		else
			{ $gbl_info['command'] = "SELECT id FROM ".PREFIX.USERS." WHERE ".EMAIL."=? LIMIT 1"; }
		$gbl_info['values'] = '[s] '.$_POST['txtContactEmail'];
		$stmt = $linkDB->prepare($gbl_info['command']);
		$stmt->bind_param('s', $_POST['txtContactEmail']);
		$stmt->execute();
		$existing = $stmt->get_result()->fetch_assoc();
		if ($existing) {
			echo "<f><msg>Thanks for the referral, but it appears that person is already a member!</msg></f>";
			exit();
		}
	}

	# if we've made it here, we can send the referral!
	$id = 0;								# set default values
	$name = 'Undisclosed';
	$contact = 'Sir/Madam,';
	if ($_POST['txtContact'] != '') { $contact = $_POST['txtContact']; }

	if ($_POST['username'] != '_guest' && $_POST['username'] != '') {	# if the user is logged in, we can tag their account with the referral so 
		$gbl_errs['error'] = "The user account can not be found in the database.";
		if (USERS == '')						# IF we need to access the native Tracker DB table, then...
			{ $gbl_info['command'] = "SELECT id,pes,alias,first,last FROM ".PREFIX."Accounts WHERE username=? LIMIT 1"; }
		else
			{ $gbl_info['command'] = "SELECT ".UID.",".ALIAS.",".FIRST.",".LAST." FROM ".PREFIX.USERS." WHERE ".USERNAME."=? LIMIT 1"; }
		$gbl_info['values'] = '[s] '.$_POST['username'];
		$stmt = $linkDB->prepare($gbl_info['command']);
		$stmt->bind_param('s', $_POST['username']);
		$stmt->execute();
		$account = $stmt->get_result()->fetch_assoc();
		if ($account) {
			if (USERS == '') {
				# decrypt the account information
				$salt = file_get_contents('../../denaccess');		# decrypt the users 'personal encryption string' (pes)
				$account['pes'] = Cipher::decrypt($account['pes'], $salt);	# use the 'pes' to decrypt the users account fields
				if (! is_null($account['last']) && $account['last'] != '') { $account['last'] = Cipher::decrypt($account['last'], $account['pes']); }	# NOTE: the "!= ''" is for accounts with a password reset

				if ($account['first'] != '') { $name = $account['first']; }
				else if ($account['alias'] != '') { $name = $account['alias']; }

				if ($account['first'] != '' && $account['last'] != '') { $name .= " ".$account['last']; }
			} else {
				if ($account['first'] != '') { $name = $account['first']; }
				else if ($account['alias'] != '') { $name = $account['alias']; }

				if ($account['first'] != '' && $account['last'] != '') { $name .= " ".$account['last']; }
			}
			$id = $account['id'];
		}
	}

	# construct the email to send to the contact
	$mail = new mime_mail();
	$mail->from = $gbl_emailNoReply;
	$mail->cc = "";
	$mail->headers = "Errors-To: ".$gbl_emailHackers;
	$mail->to = $_POST['txtContactEmail'];
	$mail->subject = "You have been referred!";
	if ($_POST['hidType'] == 'project')
		{ $mail->body = "<html>\n<body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' offset='0' bgcolor='#ffffff'>\n<table width='100%'>\n<tr>\n<td>&nbsp;</td>\n<td width='500'>\n<img src='".$gbl_uriProject."/home/guest/imgs/email_invite.png' border='0' style='float:right; padding-left: 5px;' />\n<h1 style='padding: 50px 0 10px 0; font-size: 32px; font-variant: small-caps; color: #92bfe5;'>".PROJECT."</h1><br />\n<h2 style='margin-bottom: 5px; font: 12pt verdana bold; color: #808080;'>You have been referred!</h2>\n<p style='font: 12px/17px verdana; color: #808080; text-align: justify;'>\n".$contact.",<br />\nYou have been referred to look at our \"".$_POST['hidName']."\" project in our online market by \"".$name."\" who thinks you would be interested in it! Please feel free to visit <a href='".$gbl_uriProject."/#Shoppe=".$_POST['hidID']."' target='_new'>our site</a>, to read about this and other ".$_POST['hidType']."s. It is also important to note that our software is free of charge with optional technical support, alternative licensing, and more! If you have any questions, comments, or concerns, please contact our staff at your earliest convenience.<br />\n<br />\nSincerely,<br />\n".PROJECT." Staff\n<br /><br /><br /><br />Optional Message:<br />\n".$_POST['txtContactMsg']."</p>\n</td>\n<td>&nbsp;</td>\n</tr>\n<table>\n</body>\n</html>"; }
	else if ($_POST['hidType'] == 'product')
		{ $mail->body = "<html>\n<body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' offset='0' bgcolor='#ffffff'>\n<table width='100%'>\n<tr>\n<td>&nbsp;</td>\n<td width='500'>\n<img src='".$gbl_uriProject."/home/guest/imgs/email_invite.png' border='0' style='float:right; padding-left: 5px;' />\n<h1 style='padding: 50px 0 10px 0; font-size: 32px; font-variant: small-caps; color: #92bfe5;'>".PROJECT."</h1><br />\n<h2 style='margin-bottom: 5px; font: 12pt verdana bold; color: #808080;'>You have been referred!</h2>\n<p style='font: 12px/17px verdana; color: #808080; text-align: justify;'>\n".$contact.",<br />\nYou have been referred to look at the \"".$_POST['hidName']."\" product in our online market by \"".$name."\" who thinks you would be interested in it! Please feel free to visit <a href='".$gbl_uriProject."/#".$_POST['hidCategory']."=".$_POST['hidID']."' target='_new'>our site</a>, to read about this and other ".$_POST['hidType']."s. If you have any questions, comments, or concerns, please contact our staff at your earliest convenience.<br />\n<br />\nSincerely,<br />\n".PROJECT." Staff\n<br /><br /><br /><br />Optional Message:<br />\n".$_POST['txtContactMsg']."</p>\n</td>\n<td>&nbsp;</td>\n</tr>\n<table>\n</body>\n</html>"; }
	else if ($_POST['hidType'] == 'person')
		{ $mail->body = "<html>\n<body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' offset='0' bgcolor='#ffffff'>\n<table width='100%'>\n<tr>\n<td>&nbsp;</td>\n<td width='500'>\n<img src='".$gbl_uriProject."/home/guest/imgs/email_invite.png' border='0' style='float:right; padding-left: 5px;' />\n<h1 style='padding: 50px 0 10px 0; font-size: 32px; font-variant: small-caps; color: #92bfe5;'>".PROJECT."</h1><br />\n<h2 style='margin-bottom: 5px; font: 12pt verdana bold; color: #808080;'>You have been referred!</h2>\n<p style='font: 12px/17px verdana; color: #808080; text-align: justify;'>\n".$contact.",<br />\nYou have been referred to our website by \"".$name."\" who thinks you would be interested in it. So feel free to visit <a href='".$gbl_uriProject."' target='_new'>our site</a>, to find out what all the fuss is about. And by creating an account after using the preceding link, you will have access to fully interact with other users! If you have any questions, comments, or concerns, please contact our staff at your earliest convenience.<br />\n<br />\nSincerely,<br />\n".PROJECT." Staff\n<br /><br /><br /><br />Optional Message:<br />\n".$_POST['txtContactMsg']."</p>\n</td>\n<td>&nbsp;</td>\n</tr>\n<table>\n</body>\n</html>"; }
	else if ($_POST['hidType'] == 'website')
		{ $mail->body = "<html>\n<body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' offset='0' bgcolor='#ffffff'>\n<table width='100%'>\n<tr>\n<td>&nbsp;</td>\n<td width='500'>\n<img src='".$gbl_uriProject."/home/guest/imgs/email_invite.png' border='0' style='float:right; padding-left: 5px;' />\n<h1 style='padding: 50px 0 10px 0; font-size: 32px; font-variant: small-caps; color: #92bfe5;'>".PROJECT."</h1><br />\n<h2 style='margin-bottom: 5px; font: 12pt verdana bold; color: #808080;'>You have been referred!</h2>\n<p style='font: 12px/17px verdana; color: #808080; text-align: justify;'>\n".$contact.",<br />\nYou have been referred to our website by \"".$name."\". Perhaps they think it contains information you have been looking for or require, or just something that you may have interest in. So feel free to visit <a href='".$_POST['hidID']."' target='_new'>".$_POST['hidName']."</a>, to find out what all the fuss is about when you have a chance! If you have any questions, comments, or concerns, please contact our staff at your earliest convenience.<br />\n<br />\nSincerely,<br />\n".PROJECT." Staff\n<br /><br /><br /><br />Optional Message:<br />\n".$_POST['txtContactMsg']."</p>\n</td>\n<td>&nbsp;</td>\n</tr>\n<table>\n</body>\n</html>"; }

	if ($mail->send())
		{ echo "<s><msg>The referred person has been contacted - we appreciate it!</msg></s>"; }
	else
		{ echo "<f><msg>An error was encountered while attempting to send the mail, our staff has been alerted.</msg></f>"; }
	exit();


} else if ($_POST['action'] == 'send' && $_POST['target'] == 'referral') {	// send the referral!
	# validate all submitted data
	if (! validate($_POST['id'],64,'[^0-9]')) { exit(); }

	# connect to the DB for writing below
	if (! connect2DB(DBHOST,DBNAME,DBUNRO,DBPWRO)) { exit(); }		# since the errors from this function are handled internal, we can just 'exit()' during a failure

	$name = 'Anonymous';
	if ($_POST['id'] > 0) {							# if the user was logged in when sending the referral, then lets fetch their account info
		$gbl_errs['error'] = "The user account can not be found in the database.";
		if (USERS == '')						# IF we need to access the native Tracker DB table, then...
			{ $gbl_info['command'] = "SELECT pes,alias,first,last,company FROM ".PREFIX."Accounts WHERE id=? LIMIT 1"; }
		else
			{ $gbl_info['command'] = "SELECT ".ALIAS.",".FIRST.",".LAST." FROM ".PREFIX.USERS." WHERE ".UID."=? LIMIT 1"; }
		$gbl_info['values'] = '[i] '.$_POST['id'];
		$stmt = $linkDB->prepare($gbl_info['command']);
		$stmt->bind_param('i', $_POST['id']);
		$stmt->execute();
		$Referral = $stmt->get_result()->fetch_assoc();
		if (! $Referral) {
			echo "<f><msg>The referrals account could not be found in the database.</msg></f>";
			exit();
		}

		if (USERS == '') {
			$salt = file_get_contents('../../denaccess');		# decrypt the users 'personal encryption string' (pes)
			$Referral['pes'] = Cipher::decrypt($Referral['pes'], $salt);	# use the 'pes' to decrypt the users account fields
			if (! is_null($Referral['last']) && $Referral['last'] != '') { $Referral['last'] = Cipher::decrypt($Referral['last'], $Referral['pes']); }	# NOTE: the "!= ''" is for accounts with a password reset

			if ($Referral['first'] != '') { $name = $Referral['first']; } else if ($Referral['alias'] != '') { $name = $Referral['alias']; }
			if ($Referral['first'] != '' && $Referral['last'] != '') { $name .= " ".$Referral['last']; }
			if ($Referral['company'] != '') {
				if ($name == '') { $name = $Referral['company']; } else { $name .= ' @ '.$Referral['company']; }
			}
		} else {
			if ($Referral['first'] != '') { $name = $Referral['first']; } else if ($Referral['alias'] != '') { $name = $Referral['alias']; }
			if ($Referral['first'] != '' && $Referral['last'] != '') { $name .= " ".$Referral['last']; }
		}
	}

	echo "<s><data id='".$_POST['id']."' name=\"".$name."\" /></s>\n";
	exit();


} else if ($_POST['action'] == 'send' && $_POST['target'] == 'job') {		// send the job referral!
	// make appropriate substitutions
	$_POST['txtContactMsg'] = str_replace('<','&lt;',$_POST['txtContactMsg']);
	$_POST['txtContactMsg'] = str_replace('>','&gt;',$_POST['txtContactMsg']);

	# validate all submitted data
	if (! validate($_POST['txtContact'],64,'[^a-zA-Z0-9_\- ]')) { exit(); }
	if (! validate($_POST['txtContactEmail'],128,'[^a-zA-Z0-9\.@\-_]')) { exit(); }
	#if (! validate($_POST['txtContactMsg'],256,'![=<>;]')) { exit(); }	# NOTE: since this is just an email that gets sent, there is no reason to perform these checks; besides the &lt;/&gt; conversion will still retain a semicolon!
	if (! validate($_POST['title'],10,'{Marketing|Sales|Service|Support}')) { exit(); }

	# construct the email to send to the contact
	$mail = new mime_mail();
	$mail->from = $gbl_emailNoReply;
	$mail->cc = "";
	$mail->headers = "Errors-To: ".$gbl_emailHackers;
	$mail->to = $_POST['txtContactEmail'];
	$mail->subject = "You have been referred!";
	$mail->body = "<html>\n<body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' offset='0' bgcolor='#ffffff'>\n<table width='100%'>\n<tr>\n<td>&nbsp;</td>\n<td width='500'>\n<img src='".$gbl_uriProject."/home/guest/imgs/email_invite.png' border='0' style='float:right; padding-left: 5px;' />\n<h1 style='padding: 50px 0 10px 0; font-size: 32px; font-variant: small-caps; color: #92bfe5;'>".PROJECT."</h1><br />\n<h2 style='margin-bottom: 5px; font: 12pt verdana bold; color: #808080;'>You have been referred!</h2>\n<p style='font: 12px/17px verdana; color: #808080; text-align: justify;'>\n".$_POST['txtContact'].",<br />\nYou have been referred to look at the \"".$_POST['title']."\" job posting on our website by someone who thinks you would be interested in it! Please feel free to visit <a href='".$gbl_uriProject."/#Earn' target='_new'>our site</a>, to read about this and other jobs and methods of earning money by working with us. If you have any questions, comments, or concerns, please contact our staff at your earliest convenience.<br />\n<br />\nSincerely,<br />\n".PROJECT." Staff\n<br /><br /><br /><br />Optional Message:<br />\n".$_POST['txtContactMsg']."</p>\n</td>\n<td>&nbsp;</td>\n</tr>\n<table>\n</body>\n</html>";

	if ($mail->send())
		{ echo "<s><msg>The referred person has been contacted - we appreciate it!</msg></s>"; }
	else
		{ echo "<f><msg>An error was encountered while attempting to send the mail, our staff has been alerted.</msg></f>"; }
	exit();


} else if ($_POST['action'] == 'apply' && $_POST['target'] == 'job') {		// apply for the job posting
	# validate all submitted data
	if (! validate($_POST['id'],6,'[^0-9]')) { exit(); }
	if (! validate($_POST['title'],10,'{Marketing|Sales|Service|Support}')) { exit(); }

	# construct the email to send to the contact (with attachment)
#	echo email::sendMail("dhenderson@digital-pipe.com", "Test Attach ".date("H:i:s"), "This is the body", $_POST['hidResume'], '', '', false);

	$mail = new mime_mail();
	$mail->from = $gbl_emailNoReply;
	$mail->cc = "";
	$mail->headers = "Errors-To: ".$gbl_emailHackers;
	$mail->to = 'jobs@'.$gbl_uriContact;
	$mail->subject = "A job application has been received!";
	$mail->body = "<html>\n<body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' offset='0' bgcolor='#ffffff'>\n<table width='100%'>\n<tr>\n<td>&nbsp;</td>\n<td width='500'>\n<img src='".$gbl_uriProject."/home/guest/imgs/email_invite.png' border='0' style='float:right; padding-left: 5px;' />\n<h1 style='padding: 50px 0 10px 0; font-size: 32px; font-variant: small-caps; color: #92bfe5;'>".PROJECT."</h1><br />\n<h2 style='margin-bottom: 5px; font: 12pt verdana bold; color: #808080;'>Job applicant submission!</h2>\n<p style='font: 12px/17px verdana; color: #808080; text-align: justify;'>\nTeam,<br />\nWe have had a visitor to our site take interest in one of our job postings! The application is for our open position in \"".$_POST['title']."\" and has the random prefix ID of \"".$_POST['id']."\". Please review this applicant at your earliest convenience and response to their submission if they meet our requirements.<br />\n<br />\nSincerely,<br />\n".PROJECT." Staff\n</p>\n</td>\n<td>&nbsp;</td>\n</tr>\n<table>\n</body>\n</html>";
#	if ($_POST['hidCover'] != '') {
#file_put_contents('debug.txt', "we have a cover value!!!\n", FILE_APPEND);
#		$mail->
#	}
#if ($_POST['hidResume'] != '') {
#file_put_contents('debug.txt', "we have a resume value!!!\n", FILE_APPEND);
#}

	if ($mail->send())
		{ echo "<s></s>"; }
	else
		{ echo "<f><msg>An error was encountered while attempting to process the request, our staff has been alerted.</msg></f>"; }
	exit();


} else if ($_POST['action'] == 'send' && $_POST['target'] == 'bug') {		// send the external bug submission
	// make appropriate substitutions
	$_POST['description'] = str_replace('<','&lt;',$_POST['description']);
	$_POST['description'] = str_replace('>','&gt;',$_POST['description']);

	# validate all submitted data
	if (! validate($_POST['txtCaptcha'],16,'[^a-zA-Z\- ]')) { exit(); }
	if (! validate($_POST['title'],128,"![=<>;]")) { exit(); }
	if (! validate($_POST['description'],3072,"![=<>;]")) { exit(); }

	# construct the email to send to the contact
	$mail = new mime_mail();
	$mail->from = $gbl_emailNoReply;
	$mail->cc = "";
	$mail->headers = "Errors-To: ".$gbl_emailHackers;
	$mail->to = 'hackers@cliquesoft.org';
	$mail->subject = "External Bug Submission";
	$mail->body = "<html>\n<body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' offset='0' bgcolor='#ffffff'>\n<table width='100%'>\n<tr>\n<td>&nbsp;</td>\n<td width='500'>\n<img src='".$gbl_uriProject."/home/guest/imgs/email_alert.png' border='0' style='float:right; padding-left: 5px;' />\n<h1 style='padding: 50px 0 10px 0; font-size: 32px; font-variant: small-caps; color: #92bfe5;'>".PROJECT."</h1><br />\n<h2 style='margin-bottom: 5px; font: 12pt verdana bold; color: #808080;'>External Bug Submission</h2>\n<p style='font: 12px/17px verdana; color: #808080; text-align: justify;'>\nTeam,<br />\nWe have had a bug submission from an external installation of our ".PROJECT." software. All of the necessary information will be included below in order to identify the problem at hand along with the contact information for the organization hosting an instance of our software. Please investigate this matter at your earliest convenience and add any verified bug to our internal tracking for resolution.<br />\n<br />\nSincerely,<br />\n".PROJECT." Staff<br />\n<br />\n<br />\nProject: ".$gbl_uriProject."<br />\nHackers: ".$gbl_emailHackers."<br />\nCrackers: ".$gbl_emailCrackers."<br />\nIP Address: ".$_SERVER['REMOTE_ADDR']."<br />\n<br />\nTitle: ".$_POST['title']."<br />\nDescription: ".$_POST['description']."<br />\n</p>\n</td>\n<td>&nbsp;</td>\n</tr>\n<table>\n</body>\n</html>";

	if ($mail->send())
		{ echo "<s><msg>The Cliquesoft staff has been contacted - we appreciate it!</msg></s>"; }
	else
		{ echo "<f><msg>An error was encountered while attempting to send the mail, our staff has been alerted.</msg></f>"; }
	exit();


} else if ($_POST['action'] == 'send' && $_POST['target'] == 'feature') {	// send the external feature request
	// make appropriate substitutions
	$_POST['description'] = str_replace('<','&lt;',$_POST['description']);
	$_POST['description'] = str_replace('>','&gt;',$_POST['description']);

	# validate all submitted data
	if (! validate($_POST['txtCaptcha'],16,'[^a-zA-Z\- ]')) { exit(); }
	if (! validate($_POST['title'],128,"![=<>;]")) { exit(); }
	if (! validate($_POST['description'],3072,"![=<>;]")) { exit(); }

	# construct the email to send to the contact
	$mail = new mime_mail();
	$mail->from = $gbl_emailNoReply;
	$mail->cc = "";
	$mail->headers = "Errors-To: ".$gbl_emailHackers;
	$mail->to = 'hackers@cliquesoft.org';
	$mail->subject = "External Feature Request";
	$mail->body = "<html>\n<body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' offset='0' bgcolor='#ffffff'>\n<table width='100%'>\n<tr>\n<td>&nbsp;</td>\n<td width='500'>\n<img src='".$gbl_uriProject."/home/guest/imgs/email_alert.png' border='0' style='float:right; padding-left: 5px;' />\n<h1 style='padding: 50px 0 10px 0; font-size: 32px; font-variant: small-caps; color: #92bfe5;'>".PROJECT."</h1><br />\n<h2 style='margin-bottom: 5px; font: 12pt verdana bold; color: #808080;'>External Feature Request</h2>\n<p style='font: 12px/17px verdana; color: #808080; text-align: justify;'>\nTeam,<br />\nWe have had a new feature request from an external installation of our ".PROJECT." software. All of the necessary information will be included below in order to explain the new feature along with the contact information for the organization hosting an instance of our software. Please investigate this matter at your earliest convenience and add any legitimate request to our internal tracking for future inclusion.<br />\n<br />\nSincerely,<br />\n".PROJECT." Staff<br />\n<br />\n<br />\nProject: ".$gbl_uriProject."<br />\nHackers: ".$gbl_emailHackers."<br />\nCrackers: ".$gbl_emailCrackers."<br />\nIP Address: ".$_SERVER['REMOTE_ADDR']."<br />\n<br />\nTitle: ".$_POST['title']."<br />\nDescription: ".$_POST['description']."<br />\n</p>\n</td>\n<td>&nbsp;</td>\n</tr>\n<table>\n</body>\n</html>";

	if ($mail->send())
		{ echo "<s><msg>The Cliquesoft staff has been contacted - we appreciate it!</msg></s>"; }
	else
		{ echo "<f><msg>An error was encountered while attempting to send the mail, our staff has been alerted.</msg></f>"; }
	exit();




} else {					// otherwise, we need to indicate that an invalid request was made

	echo "<f><msg>An invalid request has occurred, our staff has been notified.</msg></f>";
	sendMail($gbl_emailCrackers,$gbl_nameCrackers,$gbl_emailNoReply,$gbl_nameNoReply,'!!! Possible Cracking Attempt !!!',"<html>\n<body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' offset='0' bgcolor='#ffffff'>\n<table width='100%'>\n<tr>\n<td>&nbsp;</td>\n<td width='500'>\n<img src='".$gbl_uriProject."/home/guest/imgs/email_error.png' border='0' style='float:right; padding-left: 5px;' />\n<h1 style='padding: 50px 0 10px 0; font-size: 32px; font-variant: small-caps; color: #92bfe5;'>".$gbl_nameProject."</h1>\n<h2 style='margin-bottom: 5px; font: 12pt verdana bold; color: #808080;'>Possible Cracking Attempt</h2><br />\n<p style='font: 12px/17px verdana; color: #808080; text-align: justify;'>\nTeam,<br />\n<br />\nWe might have had a possible cracking attempt made on ".$_.", from ".$_SERVER['REMOTE_ADDR'].", while attempting to pass an invalid API value.  Please investigate and correct this problem as soon as possible.  If the problem warrants contacting the end user, please do so as well by referencing the relevant information below:<br />\n<br />\n<br />\nUsername: ".$gbl_user['username']."<br />\nAddress: ".$gbl_user['email']."<br />\n<br />\nProject: ".PROJECT."<br />\nModule: ".MODULE."<br />\nScript: ".SCRIPT."<br />\n<br />\nDB Host: ".DBHOST."<br />\nDB Name: ".DBNAME."<br />\nDB Prefix: ".PREFIX."<br />\n<br />\nOur Error: An invalid API value was passed to the script.<br />\n<br />\nVar Dump:<br />\n".print_r($_GET, true)."<br />\n".print_r($_POST, true)."<br />\n<br />\n[".SCRIPT."; Body]<br />\n<br />\nSincerely,<br />\n".$gbl_nameProject." Staff\n</p>\n</td>\n<td>&nbsp;</td>\n</tr>\n</table>\n</body>\n</html>");


}
?>
