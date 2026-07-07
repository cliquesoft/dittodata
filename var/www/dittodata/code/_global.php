<?php
# _global.php	provides access to extra functions
# created	2009/10/08 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
# updated	2021/06/15 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
#
# Unless a valid Cliquesoft Proprietary License (CPLv1) has been purchased
# for this device, this software is licensed under the Cliquesoft Public
# License (CPLv2) as found on the Cliquesoft website at www.cliquesoft.org
#
# This program is distributed in the hope that it will be useful, but WITHOUT
# ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
# FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.
#
# ADDITIONAL
# http://stackoverflow.com/questions/6215789/build-condition-comparison-for-if-statement
# https://stackoverflow.com/questions/57153848/converting-from-mysqli-to-prepared-statements#57154741
# https://phpdelusions.net/articles/error_reporting
#
# Prepared SQL Statement Definitions:
#	b - blob/binary (such as image, PDF file, etc.)
#	d - double (floating point number/decimal)
#	i - integer (whole number)
#	s - string (text, date)




# This tells php how to handle -MOST- errors that it encounters (which it to call the specified function)
set_error_handler("myErrorHandler");
function myErrorHandler($errno, $errstr, $errfile, $errline) {
# gbl_errs definitions:
#	$gbl_info['admin']	the name of the admin to email
#	$gbl_info['email']	the email address of the admin
#	$gbl_info['name']	the username, name, alias, or some identifying data of the user account
#	$gbl_info['contact']	a phone number, email address, or some information for contacting the user
#	$gbl_info['other']	any other account information relevent to the error (optional)
#	$gbl_info['command']	the command that was attempting to be executed that threw the error
#	$gbl_info['values']	the values associated with the 'command' when using prepared SQL statements
#	$gbl_errs['error']	considered "our error" which is a summary of the problem encountered
#	$gbl_errs['prompt']	the prompt to show to the user; without this value a generic default will be displayed; a value of 'off' disables message
#	$gbl_errs['continue']	whether or not script execution should continue after the error has triggered this function
	global $gbl_emailHackers,$gbl_nameHackers,$gbl_emailNoReply,$gbl_nameNoReply,$gbl_uriProject,$gbl_errs,$gbl_info,$gbl_dirLogs,$gbl_logScript;

	# set some default values
	if (! isset($gbl_info['admin']) || $gbl_info['admin'] == '') { $gbl_info['admin'] = $gbl_nameHackers; }
	if (! isset($gbl_info['email']) || $gbl_info['email'] == '') { $gbl_info['email'] = $gbl_emailHackers; }
	if (! isset($gbl_info['name']) || $gbl_info['name'] == '') { $gbl_info['name'] = 'Unknown'; }
	if (! isset($gbl_info['contact']) || $gbl_info['contact'] == '') { $gbl_info['contact'] = 'Unknown'; }
	if (! isset($gbl_info['other']) || $gbl_info['other'] == '') { $gbl_info['other'] = 'None'; }
	if (! isset($gbl_info['command']) || $gbl_info['command'] == '') { $gbl_info['command'] = 'Not Provided'; }
	if (! isset($gbl_info['values']) || $gbl_info['values'] == '') { $gbl_info['values'] = 'None'; }
	if (! isset($gbl_errs['error']) || $gbl_errs['error'] == '') { $gbl_errs['error'] = 'Not Provided'; }
	if (! isset($gbl_errs['continue']) || $gbl_errs['continue'] === FALSE) {
		#if (isset($gbl_errs['prompt']) || array_key_exists('prompt',$gbl_errs))	# https://www.php.net/manual/en/function.array-key-exists.php
		if (! isset($gbl_errs['prompt']) || $gbl_errs['prompt'] == '')
			{ echo "<f><msg>There was an error processing your request and our staff has been notified.  Please try again in a few minutes.</msg></f>"; }
		else if ($gbl_errs['prompt'] != 'off')
			{ echo "<f><msg>".$gbl_errs['prompt']."</msg></f>"; }
	}
	# Alternative to show a page instead of error message
	#header('HTTP/1.1 500 Internal Server Error', TRUE, 500);
	#readfile("500.html");

# REMOVED 2019/07/31 - this was triggering problems of its own (even if the file existed!!!)
#	error_log("[".gmdate("Y-m-d H:i:s",time())."]\n$errfile\n$errline: $errstr\n\n",3,$gbl_dirLogs.'/'.$gbl_logScript);

	sendMail($gbl_info['email'],$gbl_info['admin'],$gbl_emailNoReply,$gbl_nameNoReply,'*** Script Execution Error ***',"<html>\n<body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' offset='0' bgcolor='#ffffff'>\n<table width='100%'>\n<tr>\n<td>&nbsp;</td>\n<td width='500'>\n<img src='".$gbl_uriProject."home/guest/imgs/email_error.png' border='0' style='float:right; padding-left: 5px;' />\n<h1 style='padding: 50px 0 10px 0; font-size: 32px; font-variant: small-caps; color: #92bfe5;'>".PROJECT."</h1><br />\n<h2 style='margin-bottom: 5px; font: 12pt verdana bold; color: #808080;'>Script Execution Error</h2><br />\n<p style='font: 12px/17px verdana; color: #808080; text-align: justify;'>\nTeam,<br />\n<br />\nOne of our users was attempting to interact with our site, but encountered an error which has been included below.  Please investigate and correct this problem as soon as possible.  If the problem warrants contacting the end user, please do so as well by referencing the relevant information below:<br />\n<br />\n<u>Date:</u> ".gmdate("Y-m-d H:i:s",time())." GMT<br />\n<u>From:</u> ".$_SERVER['REMOTE_ADDR']."<br />\n<br />\n<u>Project:</u> ".PROJECT."<br />\n<u>Module:</u> ".MODULE."<br />\n<u>Script:</u> ".SCRIPT."<br />\n<br />\n<u>DB Host:</u> ".DBHOST."<br />\n<u>DB Name:</u> ".DBNAME."<br />\n<u>DB Prefix:</u> ".PREFIX."<br />\n<br />\n<u>Name:</u> ".$gbl_info['name']."<br />\n<u>Contact:</u> ".$gbl_info['contact']."<br />\n<u>Other:</u> ".$gbl_info['other']."<br />\n<br />\n<u>Summary:</u> ".$gbl_errs['error']."<br />\n<u>Error:</u> (".$errno.") ".$errstr."<br />\n<u>Command:</u> ".$gbl_info['command']."<br />\n<u>Values:</u> ".$gbl_info['values']."<br />\n<u>File:</u> ".$errfile."<br />\n<u>Line:</u> ".$errline."<br />\n<br />\n<br />\nSincerely,<br />\n".PROJECT." Staff\n<br />\n<br />\nVar Dump:<br />\n</p>\n<pre>_POST\n".print_r($_POST, true)."</pre><br />\n<pre>_GET\n".print_r($_GET, true)."</pre><br />\n</td>\n<td>&nbsp;</td>\n</tr>\n</table>\n</body>\n</html>");
	if (isset($gbl_errs['continue']) && $gbl_errs['continue'] === TRUE) { return true; }	# continue script execution
	exit(1);
}

// Handles Exceptions
set_exception_handler("myExceptionHandler");
function myExceptionHandler($exception) {
#file_put_contents('debug.txt', get_class($exception)."\n", FILE_APPEND);
#file_put_contents('debug.txt', print_r($exception, true)."\n", FILE_APPEND);

	# https://www.php.net/manual/en/class.error.php
	# https://www.php.net/manual/en/function.gettype.php
	# https://www.php.net/manual/en/function.is-a.php
	# https://www.php.net/manual/en/function.get-class.php
	if (get_class($exception) == 'Error' || get_class($exception) == 'Exception')
		{ myErrorHandler($exception->getCode(), $exception->getMessage(), $exception->getFile(), $exception->getLine()); }
	else
		{ myErrorHandler($exception['code'], $exception['message'], $exception['file'], $exception['line']); }
}

// Handles fatal errors
register_shutdown_function('myShutdownHandler');
function myShutdownHandler() {
//	var_dump(error_get_last());

	$error = error_get_last();
	if ($error !== NULL) {					# ADDED 2021/05/12 - fixes an error after updating to php 7.4 from 7.2	https://stackoverflow.com/questions/277224/how-do-i-catch-a-php-fatal-e-error-error
		if ($error['type'] === E_ERROR) {
			// fatal error
			myErrorHandler(E_ERROR, $error['message'], $error['file'], $error['line']);
		}
	}
}




function genRandom($length = 40)				# generates random string of (40) characters (e.g. login SID, commerce SID, etc)
    { return substr(sha1(rand()), 0, $length); }		# also see http://stackoverflow.com/questions/853813/how-to-create-a-random-string-using-php




# Usage syntax:
# $key = Cipher::create_encryption_key();
# $val = 'Sri Lanka is a beautiful country!';
# $encrypted = Cipher::encrypt($val, $key); 
#    echo "Encrypted: ".$encrypted;
# $decrypted = Cipher::decrypt($encrypted, $key);
#    echo "Decrypted: ".$decrypted;
#
# NOTES
# https://stackoverflow.com/questions/4484246/encrypt-and-decrypt-text-with-rsa-in-php
# https://deliciousbrains.com/php-encryption-methods/
# https://www.zimuel.it/blog/strong-cryptography-in-php
# Simple sodium crypto class for PHP >= 7.2
# Author: MRK
class Cipher {
	# @return type
	static public function create_encryption_key()
		{ return base64_encode(sodium_crypto_secretbox_keygen()); }

	# Encrypt a value and return it!
	# $val	value to encrypt
	# $key	encryption key (via create_encryption_key())
	static function encrypt($val, $key) {
		if (is_null($val) || $val == '') { return ''; }			# if the passed value is blank, then no need to do any processing

		$key_decoded = base64_decode($key);
		$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

		$cipher = base64_encode($nonce . sodium_crypto_secretbox($val, $nonce, $key_decoded));
		sodium_memzero($val);
		sodium_memzero($key_decoded);
		return $cipher;
	}

	# Decrypt a value and return it!
	# $val - value to decrypt
	# $key - encryption key
	static function decrypt($val, $key) {
		if (is_null($val) || $val == '') { return ''; }			# if the passed value is blank, then no need to do any processing

		$decoded = base64_decode($val);
		$key_decoded = base64_decode($key);

		if ($decoded === false) { throw new Exception('Error: The encoding failed.'); }
		if (mb_strlen($decoded, '8bit') < (SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES))
			{ throw new Exception('Error: The message was truncated.'); }

		$nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
		$ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');

		$plain = sodium_crypto_secretbox_open($ciphertext, $nonce, $key_decoded);
		if ($plain === false)
			{ throw new Exception('Error: The message was tampered with in transit.'); }
		sodium_memzero($ciphertext);
		sodium_memzero($key_decoded);
		return $plain;
	}
}




function loadUser($intTimeout,$strPopulate,$strDBPerm,$strFields,$strATable,$strMField,$strMValue,$strVField='',$strVValue='',$strChecks='',$strAlerts='',$strDBType='oop',$chrErrors='x') {
# Before processing any request by the user, call this function to validate and load the user account information.
# Can optionally perform any additional account checks on a per call basis.
# NOTE: upon a successful call of this function, $strFill will become populated.
#	you need to also perform any value validation for the passed variables *before* calling this routine as it does *not* perform these checks!
# intTimeout	whether or not this function should check if the users session has timed out or not: 0=no, 1=yes
# strPopulate	if an array is passed, it will be populated with the DB search results; if a variable was passed, the dataset is returned
# strDBPerm	the permissions given to the SQL DB access; valid values: ro (readonly), rw (read/write)
# strFields	the fields (comma separated) to store in 'strPopulate' from the database; '*' for all fields in the row
# strATable	the name of the DB table containing the field/column used to IDENTIFY and optionally VALIDATE the users (a)ccount
# strMField	the field/column name in strATable used to (m)atch against (typically 'username' or 'id' when using this field as part of the authentication process)	WARNING: the values in this field/column MUST be unique!
# strMValue	the UNIQUE value of strMField to isolate a SINGLE record	NOTE: a SQL value identifier will need to preceed the value [b,d,i,s] such as "'i|'.$_POST['id']" or "'s|'.$_POST['username']"
# strVField	the field/column name in strVTable used to (v)alidate the user is who they say they are (typically 'sid'); blank value disables
# strVValue	the value to match within strVField (to permit comma separated lists; 10 contiguous character minimum match)	NOTE: a SQL value identifier will need to be part of this value too like strMValue
# strChecks	pipe separated checks to perform on the users account before exiting this function (e.g. "\$gbl_user['disabled']<1|\$gbl_user['status']=='active'|...")	NOTE: if using a variable in this value, you MUST escape the dollar sign ($)!
# strAlerts	pipe separated alerts that correspond to the associated strCheck and get processed upon FAILURE (e.g. "Your account is disabled so...|Your account is not active so...|...")
#		NOTE: the above two options are ONLY available if strPopulate is an array!
# strDBType	the type of connection: pro(cedural), oop (object oriented programming)
# chrErrors	defines how errors should be handled; valid values: (a)rray, (x)ml, (h)tml, blank value disables output
	global $linkDB,$gbl_emailCrackers,$gbl_nameCrackers,$gbl_emailHackers,$gbl_nameHackers,$gbl_emailNoReply,$gbl_uriProject,$gbl_debug,$gbl_errs,$gbl_info,$gbl_user;
	$results = '';

	# If we need to check that the users session has not timed out, then...
	if ($intTimeout && TIMEOUT > 0) {
		$epoch = time();		# obtain the current time for comparison below

		if (! array_key_exists('Time', $_SESSION)) {			# if the session time doesn't exist, then...
			if ($chrErrors == 'a') { $gbl_errs[] = "It appears that your session has timed out, please login again before continuing."; }
			else if ($chrErrors == 'x') { echo "<f><msg>It appears that your session has timed out, please login again before continuing.</msg></f>"; }
			else if ($chrErrors == 'h') { echo "<div class='divFail'>It appears that your session has timed out, please login again before continuing.</div>"; }
			return 0;
		} else if (($_SESSION['Time']+TIMEOUT) < $epoch) {		# if the users session has expired, then...
			if ($chrErrors == 'a') { $gbl_errs[] = "For security reasons your session has expired, please log in again before continuing."; }
			else if ($chrErrors == 'x') { echo "<f><msg>For security reasons your session has expired, please log in again before continuing.</msg></f>"; }
			else if ($chrErrors == 'h') { echo "<div class='divFail'>For security reasons your session has expired, please log in again before continuing.</div>"; }
			return 0;
		}

		if ($chrErrors == 'a' && $gbl_debug) { $gbl_info[] = "DEBUG: Updating the users session time so they may continue to interact with our project."; }
		else if ($chrErrors == 'x' && $gbl_debug) { echo "<i><msg>DEBUG: Updating the users session time so they may continue to interact with our project.</msg></i>"; }
		else if ($chrErrors == 'h' && $gbl_debug) { echo "<div class='divInfo'>DEBUG: Updating the users session time so they may continue to interact with our project.</div>"; }
		$_SESSION['Time'] = $epoch;					# actually update the session time
	}

	# Now lets connect to the DB to process the user account
	if ($strDBPerm == 'ro') 
   		{ if (! connect2DB(DBHOST,DBNAME,DBUNRO,DBPWRO,$strDBType,$chrErrors)) { return 0; } }	# the connect2DB has its own error handling so we don't need to do it here!
	else if ($strDBPerm == 'rw') 
		{ if (! connect2DB(DBHOST,DBNAME,DBUNRW,DBPWRW,$strDBType,$chrErrors)) { return 0; } }

	# Time to locate and store the users account info into $gbl_user (if the optional validation checks out)
	$gbl_errs['error'] = "The users account could not be found or validated in the DB.";
	if ($strVField == '') {		# IF we do NOT need to validate the users account, then...	NOTE: added 'LIMIT 1' so the query doesn't continue searching after the account has been found
		$gbl_info['command'] = "SELECT ".$strFields." FROM ".$strATable." WHERE ".$strMField."=? LIMIT 1";
		$gbl_info['values'] = '['.substr($strMValue,0,1).'] '.substr($strMValue,2);
		$stmt = $linkDB->prepare($gbl_info['command']);
		$stmt->bind_param(substr($strMValue,0,1), $match);
		$match = substr($strMValue,2);					# WARNING: we can NOT pass the substr() call as the parameter value
	} else {			# OTHERWISE we need to validate as well as locate the users account, so...
		$gbl_info['command'] = "SELECT ".$strFields." FROM ".$strATable." WHERE ".$strMField."=? AND ".$strVField."=? LIMIT 1";
		$gbl_info['values'] = '['.substr($strMValue,0,1).'] '.substr($strMValue,2).', ['.substr($strVValue,0,1).'] '.substr($strVValue,2);
		$stmt = $linkDB->prepare($gbl_info['command']);
		$stmt->bind_param(substr($strMValue,0,1).substr($strVValue,0,1), $match, $value);
		$match = substr($strMValue,2);					# WARNING: we can NOT pass the substr() call as the parameter values
		$value = substr($strVValue,2);
	}
	$stmt->execute();
  	if (gettype($$strPopulate) == 'array')					# if we need to return a populated array, then...
		{ $$strPopulate += $stmt->get_result()->fetch_assoc(); }	#   populate the array now (adding to it if values already exists)
	else									# otherwise we only need the dataset returned, so...
		{ $$strPopulate = $stmt->get_result(); return 1; }

	if (! $$strPopulate) {							# IF no information was found in the above query, then...
		if ($chrErrors == 'a') { $gbl_errs[] = "There was a problem processing your request. If you are attempting to login, please check your spelling and try again. Otherwise, try logging out and back in as your credentials may be stale."; }
		else if ($chrErrors == 'x') { echo "<f><msg>There was a problem processing your request. If you are attempting to\nlogin, please check your spelling and try again. Otherwise, try logging\nout and back in as your credentials may be stale.</msg></f>"; }
		else if ($chrErrors == 'h') { echo "<div class='divFail'>There was a problem processing your request. If you are attempting to login, please check your spelling and try again. Otherwise, try logging out and back in as your credentials may be stale.</div>"; }

		$$strPopulate=array();						# blank out the $gbl_user array since we've errored out
		return 0;
	}

	# Now lets process any checks that have been passed
	if ($strChecks != '') {
		$checks = explode("|", $strChecks);				# separate the checks and corresponding alerts
		$alerts = explode("|", $strAlerts);

		for ($i=0; $i<count($checks); $i++) {
			if (eval("return ".$checks[$i].";")) {
				if ($chrErrors == 'a') { $gbl_errs[] = $alerts[$i]; }
				else if ($chrErrors == 'x') { echo "<f><msg>".$alerts[$i]."</msg></f>"; }
				else if ($chrErrors == 'h') { echo "<div class='divFail'>".$alerts[$i]."</div>"; }

				$$strPopulate=array();				# blank out the $gbl_user array since we've errored out
				return 0;
			}
		}
	}

	if ($chrErrors == 'a' && $gbl_debug) { $gbl_info[] = "DEBUG: The SQL database request you made was successful!"; }
	else if ($chrErrors == 'x' && $gbl_debug) { echo "<i><msg>DEBUG: The SQL database request you made was successful!</msg></i>"; }
	else if ($chrErrors == 'h' && $gbl_debug) { echo "<div class='divInfo'>DEBUG: The SQL database request you made was successful!</div>"; }
	return 1;				# return success if we've made it down here
}




function connect2DB($strServer,$strDB,$strUser,$strPass,$strType='oop',$chrErrors='x') {
# Connects to the requested database for further interaction via this script
# strServer	the FQDN of the MySQL server to connect to
# strDB	the name of the MySQL database to open
# strUser	the name of the user account with sufficient priviledges to interact with the DB
# strPass	the password for the strUser
# strType	the type of connection: pro(cedural), oop (object oriented programming)
# chrErrors	defines how errors should be handled; valid values: (a)rray, (x)ml, (h)tml, blank value disables output
	global $linkDB,$gbl_errs,$gbl_info,$gbl_debug,$gbl_emailCrackers,$gbl_nameCrackers,$gbl_emailNoReply;

	if ($strServer == '' || $strUser == '' || $strPass == '') {
		# NOTE: we didn't add a sendMail call here since this should ONLY be shown during development
		if ($chrErrors == 'h')
			{ print "<div class='divFail'>There was an error processing your request and our staff has been notified.  Please try again in a few minutes.</div>\n"; return 0; }
		else if ($chrErrors == 'x')
			{ echo "<f><msg>There was an error processing your request and our staff has been notified.  Please try again in a few minutes.</msg></f>"; return 0; }
		else if ($chrErrors == 'a')
			{ $gbl_errs[] = "There was an error processing your request and our staff has been notified.  Please try again in a few minutes."; return 0; }
	}
	if ($chrErrors == 'a' && $gbl_debug) { $gbl_info[] = "DEBUG: All SQL Server authentication information has been passed."; }
	else if ($chrErrors == 'x' && $gbl_debug) { echo "<i><msg>DEBUG: All SQL Server authentication information has been passed.</msg></i>"; }
	else if ($chrErrors == 'h' && $gbl_debug) { echo "<div class='divInfo'>DEBUG: All SQL Server authentication information has been passed.</div>"; }

	if ($strDB == '') {
		# NOTE: we didn't add a sendMail call here since this should ONLY be shown during development
		if ($chrErrors == 'h')
			{ print "<div class='divFail'>There was an error processing your request and our staff has been notified.  Please try again in a few minutes.</div>\n"; return 0; }
		else if ($chrErrors == 'x')
			{ echo "<f><msg>There was an error processing your request and our staff has been notified.  Please try again in a few minutes.</msg></f>"; return 0; }
		else if ($chrErrors == 'a')
			{ $gbl_errs[] = "There was an error processing your request and our staff has been notified.  Please try again in a few minutes."; return 0; }
	}
	if ($chrErrors == 'a' && $gbl_debug) { $gbl_info[] = "DEBUG: All SQL Server database name has been passed."; }
	else if ($chrErrors == 'x' && $gbl_debug) { echo "<i><msg>DEBUG: All SQL Server database name has been passed.</msg></i>"; }
	else if ($chrErrors == 'h' && $gbl_debug) { echo "<div class='divInfo'>DEBUG: All SQL Server database name has been passed.</div>"; }

	# WARNING: do NOT implement this because it will not allow silencing of errors -AT ALL- for better error handling!!!
	# mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);		# turn on MySQLi exception mode to report all errors	https://stackoverflow.com/questions/22662488/how-to-get-mysqli-error-information-in-different-environments-mysqli-fetch-as/22662582#22662582

	$gbl_errs['error'] = "An error has occurred while attempting to connect to the DB server.";	# https://stackoverflow.com/questions/15707696/new-mysqli-vs-mysqli-connect
	if ($strType == 'pro') {
		$gbl_info['command'] = "mysqli_connect('p:".$strServer."','".$strUser."','****','".$strDB."')";
		$linkDB = mysqli_connect('p:'.$strServer,$strUser,$strPass,$strDB);			# need to pass: mysqli_connect_errno() & mysqli_connect_error() ?
	} else if ($strType == 'oop') {
		$gbl_info['command'] = "new mysqli('p:".$strServer."','".$strUser."','****','".$strDB."')";
		$linkDB = new mysqli('p:'.$strServer,$strUser,$strPass,$strDB);
	}
	if ($chrErrors == 'a' && $gbl_debug) { $gbl_info[] = "DEBUG: Connection to the SQL Server has been made successfully!"; }
	else if ($chrErrors == 'x' && $gbl_debug) { echo "<i><msg>DEBUG: Connection to the SQL Server has been made successfully!</msg></i>"; }
	else if ($chrErrors == 'h' && $gbl_debug) { echo "<div class='divInfo'>DEBUG: Connection to the SQL Server has been made successfully!</div>"; }

	return 1;
}




# Usage syntax:
#	validate(variable,14,'{red|green|blue}')		this allows up to a 14 character string with the only values being red, green, or blue
#	validate(variable,8,'![a-z0-9]')			this checks that it does NOT contain lowercase letters and numbers (by comparing against disallowed characters)
#	validate(variable,10,'[^a-z0-9]')			this checks that it only contains lowercase letters and numbers (by comparing against those disallowed characters)
#	validate(variable,25,'^[a-z]*$')			this checks for values under 25 characters and that it only contains lowercase letters (by comparing against allowed characters)
#	validate(variable,128,'*')				this checks for values under 128 characters, but allows any characters to be submitted
# NOTE: can be used like: if (! validate($_POST['whatever'],14,'{red|green|blue}')) { FLAG=1; }
function validate($strValue,$intLength,$strMatch,$chrErrors='x') {		# https://stackoverflow.com/questions/9166914/using-default-arguments-in-a-function
# validates data against the strMatch value
# strValue	the data to be validated (e.g. $_POST['username'])
# intLength	the string length that must not be exceeded by the strValue
# strMatch	a list of values or a regular expression to match against
# chrErrors	defines how errors should be handled; valid values: (a)rray, (x)ml, (h)tml, blank value disables output
	global $gbl_errs,$gbl_emailCrackers,$gbl_nameCrackers,$gbl_emailNoReply,$gbl_uriProject;

	# if the passed value does NOT exist (e.g. $_POST['absent']), then exit this function
	if (! isset($strValue)) { return 1; }

	# check that the length of the passed value is less than what is allowed
	if (strlen($strValue) > $intLength) {
		if ($chrErrors == 'h') { print "<div class='divFail'>The \"".$strValue."\" value has more characters than are allowed (max = ".$intLength.").</div>\n"; }
		else if ($chrErrors == 'x') { echo "<f><msg>The \"".$strValue."\" value has more characters than are allowed (max = ".$intLength.").</msg></f>"; }
		else if ($chrErrors == 'a') { $gbl_errs[] = "The \"".$strValue."\" value has more characters than are allowed (max = ".$intLength.")."; }

		sendMail($gbl_emailCrackers,$gbl_nameCrackers,$gbl_emailNoReply,SCRIPT.' Script','*** Possible Cracking Attempt ***',"<html>\n<body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' offset='0' bgcolor='#ffffff'>\n<table width='100%'>\n<tr>\n<td>&nbsp;</td>\n<td width='500'>\n<img src='".$gbl_uriProject."home/guest/imgs/email_alert.png' border='0' style='float:right; padding-left: 5px;' />\n<h1 style='padding: 50px 0 10px 0; font-size: 32px; font-variant: small-caps; color: #92bfe5;'>".PROJECT."</h1>\n<h2 style='margin-bottom: 5px; font: 12pt verdana bold; color: #808080;'>Possible Cracking Attempt</h2><br />\n<p style='font: 12px/17px verdana; color: #808080; text-align: justify;'>\nTeam,<br />\n<br />\nOne of the users was interacting with our '".PROJECT."' project, but encountered the listed error in the process of doing so.  Please investigate and correct this problem as soon as possible.  If the problem warrants contacting the end user, please do so as well by referencing the relevant information below:<br />\n<br />\n<br />\nProject: ".PROJECT."<br />\nModule: ".MODULE."<br />\nScript: ".SCRIPT."<br />\nFunction: validate<br />\n<br />Calling Script: ".SCRIPT."<br />\n<br />\n\nDB Host: ".DBHOST."<br />\nDB Name: ".DBNAME."<br />\nDB Prefix: ".PREFIX."<br />\nIP Address: ".$_SERVER['REMOTE_ADDR']."<br />\n<br />\nOur Error: A user is attempting to pass too large of a value during validation.<br />\n<br />\nMax Length: ".$intLength." characters<br />\nProcessed Value: ".$strValue."<br />\nVar Dump:<br />\n".print_r($_GET, true)."<br />\n".print_r($_POST, true)."<br />\n</p>\n</td>\n<td>&nbsp;</td>\n</tr>\n<table>\n</body>\n</html>");
		return 0;
	}

	# remove any RegEx formatting so we can isolate just the characters that are (dis)allowed
	$accepted = preg_replace('/^\!\[|^\^\[|^\[\^|^\[|\]\+\$$|\]\*\$$|\]\$$|\$\]$|\]$/', '', $strMatch);

	# adjust the values so the display is readable for the user if an error occurs below
	$accepted = str_replace('0-9', '0-9, ', $accepted);
	$accepted = str_replace('A-Z', 'A-Z, ', $accepted);
	$accepted = str_replace('a-z', 'a-z, ', $accepted);
	if (strrpos($accepted, ', ') == strlen($accepted)-2)	# if one of the above replacements occured, but there are no trailing single characters, then erase the ', ' postfix
		{ $accepted = substr($accepted, 0, strlen($accepted)-2); }
	else if (strpos($accepted, ', ') > -1)			# if one of the above replacements occured, then comma separate all following single characters
		{ $accepted = substr($accepted, 0, strrpos($accepted, ', ')) . ', ' . implode(', ', str_split(substr($accepted, strrpos($accepted, ', ')+2))); }
	else							# otherwise we have no groups of characters, so separate them all as single characters
		{ $accepted = implode(', ', str_split($accepted)); }

	# now check that the value entered contains legal characters
	# https://stackoverflow.com/questions/1735972/php-fastest-way-to-check-for-invalid-characters-all-but-a-z-a-z-0-9
	# https://stackoverflow.com/questions/1972100/getting-the-first-character-of-a-string-with-str0
	if ($strMatch[0] == '[') {				# if we need to process valid, allowed characters, then...
		if (preg_match('/'.$strMatch.'/', $strValue)) {
			if ($chrErrors == 'h') { print "<div class='divFail'>There is an invalid character in the \"".$strValue."\" value. The allowed characters are: ".$accepted."</div>\n"; }
			else if ($chrErrors == 'x') { echo "<f><msg>There is an invalid character in the \"".$strValue."\" value. The allowed characters are: ".$accepted."</msg></f>"; }
			else if ($chrErrors == 'a') { $gbl_errs[] = "There is an invalid character in the \"".$strValue."\" value. The allowed characters are: ".$accepted; }

			sendMail($gbl_emailCrackers,$gbl_nameCrackers,$gbl_emailNoReply,SCRIPT.' Script','*** Possible Cracking Attempt ***',"<html>\n<body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' offset='0' bgcolor='#ffffff'>\n<table width='100%'>\n<tr>\n<td>&nbsp;</td>\n<td width='500'>\n<img src='".$gbl_uriProject."home/guest/imgs/email_alert.png' border='0' style='float:right; padding-left: 5px;' />\n<h1 style='padding: 50px 0 10px 0; font-size: 32px; font-variant: small-caps; color: #92bfe5;'>".PROJECT."</h1>\n<h2 style='margin-bottom: 5px; font: 12pt verdana bold; color: #808080;'>Possible Cracking Attempt</h2><br />\n<p style='font: 12px/17px verdana; color: #808080; text-align: justify;'>\nTeam,<br />\n<br />\nOne of the users was interacting with our '".PROJECT."' project, but encountered the listed error in the process of doing so.  Please investigate and correct this problem as soon as possible.  If the problem warrants contacting the end user, please do so as well by referencing the relevant information below:<br />\n<br />\n<br />\nProject: ".PROJECT."<br />\nModule: ".MODULE."<br />\nScript: ".SCRIPT."<br />\nFunction: validate<br />\n<br />Calling Script: ".SCRIPT."<br />\n<br />\n\nDB Host: ".DBHOST."<br />\nDB Name: ".DBNAME."<br />\nDB Prefix: ".PREFIX."<br />\nIP Address: ".$_SERVER['REMOTE_ADDR']."<br />\n<br />\nOur Error: A user is attempting to pass a value containings illegal characters during validation.<br />\n<br />\nAllowed Characters: ".$strMatch."<br />\nProcessed Value: ".$strValue."<br />\nVar Dump:<br />\n".print_r($_GET, true)."<br />\n".print_r($_POST, true)."<br />\n</p>\n</td>\n<td>&nbsp;</td>\n</tr>\n<table>\n</body>\n</html>");
			return 0;
		}

	} else if ($strMatch[0] == '^') {					# if we need to process valid, allowed characters, then...
		if (preg_match('/'.$strMatch.'/', $strValue) === 0) {
			if ($chrErrors == 'h') { print "<div class='divFail'>There is an invalid character in the \"".$strValue."\" value. The allowed characters are: ".$accepted."</div>\n"; }
			else if ($chrErrors == 'x') { echo "<f><msg>There is an invalid character in the \"".$strValue."\" value. The allowed characters are: ".$accepted."</msg></f>"; }
			else if ($chrErrors == 'a') { $gbl_errs[] = "There is an invalid character in the \"".$strValue."\" value. The allowed characters are: ".$accepted; }

			sendMail($gbl_emailCrackers,$gbl_nameCrackers,$gbl_emailNoReply,SCRIPT.' Script','*** Possible Cracking Attempt ***',"<html>\n<body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' offset='0' bgcolor='#ffffff'>\n<table width='100%'>\n<tr>\n<td>&nbsp;</td>\n<td width='500'>\n<img src='".$gbl_uriProject."home/guest/imgs/email_alert.png' border='0' style='float:right; padding-left: 5px;' />\n<h1 style='padding: 50px 0 10px 0; font-size: 32px; font-variant: small-caps; color: #92bfe5;'>".PROJECT."</h1>\n<h2 style='margin-bottom: 5px; font: 12pt verdana bold; color: #808080;'>Possible Cracking Attempt</h2><br />\n<p style='font: 12px/17px verdana; color: #808080; text-align: justify;'>\nTeam,<br />\n<br />\nOne of the users was interacting with our '".PROJECT."' project, but encountered the listed error in the process of doing so.  Please investigate and correct this problem as soon as possible.  If the problem warrants contacting the end user, please do so as well by referencing the relevant information below:<br />\n<br />\n<br />\nProject: ".PROJECT."<br />\nModule: ".MODULE."<br />\nScript: ".SCRIPT."<br />\nFunction: validate<br />\n<br />Calling Script: ".SCRIPT."<br />\n<br />\n\nDB Host: ".DBHOST."<br />\nDB Name: ".DBNAME."<br />\nDB Prefix: ".PREFIX."<br />\nIP Address: ".$_SERVER['REMOTE_ADDR']."<br />\n<br />\nOur Error: A user is attempting to pass a value containings illegal characters during validation.<br />\n<br />\nAllowed Characters: ".$strMatch."<br />\nProcessed Value: ".$strValue."<br />\nVar Dump:<br />\n".print_r($_GET, true)."<br />\n".print_r($_POST, true)."<br />\n</p>\n</td>\n<td>&nbsp;</td>\n</tr>\n<table>\n</body>\n</html>");
			return 0;
		}

	} else if ($strMatch[0] == '!') {					# if we need to process invalid, illegal characters, then...
		if (preg_match('/'.substr($strMatch,1).'/', $strValue) === 1) {
			if ($chrErrors == 'h') { print "<div class='divFail'>There is an invalid character in the \"".$strValue."\" value. The disallowed characters are: ".$accepted."</div>\n"; }
			else if ($chrErrors == 'x') { echo "<f><msg>There is an invalid character in the \"".$strValue."\" value. The disallowed characters are: ".$accepted."</msg></f>"; }
			else if ($chrErrors == 'a') { $gbl_errs[] = "There is an invalid character in the \"".$strValue."\" value. The disallowed characters are: ".$accepted; }

			sendMail($gbl_emailCrackers,$gbl_nameCrackers,$gbl_emailNoReply,SCRIPT.' Script','*** Possible Cracking Attempt ***',"<html>\n<body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' offset='0' bgcolor='#ffffff'>\n<table width='100%'>\n<tr>\n<td>&nbsp;</td>\n<td width='500'>\n<img src='".$gbl_uriProject."home/guest/imgs/email_alert.png' border='0' style='float:right; padding-left: 5px;' />\n<h1 style='padding: 50px 0 10px 0; font-size: 32px; font-variant: small-caps; color: #92bfe5;'>".PROJECT."</h1>\n<h2 style='margin-bottom: 5px; font: 12pt verdana bold; color: #808080;'>Possible Cracking Attempt</h2><br />\n<p style='font: 12px/17px verdana; color: #808080; text-align: justify;'>\nTeam,<br />\n<br />\nOne of the users was interacting with our '".PROJECT."' project, but encountered the listed error in the process of doing so.  Please investigate and correct this problem as soon as possible.  If the problem warrants contacting the end user, please do so as well by referencing the relevant information below:<br />\n<br />\n<br />\nProject: ".PROJECT."<br />\nModule: ".MODULE."<br />\nScript: ".SCRIPT."<br />\nFunction: validate<br />\n<br />Calling Script: ".SCRIPT."<br />\n<br />\n\nDB Host: ".DBHOST."<br />\nDB Name: ".DBNAME."<br />\nDB Prefix: ".PREFIX."<br />\nIP Address: ".$_SERVER['REMOTE_ADDR']."<br />\n<br />\nOur Error: A user is attempting to pass a value containings illegal characters during validation.<br />\n<br />\nDisallowed Characters: ".$strMatch."<br />\nProcessed Value: ".$strValue."<br />\nVar Dump:<br />\n".print_r($_GET, true)."<br />\n".print_r($_POST, true)."<br />\n</p>\n</td>\n<td>&nbsp;</td>\n</tr>\n<table>\n</body>\n</html>");
			return 0;
		}

	} else if ($strMatch[0] == '{') {					# if we need to process valid, allowed values, then...
		$values = explode("|", substr($strMatch, 1, -1));
		$found = 0;

		if ($strValue == '') { $found = 1; }				# if no value was passed for this field, then mark it as being ok
		foreach ($values as $value)					# cycle each allowed value to see if the one passed matches one of them
			{ if ($value == $strValue) {$found = 1; break;} }

		if (! $found) {							# if it does NOT, then...
			if ($chrErrors == 'h') { print "<div class='divFail'>The \"".$strValue."\" value does not match any allowed.</div>\n"; }
			else if ($chrErrors == 'x') { echo "<f><msg>The \"".$strValue."\" value does not match any allowed.</msg></f>"; }
			else if ($chrErrors == 'a') { $gbl_errs[] = "The \"".$strValue."\" value does not match any allowed."; }

			sendMail($gbl_emailCrackers,$gbl_nameCrackers,$gbl_emailNoReply,SCRIPT.' Script','*** Possible Cracking Attempt ***',"<html>\n<body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' offset='0' bgcolor='#ffffff'>\n<table width='100%'>\n<tr>\n<td>&nbsp;</td>\n<td width='500'>\n<img src='".$gbl_uriProject."home/guest/imgs/email_alert.png' border='0' style='float:right; padding-left: 5px;' />\n<h1 style='padding: 50px 0 10px 0; font-size: 32px; font-variant: small-caps; color: #92bfe5;'>".PROJECT."</h1>\n<h2 style='margin-bottom: 5px; font: 12pt verdana bold; color: #808080;'>Possible Cracking Attempt</h2><br />\n<p style='font: 12px/17px verdana; color: #808080; text-align: justify;'>\nTeam,<br />\n<br />\nOne of the users was interacting with our '".PROJECT."' project, but encountered the listed error in the process of doing so.  Please investigate and correct this problem as soon as possible.  If the problem warrants contacting the end user, please do so as well by referencing the relevant information below:<br />\n<br />\n<br />\nProject: ".PROJECT."<br />\nModule: ".MODULE."<br />\nScript: ".SCRIPT."<br />\nFunction: validate<br />\n<br />Calling Script: ".SCRIPT."<br />\n<br />\n\nDB Host: ".DBHOST."<br />\nDB Name: ".DBNAME."<br />\nDB Prefix: ".PREFIX."<br />\nIP Address: ".$_SERVER['REMOTE_ADDR']."<br />\n<br />\nOur Error: A user is attempting to pass a value not permitted during validation.<br />\n<br />\nAllowed Values: ".substr($strMatch, 1, -1)."<br />\nProcessed Value: ".$strValue."<br />\nVar Dump:<br />\n".print_r($_GET, true)."<br />\n".print_r($_POST, true)."<br />\n</p>\n</td>\n<td>&nbsp;</td>\n</tr>\n<table>\n</body>\n</html>");
			return 0;
		}
	}

	return 1;								# if we've made it here, then everything checked out just fine
}




# Usage syntax:
# $variable = charSwap($_POST['obj'],'in');	makes the values safe for processing
# $variable = charSwap($_POST['obj'],'out');	reverts the values back to their original form
function charSwap(&$strValue,$strFlow) {
# Useful to swap a few characters to help prevent malicious SQL injections/attacks
	if ($strFlow == 'in') {							# remove malicious syntax
		$strValue = str_replace('=',chr(0xB1),$strValue);
		$strValue = str_replace('<',chr(0xAB),$strValue);
		$strValue = str_replace('>',chr(0xBB),$strValue);
		$strValue = str_replace(';',chr(0xA1),$strValue);
	} else if ($strFlow == 'out') {						# convert the prior values back to the originals
		$strValue = str_replace(chr(0xB1),'=',$strValue);
		$strValue = str_replace(chr(0xAB),'<',$strValue);
		$strValue = str_replace(chr(0xBB),'>',$strValue);
		$strValue = str_replace(chr(0xA1),';',$strValue);
	}
	return $strValue;
}




# Usage syntax:
# $variable = safeXML($_POST['obj']);
function safeXML(&$strValue) {
# This function makes the passed value safe for XML transmission (e.g. & > &amp;)
	$strValue = str_replace('<','&lt;',$strValue);				# these make any saved "<pre>" tags work correctly
	$strValue = str_replace('>','&gt;',$strValue);
	$strValue = str_replace('&','&amp;',$strValue);				# used for syntax friendly xml (HAS to come last)
	$strValue = str_replace('"','&quot;',$strValue);
	$strValue = str_replace("'",'&apos;',$strValue);
	return $strValue;
}




function sendMail($strToEmail,$strToName,$strFromEmail,$strFromName,$strSubject,$strMsg) {
# A wrapper script that sets the appropriate variables then sends the MIME encoded email
# strToEmail	the 'To' email address of the contact
# strToName	the name of the person/group that is receiving the email
# strFromEmail	the 'From' email address of the sender
# strFromName	the name of the person/group that is sending the email
# strSubject	the subject of the email
# strMsg	the message/body of the email
	global $gbl_dirLogs,$gbl_logEmail,$gbl_uriContact;

	$mail = new mime_mail();
	if ($strFromName != '') { $mail->from = '"'.$strFromName.'" <'.$strFromEmail.'>'; } else { $mail->from = $strFromEmail; }
	$mail->cc = "";								# $_POST["cc"];
	$mail->headers = "Errors-To: support@".$gbl_uriContact;			# WARNING: this line can NOT have a trailing '\n' as it will cause problems with the _mimemail.php script!
	if ($strFromName != '') { $mail->to = '"'.$strToName.'" <'.$strToEmail.'>'; } else { $mail->to = $strToEmail; }
	$mail->subject = $strSubject;
	$mail->body = $strMsg;

	if( $mail->send() ) {
		if ($EML = fopen("$gbl_dirLogs/$gbl_logEmail",'a')) { fwrite($EML,"Email successfully sent to user '".$strToName."' on ".date("m/d/Y H:i:s",time()).".\n"); }
		return 1;
	} else {
		if ($EML = fopen("$gbl_dirLogs/$gbl_logEmail",'a')) { fwrite($EML,"Couldn't send the email to user '".$strToName."' on ".date("m/d/Y H:i:s",time()).".\n"); }
		return 0;
	}
}




function verifyEmail($strSearchCol,$strSearchVal,$strReplaceCol,$strReplaceVal,$strName,$strEmail,$strTable,$chrErrors) {
# Creates a dynamically named .php file that will be used to perform validation of an accounts' email address
# NOTE: this also CHANGES a database column/field value called 'status' equal to 'active' upon success, no
#	change otherwise.
# strSearchCol	the database column/field name to search for the users account in
# strSearchVal	the UNIQUE value that will identify the users account (e.g. primary key, username, email, etc)
# strReplaceCol	the database column/field name to adjust upon success (e.g. disabled)
# strReplaceVal	the value to change the strReplaceCol to
# strName	the real name, username, alias, etc that the user is known by; this is part of the email 'To' field
# strEmail	the email address associated with the users account
# strTable	the table containing the user accounts
# chrErrors	defines how errors should be handled; valid values: (a)rray, (x)ml, (h)tml, blank value disables output
	global $linkDB,$gbl_nameHackers,$gbl_emailHackers,$gbl_emailNoReply,$gbl_uriContact,$gbl_uriProject,$gbl_dirVerify,$gbl_errs;

	while (1) {								# make sure not to overwrite an existing verification script
		$random = rand(1000,1000000000);
		if (! file_exists("$gbl_dirVerify/$random.php")) { break; }
	}

	if (! $VPL = fopen("$gbl_dirVerify/$random.php",'w')) {
		sendMail($gbl_emailHackers,$gbl_nameHackers,$gbl_emailNoReply,SCRIPT.' script','*** Script Execution Error ***',"<html>\n<body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' offset='0' bgcolor='#ffffff'>\n<table width='100%'>\n<tr>\n<td>&nbsp;</td>\n<td width='500'>\n<img src='".$gbl_uriProject."home/guest/imgs/email_error.png' border='0' style='float:right; padding-left: 5px;' />\n<h1 style='padding: 50px 0 10px 0; font-size: 32px; font-variant: small-caps; color: #92bfe5;'>".PROJECT."</h1>\n<h2 style='margin-bottom: 5px; font: 12pt verdana bold; color: #808080;'>Script Execution Error</h2><br />\n<p style='font: 12px/17px verdana; color: #808080; text-align: justify;'>\nTeam,<br />\n<br />\nOne of the users was interacting with our '".PROJECT."' project, but encountered the listed error in the process of doing so.  Please investigate and correct this problem as soon as possible.  If the problem warrants contacting the end user, please do so as well by referencing the relevant information below:<br />\n<br />\n<br />\nProject: ".PROJECT."<br />\nModule: ".MODULE."<br />\nScript: ".SCRIPT."<br />\nFunction: verifyEmail<br />\n<br />Calling Script: ".SCRIPT."<br />\n<br />\n\nDB Host: ".DBHOST."<br />\nDB Name: ".DBNAME."<br />\nDB Prefix: ".PREFIX."<br />\n<br />\nOur Error: The random-named php script could not be created on the server.<br />\nExec Error: ".print_r(error_get_last(), true)."<br />\n<br />\nVar Dump:<br />\nDirectory > ".$gbl_dirVerify.", Script > ".$random.".php<br />\n</p>\n</td>\n<td>&nbsp;</td>\n</tr>\n<table>\n</body>\n</html>");
		# NOTE: if the sendmail fails above, our staff will never know about this error!

		if ($chrErrors == 'h')
			{ print "<div class='divFail'>There was an error creating the verification script and our staff has been notified of the error.  Please try again in a couple of minutes.</div>\n"; return 0; }
		else if ($chrErrors == 'x')
			{ echo "<f><msg>There was an error creating the verification script and our staff has been notified of the error.  Please try again in a couple of minutes.</msg></f>"; return 0; }
		else if ($chrErrors == 'a')
			{ $gbl_errs[] = "There was an error creating the verification script and our staff has been notified of the error.  Please try again in a couple of minutes."; return 0; }
	} else {
		$html = '<?php
# Constant Definitions
define("MODULE","'.MODULE.'");
define("NAME","Verify");			# the actual name of this script
define("PREFIX","'.PREFIX.'");			# the prefix used with the database
define("SCRIPT","'.$random.'.php");

# Module Requirements
require_once("../../../sqlaccess");
require_once("../config.php");
require_once("../../code/_mimemail.php");
require_once("../../code/_global.php");

# Start or resume the PHP session
session_start();




$gbl_errs[\'continue\'] = TRUE;
if (! connect2DB(DBHOST,DBNAME,DBUNRW,DBPWRW)) {
	$color="#f00";
	$msg="Uh-oh!  Something went wrong so a message has been sent to the system administrator.  We appreciate your patience and apologize for the inconvenience.  You will be redirected back to the site in 15 seconds.\n";
} else {
	$gbl_errs[\'error\'] = "The user account can not be enabled in the database.";
	$gbl_errs[\'command\'] = "UPDATE ".PREFIX."'.$strTable.' SET '.$strReplaceCol.'=\''.$strReplaceVal.'\',status=\'active\' WHERE '.$strSearchCol.'=\''.$strSearchVal.'\'";
	if (! $linkDB->query($gbl_errs[\'command\'])) {
		$color="#f00";
		$msg="Uh-oh!  Something went wrong so a message has been sent to the system administrator.  We appreciate your patience and apologize for the inconvenience.  You will be redirected back to the site in 15 seconds.\n";
		sendMail($gbl_emailHackers,$gbl_nameHackers,$gbl_emailNoReply,"'.SCRIPT.' script","*** Script Execution Error ***","<html>\n<body topmargin=\'0\' leftmargin=\'0\' marginwidth=\'0\' marginheight=\'0\' offset=\'0\' bgcolor=\'#ffffff\'>\n<table width=\'100%\'>\n<tr>\n<td>&nbsp;</td>\n<td width=\'500\'>\n<img src=\''.$gbl_uriProject.'/home/guest/imgs/email_error.png\' border=\'0\' style=\'float:right; padding-left: 5px;\' />\n<h1 style=\'padding: 50px 0 10px 0; font-size: 32px; font-variant: small-caps; color: #92bfe5;\'>".PROJECT."</h1>\n<h2 style=\'margin-bottom: 5px; font: 12pt verdana bold; color: #808080;\'>Script Execution Error</h2><br />\n<p style=\'font: 12px/17px verdana; color: #808080; text-align: justify;\'>\nTeam,<br />\n<br />\nOne of the users was interacting with our \'".PROJECT."\' project, but encountered the listed error in the process of doing so.  Please investigate and correct this problem as soon as possible.  If the problem warrants contacting the end user, please do so as well by referencing the relevant information below:<br />\n<br />\n<br />\nProject: ".PROJECT."<br />\nModule: ".MODULE."<br />\nScript: ".SCRIPT."<br />\nFunction: verifyEmail<br />\n<br />\nDB Host: ".DBHOST."<br />\nDB Name: ".DBNAME."<br />\nDB Prefix: ".PREFIX."<br />\n<br />\nOur Error: An error occurred while connecting to the DB server or database itself.<br />\nSQL Error: ".mysqli_errno($linkDB).": ".mysqli_error($linkDB)."<br />\n<br />\nVar Dump:<br />\nSearch Key > '.$strSearchCol.', Search Val > '.$strSearchVal.', Replace Key > '.$strReplaceCol.', Replace Val > '.$strReplaceVal.', Target Name > '.$strName.', Target Email > '.$strEmail.'<br />\n</p>\n</td>\n<td>&nbsp;</td>\n</tr>\n<table>\n</body>\n</html>");
	} else {
		$color="#a9d260";
		$msg="\tCongrats!<br />\n\tYou have finished the registration process.  If this page does not redirect you in 15 seconds <a href=\"'.$gbl_uriProject.'\">click here</a>.\n";
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Registration Complete</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="refresh" content="15; '.$gbl_uriProject.'" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<link rel="stylesheet" href="home/guest/look/_global.css" type="text/css" />
<style type="text/css" media="screen">
	body {
		margin: 10px auto;
		width: 400px;
		font: 12px/13pt verdana;
		color: <?php print $color; ?>;
		background-color: #fff;
		cursor: default;
		text-align: justify;
	}
	h2 { margin-bottom: 20px; font-size: 14pt; color: #76a7dc; text-align: center; }
</style>
</head>
<body>
<div id="divBody">
	<center><h2>'.PROJECT.'</h2></center>
	<div class="center">
<?php print $msg; ?>
	</div>
</div>
</body>
<html>
<?php unlink("'.$random.'.php"); ?>';

		fwrite($VPL,$html);
		fclose($VPL);
		if (! sendMail($strEmail,$strName,$gbl_emailNoReply,PROJECT,'Account Verification',"<html>\n<body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' offset='0' bgcolor='#ffffff'>\n<table width='100%'>\n<tr>\n<td>&nbsp;</td>\n<td width='500'>\n<img src='".$gbl_uriProject."home/guest/imgs/email_verify.png' border='0' style='float:right; padding-left: 5px;' />\n<h1 style='padding: 50px 0 10px 0; font-size: 32px; font-variant: small-caps; color: #92bfe5;'>".PROJECT."</h1>\n<h2 style='margin-bottom: 5px; font: 12pt verdana bold; color: #808080;'>Verify Identity</h2>\n<p style='font: 12px/17px verdana; color: #808080; text-align: justify;'>\n".$strName.",<br />\n<br />\nThanks for taking the time to create an account with our website.  We know this process can be annoying, but it\nhelps us to ensure that we have a good means of contact for you.  To help streamline this process, we have\nincluded a link below to click on that will handle the rest of the verification steps.  Clicking that link will\nresult in making your account active immediately and providing you with full acccess so you can start interacting\nonline! Also, please note, your password will <u>never</u> be asked for by any staff member and should <u>not</u>\nbe given to anyone.<br />\n<br />\n<a href='".$gbl_uriProject."data/_verify/".$random.".php'>Click here to verify</a><br />\n<br />\nSincerely,<br />\n".PROJECT." Staff\n</p>\n</td>\n<td>&nbsp;</td>\n</tr>\n<table>\n</body>\n</html>")) {
			sendMail($gbl_emailHackers,$gbl_nameHackers,$gbl_emailNoReply,SCRIPT.' script','*** Script Execution Error ***',"<html>\n<body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' offset='0' bgcolor='#ffffff'>\n<table width='100%'>\n<tr>\n<td>&nbsp;</td>\n<td width='500'>\n<img src='".$gbl_uriProject."home/guest/imgs/email_error.png' border='0' style='float:right; padding-left: 5px;' />\n<h1 style='padding: 50px 0 10px 0; font-size: 32px; font-variant: small-caps; color: #92bfe5;'>".PROJECT."</h1>\n<h2 style='margin-bottom: 5px; font: 12pt verdana bold; color: #808080;'>Script Execution Error</h2><br />\n<p style='font: 12px/17px verdana; color: #808080; text-align: justify;'>\nTeam,<br />\n<br />\nOne of the users was interacting with our '".PROJECT."' project, but encountered the listed error in the process of doing so.  Please investigate and correct this problem as soon as possible.  If the problem warrants contacting the end user, please do so as well by referencing the relevant information below:<br />\n<br />\n<br />\nProject: ".PROJECT."<br />\nModule: ".MODULE."<br />\nScript: ".SCRIPT."<br />\nFunction: verifyEmail<br />\n<br />Calling Script: ".SCRIPT."<br />\n<br />\n\nDB Host: ".DBHOST."<br />\nDB Name: ".DBNAME."<br />\nDB Prefix: ".PREFIX."<br />\n<br />\nOur Error: The verification script could not be sent to the receipient.<br />\n<br />\nVar Dump:<br />\nDirectory > ".$gbl_dirVerify.", Script > ".$random.".php<br />\n</p>\n</td>\n<td>&nbsp;</td>\n</tr>\n<table>\n</body>\n</html>");
			# NOTE: if the sendmail fails above, our staff will never know about this error!

			if ($chrErrors == 'h')
				{ print "<div class='divFail'>There was an error sending the verification script and our staff has been notified of the error.  Please try again in a couple of minutes.</div>\n"; return 0; }
			else if ($chrErrors == 'x')
				{ echo "<f><msg>There was an error sending the verification script and our staff has been notified of the error.  Please try again in a couple of minutes.</msg></f>"; return 0; }
			else if ($chrErrors == 'a')
				{ $gbl_errs[] = "There was an error sending the verification script and our staff has been notified of the error.  Please try again in a couple of minutes."; return 0; }
		}
	}
	return 1;
}




function createThumbs($width,$height,$fqdnImage,$fqdnThumb) {
# used to create thumbnail images;	http://www.howtogeek.com/109369/how-to-quickly-resize-convert-modify-images-from-the-linux-terminal/
# used to create video thumbnails:	http://blog.amnuts.com/2007/06/22/create-a-random-thumbnail-of-a-video-file/
#					http://stackoverflow.com/questions/10240972/create-thumbnail-image-from-video-in-server-in-php
   $info = pathinfo($fqdnImage);				# obtain file info

   switch(strtolower($info['extension'])) {			# load image and get image size
#	case '3gp':						# for videos...
	case 'flv':
	case 'm4v':
#	case 'mp4':
#	case 'mpeg':
#	case 'mpg':
	case 'ogv':						# HAS PROBLEMS
	case 'webm':
	case 'webmv':
#	case 'wmv':
#	   system("ffmpeg -ss 3600 -i {$fqdnImage} -deinterlace -an -t 00:00:01 -r 1 -y -s 200x150 -vcodec mjpeg -f mjpeg {$fqdnThumb} 2>&1");
#	   system("ffmpeg -an -y -itsoffset -1 -vframes 1 -vcodec mjpeg -f rawvideo -s 200x150 -i {$fqdnImage} {$fqdnThumb}");
	   $ret = `ffmpeg -itsoffset -1 -i {$fqdnImage} -vcodec mjpeg -vframes 1 -an -f rawvideo -s {$width}x{$height} {$fqdnThumb}`;
	   return true;
	   break;

	case 'jpeg':						# for images...
	case 'jpg':
#	   $img = imagecreatefromjpeg( "{$fqdnImage}" );	# NOTE: replaced in favor of the 'identify' binary call below since I was having problems with larger image sizes
#	   break;
	case 'png':
#	   $img = imagecreatefrompng( "{$fqdnImage}" );
#	   break;
	case 'gif':
#	   $img = imagecreatefromgif( "{$fqdnImage}" );
	   $w = `identify -format "%[fx:w]" {$fqdnImage}`;	# obtain the size values of the picture
	   $h = `identify -format "%[fx:h]" {$fqdnImage}`;

	   $w = trim($w);					# remove any leading & trailing whitespace for proper calculations below
	   $h = trim($h);
	   break;

	default:						# skip all the other image/video types
#	   continue;
	   break;
   }

#   $width = imagesx($img);					# obtain the sizes of the image
#   $height = imagesy($img);

   # NOTE: adding an '!' to the end of the -resize value will force that size instead of preserving the aspect ratio

   if ($w == $h && $w > $width) {				# if the image is "square", then reduce to the smallest size needed
#file_put_contents('debug.txt', "top :convert {$fqdnImage} -resize ".$width."x".$height." {$fqdnThumb}:\n", FILE_APPEND);
	if ($width < $height)
	   { system("convert {$fqdnImage} -resize ".$width."x".$width." {$fqdnThumb}"); }
	else
	   { system("convert {$fqdnImage} -resize ".$height."x".$height." {$fqdnThumb}"); }

   } else if ($w > $h && $w > $width) {				# if we have a greater width, reduce keeping the aspect ratio
#file_put_contents('debug.txt', "mid\n", FILE_APPEND);
	system("convert {$fqdnImage} -resize ".$width." {$fqdnThumb}");

   } else if ($h > $w && $h > $height) {			# if we have a greater height, reduce to keep the aspect ratio
#file_put_contents('debug.txt', "btm\n", FILE_APPEND);
	system("convert {$fqdnImage} -resize x".$height." {$fqdnThumb}");

   } else if ($w == $width) {
#file_put_contents('debug.txt', "btm2\n", FILE_APPEND);
	system("convert {$fqdnImage} -resize x".$height." {$fqdnThumb}");

   } else if ($h == $height) {
#file_put_contents('debug.txt', "btm3\n", FILE_APPEND);
	system("convert {$fqdnImage} -resize ".$width." {$fqdnThumb}");

   } else {							# otherwise, the picture is smaller than the necessary size constraints, so just copy the image over
#file_put_contents('debug.txt', "else\n", FILE_APPEND);
	copy("{$fqdnImage}", "{$fqdnThumb}");
   }
}




function delTree($dir) {
	if (! file_exists($dir)) { return false; }

	$files = array_diff(scandir($dir), array('.','..'));	# didn't use 'glob' since 'scandir' sees hidden files
	foreach ($files as $file) {
		if (is_link("$dir/$file")) {			# if the target is a symlink, then...		NOTE: this prevented some problems with the 'rmdir' call at the bottom
			@unlink("$dir/$file");			#   delete the symlink
			continue;				#   continue to the next file or directory
		}
		(is_dir("$dir/$file")) ? delTree("$dir/$file") : @unlink("$dir/$file");		# otherwise the target is a normal file or directory, so take the appropriate action
	}
	return @rmdir($dir);
}




/**
 * Copy a file, or recursively copy a folder and its contents
 * @author	Aidan Lister <aidan@php.net>
 * @version	1.0.1
 * @link	http://aidanlister.com/2004/04/recursively-copying-directories-in-php/
 * @param	string	$source		Source path
 * @param	string	$target		Target path
 * @param	int	$permissions	New folder creation permissions
 * @return	bool	Returns true on success, false on failure
 */
function xCopy($source, $target, $permissions = 0755) {
	// Check for symlinks
	if (is_link($source)) { return @symlink(readlink($source), $target); }

	// Simple copy for a file
	if (is_file($source)) { return @copy($source, $target); }

	// Make target directory
	if (!is_dir($target)) { @mkdir($target, $permissions); }

	// Loop through the folder
	$dir = dir($source);
	while (false !== $entry = $dir->read()) {
		// Skip pointers
		if ($entry == '.' || $entry == '..') { continue; }

		// Deep copy directories
		xCopy("$source/$entry", "$target/$entry", $permissions);
	}

	// Clean up
	$dir->close();
	return true;
}

?>
