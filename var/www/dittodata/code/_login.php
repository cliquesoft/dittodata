<?php
# _login.php	a standard module that provides the relevant page IO for
#		logging users into the project.
#
# Created	2012/12/18 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
# Updated	2021/03/15 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
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
define("MODULE",'webbooks');					# the name of this module (NOTE: this can be the same as the PROJECT constant in the envars.php file)
define("SCRIPT",basename($_SERVER['SCRIPT_NAME']));		# the name of this script (for tracing bugs and automated messages)
define("TABLE",'Employees');					# the name of the table containing the user accounts

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




if ($_POST['action'] == 'login' && $_POST['target'] == 'username') {		# Process the login!
	# validate all submitted data
	if (! validate($_POST['Username'],32,'[^a-zA-Z0-9@\._\-]')) { exit(); }
	if (! validate($_POST['Password'],32,'![=<>]')) { exit(); }
	if (CAPTCHAS)
		{ if (! validate($_POST['txtCaptcha'],16,'[^a-zA-Z\- ]')) {exit();} }

	# check the captcha is valid (if it's enabled)
	if (CAPTCHAS) {
		if (empty($_POST['txtCaptcha'])) {
			echo "<f><msg>You must enter the captcha text before attempting to login.</msg></f>";
			exit();
		} else if (empty($_SESSION['captcha']) || trim(strtolower($_POST['txtCaptcha'])) != $_SESSION['captcha']) {
			echo "<f><msg>The captcha text you entered does NOT match what is found in the graphic, please try again.</msg></f>";
			exit();
		}
		unset($_SESSION['captcha']);
	}

# REMOVED 2020/07/15 - we can allow logging in, just not accout creation
#	if (USERS != '') {			# WARNING: we do NOT add accounts to non-native Tracker DB tables - this will be the responsibility of the wrapper project!
#		echo "<f><msg>Logging in with your account in a non-native ".PROJECT." database table is not allowed. Please use the login section of the parent project.</msg></f>";
#		exit();
#	}

	# set the username to lowercase to prevent any issues
	$_POST['Username'] = strtolower($_POST['Username']);

	# process the login
	if (! loadUser(TIMEOUT,'gbl_user','rw','*',PREFIX.TABLE,'username','s|'.$_POST['Username'])) { exit(); }

	# check that the account can be logged into
	if ($gbl_user['attempts'] >= $gbl_intFailedAuth) {
		echo "<f><msg>Your account has had too many failed login attempts which prevents you from logging into the site.  Please contact our staff for additional information.</msg></f>";
		exit();
	} else if ($gbl_user['status'] == 'verfying') {
		echo "<f><msg>Your account is still waiting for the email validatation.  Please check your email and follow the instructions provided.</msg></f>";
		exit();
	} else if ($gbl_user['status'] == 'locked') {
		echo "<f><msg>Your account has been locked which prevents you from logging into the site.  Feel free to use our automated \"Account Services\" to re-enable your account, or contact our staff for additional information.</msg></f>";
		exit();
	} else if ($gbl_user['status'] == 'suspended') {
		echo "<f><msg>Your account has been suspended which prevents you from logging into the site.  Please contact our staff for additional information.</msg></f>";
		exit();
	} else if ($gbl_user['status'] == 'deleted') {
		echo "<f><msg>Your account has been deleted.  Please contact our staff for additional information.</msg></f>";
		exit();
	} else if ($gbl_user['disabled'] == '1') {				# this was intentionally included in the last spot as a 'safety net'
		echo "<f><msg>Your account has been disabled which prevents you from logging into the site.  Please contact our staff for additional information.</msg></f>";
		exit();
	}

	# define general info for any error generated below
	if (array_key_exists('first', $gbl_user)) { $gbl_info['name'] = $gbl_user['first']; }	# if the project uses broken down names (e.g. tracker), so...
	if (array_key_exists('email', $gbl_user)) { $gbl_info['contact'] = $gbl_user['email']; }
	if (array_key_exists('name', $gbl_user)) { $gbl_info['name'] = $gbl_user['name']; }	# otherwise for projects with a single name value (e.g. webBooks), then...
	if (array_key_exists('workEmail', $gbl_user)) { $gbl_info['contact'] = $gbl_user['workEmail']; }
	$gbl_info['other'] = '[Username] '.$gbl_user['username'];
	$gbl_info['values'] = 'None';

	# Process the login request
	$hash = md5($_POST['Password']);					# this section decrypts the password entered by the user
	$salt = file_get_contents('../../denaccess');				# decrypt the users 'personal encryption string' (pes)
	if (array_key_exists('pes', $gbl_user))
		{ $salt = Cipher::decrypt($gbl_user['pes'], $salt); }		# use the 'pes' to decrypt the users hashed password
	$decrypted = Cipher::decrypt($gbl_user['password'], $salt);

	if (strcmp($decrypted, $hash)) {					# IF the entered password doesn't equal the stored hashed password (which would yield 0), then...
		$gbl_user['attempts']++;
		if ($gbl_user['attempts'] < $gbl_intFailedAuth) {		#   if the count hasn't yet reached the maximum allowed, then just update the "attempts" count (this is done with two if statements intentionally, so the "invalid attempt" count would be correct)
			if (array_key_exists('notes', $gbl_user)) {		#   if the table containing the user accounts has the 'notes' column, then...
				$gbl_errs['error'] = "The failed login attempts count can not be updated in the database.";
				$gbl_info['command'] = "UPDATE ".PREFIX.TABLE." SET attempts='".$gbl_user['attempts']."',notes=concat('notes','\n".$_."\nInvalid login attempt from IP Address ".$_SERVER['REMOTE_ADDR']."') WHERE username=\"".$gbl_user['username']."\"";
				$linkDB->query($gbl_info['command']);
			} else {						#   otherwise the notes have their own table, so...
				$gbl_errs['error'] = "The failed login attempts count can not be updated in the database.";
				$gbl_info['command'] = "UPDATE ".PREFIX.TABLE." SET attempts='".$gbl_user['attempts']."' WHERE username=\"".$gbl_user['username']."\"";
				$linkDB->query($gbl_info['command']);

				$gbl_errs['error'] = "The failed login attempts note can not be added in the database.";
				$gbl_info['command'] = "INSERT INTO ".PREFIX."Notes (type,rowID,creatorID,access,note,created,updated) VALUES ('employee','".$gbl_user['id']."','0','managers','".$_."\nInvalid login attempt from IP Address ".$_SERVER['REMOTE_ADDR']."','".$_."','".$_."')";
				$linkDB->query($gbl_info['command']);
			}
			echo "<f><msg>The password does not match our records.  Please try again or use the 'Account Services' link at the bottom of the screen.  Please note that all invalid login attempts have their IP Address recorded.</msg></f>";
		} else {							#   otherwise the count has reached the maximum allowed so disable the account
			if (array_key_exists('notes', $gbl_user)) {
				$gbl_errs['error'] = "Locking of the user account can not be performed in the database.";
				$gbl_info['command'] = "UPDATE ".PREFIX.TABLE." SET attempts='".$gbl_user['attempts']."',disabled='1',status='locked',notes=concat('notes','\n".$_."\nAccount locked due to max login attempts reached.') WHERE username=\"".$gbl_user['username']."\"";
				$linkDB->query($gbl_info['command']);
			} else {
				$gbl_errs['error'] = "Locking of the user account can not be performed in the database.";
				$gbl_info['command'] = "UPDATE ".PREFIX.TABLE." SET attempts='".$gbl_user['attempts']."',disabled='1',status='locked') WHERE username=\"".$gbl_user['username']."\"";
				$linkDB->query($gbl_info['command']);

				$gbl_errs['error'] = "Notation of the locking of the user account can not be performed in the database.";
				$gbl_info['command'] = "INSERT INTO ".PREFIX."Notes (type,rowID,creatorID,access,note,created,updated) VALUES ('employee','".$gbl_user['id']."','0','managers','".$_."\nAccount locked due to max login attempts reached.','".$_."','".$_."')";
				$linkDB->query($gbl_info['command']);
			}
			echo "<f><msg>Your account has been locked due to the amount of invalid login attempts.  Please use the automated 'Password Reset' to regain access to your account.</msg></f>";
# UPDATED 2020/10/27 - to make this more generic
#			sendMail($gbl_info['contact'],'','support@'.$gbl_uriContact,PROJECT,'Account Locked',"<html>\n<body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' offset='0' bgcolor='#ffffff'>\n<table width='100%'>\n<tr>\n<td>&nbsp;</td>\n<td width='500'>\n<img src='".$gbl_uriProject."/themes/default/images/default/email_locked.png' border='0' style='float:right; padding-left: 5px;' />\n<h1 style='padding: 50px 0 10px 0; font-size: 32px; font-variant: small-caps; color: #92bfe5;'>".PROJECT."</h1>\n<h2 style='margin-bottom: 5px; font: 12pt verdana bold; color: #808080;'>Account Locked</h2>\n<p style='font: 12px/17px verdana; color: #808080; text-align: justify;'>\nSir/Madam,<br />\n<br />\nThis email was sent to inform you that your account with ".PROJECT." has been locked due to the amount of failed login attempts.  If you were not responsible for this, we would advise you to change your password to something that you have never used as this may be an attempt to crack into your account using familiar passwords or phrases.  At this point, you will need to go to ".$gbl_uriProject.", click the 'Account' link under 'General', and scroll to the bottom of the screen where the 'Account Services' are located.  Afterwards, fill out the information in the 'Unlock Account' section to gain access to your account once again.  If you are still having trouble or would like to report this incident as an attempt on someone cracking into your account, please respond to this email to let our staff know.<br />\n<br />\n<a href='".$gbl_uriProject."/#Account'>Click here to unlock your account</a><br />\n<br />\n<br />\nSincerely,<br />\n".PROJECT." Staff\n</p>\n</td>\n<td>&nbsp;</td>\n</tr>\n<table>\n</body>\n</html>");
			sendMail($gbl_info['contact'],'','support@'.$gbl_uriContact,PROJECT,'Account Locked',"<html>\n<body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' offset='0' bgcolor='#ffffff'>\n<table width='100%'>\n<tr>\n<td>&nbsp;</td>\n<td width='500'>\n<img src='".$gbl_uriProject."imgs/default/email_locked.png' border='0' style='float:right; padding-left: 5px;' />\n<h1 style='padding: 50px 0 10px 0; font-size: 32px; font-variant: small-caps; color: #92bfe5;'>".PROJECT."</h1>\n<h2 style='margin-bottom: 5px; font: 12pt verdana bold; color: #808080;'>Account Locked</h2>\n<p style='font: 12px/17px verdana; color: #808080; text-align: justify;'>\nSir/Madam,<br />\n<br />\nThis email was sent to inform you that your account with ".PROJECT." has been locked due to the amount of failed login attempts.  If you were not responsible for this, we would advise you to change your password to something that you have never used as this may be an attempt to crack into your account using familiar passwords or phrases.  If this is a legitimate request, you will need to go to ".$gbl_uriProject.", and locate the account services sections to regain access into your account.  Once located, fill out the information in the 'Unlock Account' section to gain access to your account once again.  If you are still having trouble or would like to report this incident as an attempt on someone cracking into your account, please respond to this email to let our staff know.<br />\n<br />\n<a href='".$gbl_uriProject."/#Account'>Click here to unlock your account</a><br />\n<br />\n<br />\nSincerely,<br />\n".PROJECT." Staff\n</p>\n</td>\n<td>&nbsp;</td>\n</tr>\n<table>\n</body>\n</html>");
   		}
		exit();
	}

	# if we've made it here, everything has checked out so far, so process the request...
	$gbl_errs['error'] = "The login could not be recorded in the database.";
	if ($gbl_user['sid'] == '') {						# if the user isn't logged in anywhere already, then...
		$SID = genRandom();						#   generate a random SID for account validation
		$gbl_info['command'] = "UPDATE ".PREFIX.TABLE." SET attempts='0',login='".$_."',sid='$SID' WHERE username=\"".$gbl_user['username']."\"";
	} else {								# otherwise they are already logged into their account from somewhere else, so...
		$SID = $gbl_user['sid'];					#   store that SID to relay back to this login
		$gbl_info['command'] = "UPDATE ".PREFIX.TABLE." SET attempts='0',login='".$_."' WHERE username=\"".$gbl_user['username']."\"";	# update the login timestamp
	}
	$linkDB->query($gbl_info['command']);

	# check that the users 'home' directory exists (in the instance that this project is integrated into another project and this step wasn't performed yet)
	if (! file_exists("../home/".$_POST['Username'])) {
		$gbl_errs['error'] = "Can not create the users 'home' directory.";
		$gbl_info['command'] = "mkdir(\"../home/".$_POST['Username']."\", 0775, true)";
		mkdir("../home/".$_POST['Username']."", 0775, true);

		$gbl_errs['error'] = "Can not create the users associated 'imgs' symlink.";
		$gbl_info['command'] = "symlink(\"../../imgs/default\",\"../home/".$_POST['Username']."/imgs\")";
		symlink("../../imgs/default","../home/".$_POST['Username']."/imgs");

		$gbl_errs['error'] = "Can not create the users associated 'look' symlink.";
		$gbl_info['command'] = "symlink(\"../../look/default\",\"../home/".$_POST['Username']."/look\")";
		symlink("../../look/default","../home/".$_POST['Username']."/look");
	}

	# below displays info to the user about how many invalid login attempts were made on their account prior to a successful login
	$msg = '';
	if ($gbl_user['attempts'] > 0) { $msg = "\n\nPlease note, there has been ".$gbl_user['attempts']." failed login\nattempts since your last successful login."; }
	if (array_key_exists('first', $gbl_info))				# if the project uses broken down names (e.g. tracker), so...
		{ echo "<s><data SID='".$SID."' admin='".$gbl_user['admin']."'>".$gbl_user['first']."|".$gbl_user['last']."|".$gbl_user['username']."|".$gbl_info['contact']."</data><msg>You have logged in successfully!".$msg."</msg></s>"; }
	else									# otherwise for projects with a single name value (e.g. webBooks), then...
		{ echo "<s><data SID='".$SID."' admin='".(array_key_exists('admin', $gbl_user) ? $gbl_user['admin'] : '')."'>".$gbl_user['name']."|".$gbl_user['username']."|".(array_key_exists('workEmail', $gbl_user) ? $gbl_user['workEmail'] : '')."</data><msg>You have logged in successfully!".$msg."</msg></s>"; }
	exit();




} else if ($_POST['action'] == 'reset' && $_POST['target'] == 'password') {	# Reset the account password/info (depending on the presence of the encryption pin)
	if ($_POST['lstPasswordKey'] == '') {
		echo "<f><msg>You must provide an account ID key before resetting your account information.</msg></f>";
		exit();
	} else if ($_POST['txtPasswordValue'] == '') {
		echo "<f><msg>You must provide an account ID value before resetting your account information.</msg></f>";
		exit();
	}

	# validate all submitted data
	if (! validate($_POST['lstPasswordKey'],8,'{username|email}')) { exit(); }
	if (! validate($_POST['txtPasswordValue'],128,'[^a-zA-Z0-9\.@\-_]')) { exit(); }

	if (USERS != '') {			# WARNING: we do NOT add accounts to non-native Tracker DB tables - this will be the responsibility of the wrapper project!
		echo "<f><msg>Resetting an account password in a non-native ".PROJECT." database table is not allowed. Please use the proper section of the parent project.</msg></f>";
		exit();
	}

	# connect to the DB for writing below
	if (! connect2DB(DBHOST,DBNAME,DBUNRW,DBPWRW)) { exit(); }

	# define general info for any error generated below
	$gbl_info['name'] = 'Unknown';
	$gbl_info['contact'] = 'Unknown';
	$gbl_info['other'] = '['.$_POST['lstPasswordKey'].'] '.$_POST['txtPasswordValue'];
	$gbl_info['values'] = '[s] '.$_POST['txtPasswordValue'];
	$gbl_errs['continue'] = TRUE;						# make sure the processing continues if we can't find the account with the original SQL statement

# VER2 - keep an eye on the number of queries for this since it may be a cracking attempt to find out account information
	$gbl_errs['error'] = "Failed to find the user account in the database via the passed values.";
	$gbl_info['command'] = "SELECT * FROM ".PREFIX.TABLE." WHERE ".$_POST['lstPasswordKey']."=? LIMIT 1";
	$stmt = $linkDB->prepare($gbl_info['command']);
	if ($stmt === FALSE && $_POST['lstPasswordKey'] == 'email') {		# if the user selected 'email' as the key (and we're using webBooks), then...
		$_POST['lstPasswordKey'] = 'workEmail';				#   update the key value depending on the DB layout
		$gbl_info['command'] = "SELECT * FROM ".PREFIX.TABLE." WHERE ".$_POST['lstPasswordKey']."=? LIMIT 1";
		$stmt = $linkDB->prepare($gbl_info['command']);
		$stmt->bind_param('s', $_POST['txtPasswordValue']);		#   and try the call again
	}
	$stmt->bind_param('s', $_POST['txtPasswordValue']);
	$stmt->execute();
	$account = $stmt->get_result()->fetch_assoc();
	if (! $account) {
		echo "<f><msg>No account containing the submitted information could be found in the database.</msg></f>";
		exit();
	}
	$gbl_errs['continue'] = FALSE;						# reset this value so we halt on errors once again

	$password = '';
	$pes = '';
	$query = '';

# NEW
	$salt = file_get_contents('../../denaccess');
	if (array_key_exists('pes', $account)) {				# if the database table uses a personal encryption string (pes), then...
		$pes = Cipher::create_encryption_key();				# generate a personal encryption string for the account
		$encPES = Cipher::encrypt($pes, $salt);				# encrypt it using the global encryption string
		$oldPES = Cipher::decrypt($account['pes'], $salt);		# store the old 'pes' to re-encrypt the users account fields (and it's the new libsodium encryption)
		$salt = $pes;							# update the value so that the below code works with the global or personal encryption string
	 }


# OLD
#	$salt = file_get_contents('../../denaccess');
#	$pes = Cipher::create_encryption_key();
#	$encPES = Cipher::encrypt($pes, $salt);
#	if (strlen($account['pes']) > 100) { $oldPES = Cipher::decrypt($account['pes'], $salt); }		# store the old 'pes' to re-encrypt the users account fields (and it's the new libsodium encryption)




	# generate a new random password
	$other = array('0','1','2','3','4','5','6','7','8','9','.','?','!','@','#','$','%','^','&','*','-','_','+',':','~','`');
	$lower = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
	$upper = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
	for ($i=0; $i<8; $i++) {						# this for loop creates the new random 8 character value
		$type = rand(0,2);						# randomly selects which of the above arrays to pick a character from
		$char = rand(0,25);						# randomly selects which character in the array to add to the new password

		if ($type == 0) { $password .= $other[$char]; }			# creates the 'password' value based on the randomly selected values from above
		else if ($type == 1) { $password .= $lower[$char]; }
		else { $password .= $upper[$char]; }
	}

	# encrypt the new password
	$hash = md5($password);							# hash the password and store that value, not the actual password!!!	https://stackoverflow.com/questions/9262109/simplest-two-way-encryption-using-php
	$encPass = Cipher::encrypt($hash, $salt); 

	# re-encrypt the account values with the updated 'pes'
	if (array_key_exists('pes', $account)) {				# if the database table uses a personal encryption string (pes), then...
		if (array_key_exists('last', $account)) { $account['last'] = Cipher::encrypt(Cipher::decrypt($account['last'], $oldPES), $salt); }
		if (! is_null($account['answer1']) && $account['answer1'] != '') { $account['answer1'] = Cipher::encrypt(Cipher::decrypt($account['answer1'], $oldPES), $salt); } else { $account['answer1'] = ''; }
		if (! is_null($account['answer2']) && $account['answer2'] != '') { $account['answer2'] = Cipher::encrypt(Cipher::decrypt($account['answer2'], $oldPES), $salt); } else { $account['answer2'] = ''; }
		if (! is_null($account['answer3']) && $account['answer3'] != '') { $account['answer3'] = Cipher::encrypt(Cipher::decrypt($account['answer3'], $oldPES), $salt); } else { $account['answer3'] = ''; }
	}

	$name = 'Unknown User';
	if (array_key_exists('name', $account)) {								# for projects with a single name value (e.g. webBooks), then...
		if ($account['name'] != '') { $name = $account['name']; }
		else if ($account['username'] != '') { $name = $account['username']; }
	} else if (array_key_exists('first', $account) && array_key_exists('alias', $account)) {		# otherwise the project uses broken down names (e.g. tracker), so...
		if ($account['first'] != '') { $name = $account['first']; }
		else if ($account['alias'] != '') { $name = $account['alias']; }
		else if ($account['username'] != '') { $name = $account['username']; }
	}

	$gbl_errs['error'] = "Failed to reset the user account information in the database.";
	if (array_key_exists('name', $account) && array_key_exists('pes', $account))				# for projects with a single name value (e.g. webBooks) -AND- the database uses 'pes' values, then...
		{ $gbl_info['command'] = "UPDATE ".PREFIX.TABLE." SET sid='',pes=\"".$encPES."\",password=\"".$encPass."\",answer1=\"".$account['answer1']."\",answer2=\"".$account['answer2']."\",answer3=\"".$account['answer3']."\",updated='".$_."' WHERE ".$_POST['lstPasswordKey']."=?"; }
	else if (array_key_exists('name', $account) && ! array_key_exists('pes', $account))			# same as above but without a 'pes' value, then...
		{ $gbl_info['command'] = "UPDATE ".PREFIX.TABLE." SET sid='',password=\"".$encPass."\",answer1=\"".$account['answer1']."\",answer2=\"".$account['answer2']."\",answer3=\"".$account['answer3']."\",updated='".$_."' WHERE ".$_POST['lstPasswordKey']."=?"; }
	else if (array_key_exists('last', $account) && array_key_exists('pes', $account))			# otherwise the project uses broken down names (e.g. tracker), so...
		{ $gbl_info['command'] = "UPDATE ".PREFIX.TABLE." SET sid='',pes=\"".$encPES."\",password=\"".$encPass."\",last=\"".$account['last']."\",answer1=\"".$account['answer1']."\",answer2=\"".$account['answer2']."\",answer3=\"".$account['answer3']."\",updated='".$_."' WHERE ".$_POST['lstPasswordKey']."=?"; }
	else if (array_key_exists('last', $account) && ! array_key_exists('pes', $account))			# same as above but without a 'pes' value, so...
		{ $gbl_info['command'] = "UPDATE ".PREFIX.TABLE." SET sid='',password=\"".$encPass."\",last=\"".$account['last']."\",answer1=\"".$account['answer1']."\",answer2=\"".$account['answer2']."\",answer3=\"".$account['answer3']."\",updated='".$_."' WHERE ".$_POST['lstPasswordKey']."=?"; }
	$stmt = $linkDB->prepare($gbl_info['command']);
	$stmt->bind_param('s', $_POST['txtPasswordValue']);
	$stmt->execute();

	if (array_key_exists('name', $account))									# for projects with a single name value (e.g. webBooks), then...
		{ sendMail($account['workEmail'],$name,$gbl_emailNoReply,$gbl_nameNoReply,'Password Reset Confirmation',"<html>\n<body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' offset='0' bgcolor='#ffffff'>\n<table width='100%'>\n<tr>\n<td>&nbsp;</td>\n<td width='500'>\n<img src='".$gbl_uriProject."/home/guest/imgs/email_info.png' border='0' style='float:right; padding-left: 5px;' />\n<h1 style='padding: 50px 0 10px 0; font-size: 32px; font-variant: small-caps; color: #92bfe5;'>".PROJECT."</h1><br />\n<h2 style='margin-bottom: 5px; font: 12pt verdana bold; color: #808080;'>Password Reset Confirmation</h2><br />\n<p style='font: 12px/17px verdana; color: #808080; text-align: justify;'>\nThis email is to notify you that your ".PROJECT." account password has been reset using the 'Account Services' functionality from our website.  The new randomly generated password has been included below to allow you access back into your account.  We recommend that you update this value, after logging in, to something that you can better remember.  For your convenience, we have also included a link to our website below.<br /><br /><b>WARNING:</b> If you did not initiate this process, contact our staff immediately as this may be a malicious cracking attempt with your account!<br />\n<br />\nPassword: ".$password."<br />\nWebsite: <a href='".$gbl_uriProject."/#Login' target='_new'>".PROJECT."</a><br /><br />\nSincerely,<br />\n".PROJECT." Staff\n</p>\n</td>\n<td>&nbsp;</td>\n</tr>\n</table>\n</body>\n</html>"); }
	else													# otherwise the project uses broken down names (e.g. tracker), so...
		{ sendMail($account['email'],$name,$gbl_emailNoReply,$gbl_nameNoReply,'Password Reset Confirmation',"<html>\n<body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' offset='0' bgcolor='#ffffff'>\n<table width='100%'>\n<tr>\n<td>&nbsp;</td>\n<td width='500'>\n<img src='".$gbl_uriProject."/home/guest/imgs/email_info.png' border='0' style='float:right; padding-left: 5px;' />\n<h1 style='padding: 50px 0 10px 0; font-size: 32px; font-variant: small-caps; color: #92bfe5;'>".PROJECT."</h1><br />\n<h2 style='margin-bottom: 5px; font: 12pt verdana bold; color: #808080;'>Password Reset Confirmation</h2><br />\n<p style='font: 12px/17px verdana; color: #808080; text-align: justify;'>\nThis email is to notify you that your ".PROJECT." account password has been reset using the 'Account Services' functionality from our website.  The new randomly generated password has been included below to allow you access back into your account.  We recommend that you update this value, after logging in, to something that you can better remember.  For your convenience, we have also included a link to our website below.<br /><br /><b>WARNING:</b> If you did not initiate this process, contact our staff immediately as this may be a malicious cracking attempt with your account!<br />\n<br />\nPassword: ".$password."<br />\nWebsite: <a href='".$gbl_uriProject."/#Login' target='_new'>".PROJECT."</a><br /><br />\nSincerely,<br />\n".PROJECT." Staff\n</p>\n</td>\n<td>&nbsp;</td>\n</tr>\n</table>\n</body>\n</html>"); }
	echo "<s><msg>Your password has been reset successfully, please check your email to continue!</msg></s>";
	exit();




} else if ($_POST['action'] == 'unlock' && $_POST['target'] == 'Account') {	# unlock the account	NOTE: the 'target' value has to be different so as to not trigger a section above (can possibly re-arrange the layout to fix)
	if ($_POST['lstUnlockKey'] == '') {
		echo "<f><msg>You must provide an account ID key before unlocking your account.</msg></f>";
		exit();
	} else if ($_POST['txtUnlockValue'] == '') {
		echo "<f><msg>You must provide an account ID value before unlocking your account.</msg></f>";
		exit();
	}

	if (USERS != '') {			# WARNING: we do NOT add accounts to non-native Tracker DB tables - this will be the responsibility of the wrapper project!
		echo "<f><msg>Resetting an account password in a non-native ".PROJECT." database table is not allowed. Please use the proper section of the parent project.</msg></f>";
		exit();
	}

	# validate all submitted data
	if (! validate($_POST['lstUnlockKey'],8,'{username|email}')) { exit(); }
	if (! validate($_POST['txtUnlockValue'],128,'[^a-zA-Z0-9\.@\-_]')) { exit(); }
	if (isset($_POST['answer']) && ! validate($_POST['answer'],32,'![=<>;]')) { exit(); }
	if (isset($_POST['id']) && ! validate($_POST['id'],1,'{1|2|3}')) { exit(); }

	# connect to the DB for writing below
	if (! connect2DB(DBHOST,DBNAME,DBUNRW,DBPWRW)) { exit(); }

	# define general info for any error generated below
	$gbl_info['name'] = 'Unknown';
	$gbl_info['contact'] = 'Unknown';
	$gbl_info['other'] = '['.$_POST['lstUnlockKey'].'] '.$_POST['txtUnlockValue'];
	$gbl_info['values'] = '[s] '.$_POST['txtUnlockValue'];

	$gbl_errs['error'] = "Failed to find the user account in the database via the passed values.";
	$gbl_info['command'] = "SELECT * FROM ".PREFIX.TABLE." WHERE ".$_POST['lstUnlockKey']."=? LIMIT 1";
	$stmt = $linkDB->prepare($gbl_info['command']);
	$stmt->bind_param('s', $_POST['txtUnlockValue']);
	$stmt->execute();
	$account = $stmt->get_result()->fetch_assoc();
	if (! $account) {
		echo "<f><msg>No account containing the submitted information could be found in the database.</msg></f>";
		exit();
	}

	# perform any security checks
	if ($account['attempts'] >= ($gbl_intFailedAuth * 2)) {			# if the count has reached the maximum allowed for unlocking, then...
		echo "<f><msg>You have reached the maximum unlock threshold and will now have to call the Cliquesoft technical support staff to unlock your account.</msg></f>";
		exit();
	}
	if ($account['status'] != 'locked') {
		echo "<f><msg>Your account is not locked, so this process has been cancelled.</msg></f>";
		exit();
	}

	# decrypt the account information to get a security question
	$salt = file_get_contents('../../denaccess');				# decrypt the users 'personal encryption string' (pes)
	if (array_key_exists('pes', $account))					# if the database table uses a personal encryption string (pes), then...
		{ $salt = Cipher::decrypt($account['pes'], $salt); }		# use the 'pes' to decrypt the users account fields

	if (! isset($_POST['answer'])) {					# if we are in the 'obtain the question' part of this task, then...
		# obtain a random number between 1 and 3 (since we only have 3 security questions)
		$random = rand(1,3);

		$q = $account['question'.$random];
		# return a random security question
		echo "<s><data id='".$random."'>".safeXML($q)."</data></s>";
	} else {								# otherwise, we are on the 'process the submitted answer' portion, so...
		$a = Cipher::decrypt($account['answer'.$_POST['id']], $salt);

		if (strtolower($a) != strtolower($_POST['answer'])) {		# to avoid issues with capitalization, make the answers all lowercase
			if ($account['attempts'] < ($gbl_intFailedAuth * 2)) {	#   if the count has NOT yet reached the maximum allowed for unlocking, then...
				$gbl_errs['error'] = "Failed to update the failed login count in the database.";
				$gbl_info['command'] = "UPDATE ".PREFIX.TABLE." SET attempts=attempts+1,notes='".$account['notes']."\n".date("Y-m-d H:i:s",time())."\nInvalid account unlock attempt using wrong Answer, from IP Address ".$_SERVER['REMOTE_ADDR'].".' WHERE ".$_POST['lstUnlockKey']."=?";
				$stmt = $linkDB->prepare($gbl_info['command']);
				$stmt->bind_param('s', $_POST['txtUnlockValue']);
				$stmt->execute();
			} else {
				$gbl_errs['error'] = "Failed to suspend the account in the database due to excessive unlock attempts.";
				$gbl_info['command'] = "UPDATE ".PREFIX.TABLE." SET status='suspended',attempts=attempts+1,notes='".$account['notes']."\n".date("Y-m-d H:i:s",time())."\nAccount now completely suspended due to max unlock attempts reached.' WHERE ".$_POST['lstUnlockKey']."=?";
				$stmt = $linkDB->prepare($gbl_info['command']);
				$stmt->bind_param('s', $_POST['txtUnlockValue']);
				$stmt->execute();
			}

			echo "<f><msg>The submitted answer is incorrect, please try again.</msg></f>";
			exit();
		}

		$gbl_errs['error'] = "Failed to reset the account in the database after successfully unlocking.";
		$gbl_info['command'] = "UPDATE ".PREFIX.TABLE." SET sid='',disabled='0',attempts='0',status='active',updated='".$_."' WHERE ".$_POST['lstUnlockKey']."=?";
		$stmt = $linkDB->prepare($gbl_info['command']);
		$stmt->bind_param('s', $_POST['txtUnlockValue']);
		$stmt->execute();

		echo "<s><msg>Your account has been unlocked successfully!</msg></s>";
	}
	exit();




} else {					// otherwise, we need to indicate that an invalid request was made
	echo "<f><msg>An invalid request has occurred, our staff has been notified.</msg></f>";
	sendMail($gbl_emailCrackers,$gbl_nameCrackers,$gbl_emailNoReply,SCRIPT.' script','*** Possible Cracking Attempt ***',"<html>\n<body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' offset='0' bgcolor='#ffffff'>\n<table width='100%'>\n<tr>\n<td>&nbsp;</td>\n<td width='500'>\n<img src='".$gbl_uriProject."/home/guest/imgs/webbooks.email_alert.png' border='0' style='float:right; padding-left: 5px;' />\n<h1 style='padding: 50px 0 10px 0; font-size: 32px; font-variant: small-caps; color: #92bfe5;'>".PROJECT."</h1>\n<h2 style='margin-bottom: 5px; font: 12pt verdana bold; color: #808080;'>Possible Cracking Attempt</h2><br />\n<p style='font: 12px/17px verdana; color: #808080; text-align: justify;'>\nTeam,<br />\n<br />\nOne of the users was interacting with our '".PROJECT."' project, but encountered the listed error in the process of doing so.  Please investigate and correct this problem as soon as possible.  If the problem warrants contacting the end user, please do so as well by referencing the relevant information below:<br />\n<br />\n<br />\nProject: ".PROJECT."<br />\nModule: ".MODULE."<br />\nScript: ".SCRIPT."<br />\n<br />\nDB Host: ".DBHOST."<br />\nDB Name: ".DBNAME."<br />\nDB Prefix: ".PREFIX."<br />\n<br />\nOur Error: A user is attempting to pass an invalid 'action' or 'target' values.<br />\n<br />\nVar Dump:<br />\n".print_r($_GET, true)."<br />\n".print_r($_POST, true)."<br />\n</p>\n</td>\n<td>&nbsp;</td>\n</tr>\n<table>\n</body>\n</html>");


}
?>
