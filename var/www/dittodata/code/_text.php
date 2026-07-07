<?php
# _text.php	provides the relevant page IO
#
# Created	2021/04/07 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
# Updated	2021/04/09 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
#
# Unless a valid Cliquesoft Private License (CPLv1) has been purchased for your
# device, this software is licensed under the Cliquesoft Public License (CPLv2)
# as found on the Cliquesoft website at www.cliquesoft.org.
#
# This program is distributed in the hope that it will be useful, but WITHOUT
# ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
# FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.


# Constant Definitions
define("MODULE",'website');					# the name of this module (NOTE: this can be the same as the PROJECT constant in the envars.php file)
define("SCRIPT",basename($_SERVER['SCRIPT_NAME']));		# the name of this script (for tracing bugs and automated messages)
define("NAME",'Default');

# Module Requirements						  NOTE: MUST come below Module Constant Definitions
require_once('../../sqlaccess');
require_once('../data/config.php');
if (file_exists('../data/config.'.strtolower(MODULE).'.php')) { require_once('../data/config.'.strtolower(MODULE).'.php'); }
require_once('_smtpmail.php');
require_once('_global.php');

# Start or resume the PHP session				  NOTE: gains access to $_SESSION variables in this script
session_start();




# format the dates in UTC
$_ = gmdate("Y-m-d H:i:s",time());				# used this mannor so all the times will be the exact same (also see http://php.net/manual/en/function.gmdate.php)




# Usage syntax:
# if (sendSMS) { ...success... } else { ...fail... }
# sends the passed text message to the target.  NOTE: the return values aren't typical and are provided below:
#	0 = sent successful
#	1 = one of the required fields is empty (from,to,message,carrier)
#	2 = message not sent due to an issue with php's mail() function
function sendSMS($strFrom,$intToPhoneNo,$strToCarrier,$message) {
	global $urlShort;

	$from = $strFrom;
	$to = '1'.preg_replace("/\D/",'',$intToPhoneNo);					# remove all -, +, etc symbols -AND- adds a '1' for region code
	$carrier = $strToCarrier;

	if (!empty($from) && !empty($to) && !empty($message) && !empty($carrier)) {	# make sure all the required info is set
		$carriers = array(							# specify carriers information
# LEFT OFF - update from http://en.wikipedia.org/wiki/List_of_carriers_providing_SMS_transit
#			to make this work, we might have to do a multiple-dimensional array such as:
#			acs => array(
#			   email => "msg.acsalaska.net",
#			   prefix => 1				# region code for the number - 1=usa, 11=uk, 385=..., etc
#			   dashes => 1				# does the phone number need to have dashes in it to work (eg 407-970-1407 or 4079701407)
#			),
#			att => array( ...
#			this will allow the correct number to be used for the phone's email address
			"acs" => "msg.acsalaska.net",
			"att" => "txt.att.net",
			"attalt1" => "mmode.com",
			"attent" => "page.att.net",
			"boost" => "boostmobile.com",
			"cellularone" => "mobile.celloneusa.com",
			"centennial" => "cwemail.com",
			"cincinnati" => "gocbw.com",
			"cingular" => "cingular.com",
			"cingulargo" => "cingulartext.com",
			"cricket" => "sms.mycricket.com",
			"gci" => "mobile.gci.net",
			"globalstar" => "msg.globalstarusa.com",
			"gsc" => "gscsms.com",
			"iridium" => "msg.iridium.com",
			"metropcs" => "mymetropcs.com",
			"quest" => "qwestmp.com",
			"sprinthelio" => "myhelio.com",
			"sprintpcs" => "messaging.sprintpcs.com",
			"sprintnextel" => "messaging.nextel.com",
			"sprintvirgin" => "vmobl.com",
			"syringa" => "rinasms.com",
			"tmobile" => "tmomail.net",
			"tmobilesuncom" => "tms.suncom.com",
			"unicel" => "utext.com",
			"uscellular" => "email.uscc.net",
			"verizon" => "vtext.com",
			"verizonalltel" => "message.alltel.com",
			"viaero" => "viaerosms.com",

			"bellmobility" => "txt.bell.ca",
			"fido" => "fido.ca",
			"Koodo" => "msg.telus.com",
			"mts" => "text.mtsmobility.com",
			"pctelecom" => "mobiletxt.ca",
			"rogers" => "pcs.rogers.com",
			"sasktel" => "sms.sasktel.com",
			"tbaytel" => "tbayteltxt.net",
			"telus" => "msg.telus.com",
			"virgin" => "vmobile.ca",

			"aql" => "text.aql.com"
		);
# 8/7/2014 UPDATE - see the following for updated carrier values: http://computer.howstuffworks.com/e-mail-messaging/how-to-send-text-messages-computer.htm

		#if (in_array($carrier, $carriers)) {					# if the carrier exists in the array, then...  NOTE: don't need this since we will only be dealing with the selection entered via OnlyProfile.org
#			$formatted_number = $to."@".$carriers[$carrier];			# format the connection string
$formatted_number = '14079701407@tmomail.net';
#$from = 'notify@darcysvirtuallegal.com';
#$from = 'notify@infinciti.com';
$from = 'daveh@infinciti.com';
			$headers = "From: {$from}\r\nReply-To: {$from}\r\nX-Mailer: PHP/".phpversion();
			if (mail($formatted_number, '', $message, $headers)) {return 0;} else {return 2;}		# sends the message
		#}
	}
	return 1;
}



# port the passed params if this script is being called from the CLI instead of webserver
if (!isset($_SERVER["HTTP_HOST"])) { parse_str($argv[1], $_POST); }		# https://stackoverflow.com/questions/5655284/how-can-i-pass-parameters-from-the-command-line-to-post-in-a-php-script

# create the header for any processing below...
if ($_POST['action'] != 'send') {
	header('Content-Type: text/xml; charset=utf-8');
	echo "<?xml version='1.0' encoding='UTF-8'?>\n\n";
}




if ($_POST['target'] == 'text') {						# ACTIONS DEALING WITH TEXTS
	if ($_POST['action'] == 'send') {					# IF WE ARE SENDING (via cron)
		if (! sendMail($_POST['t'],$_POST['n'],'notify@darcysvirtuallegal.com','Darcys Virtual Legal Alerts','Upcoming Appointment',$_POST['m'])) { exit(1); }		# if fail, the text was NOT successfully sent
		# NOTE: we do NOT exit here on purpose so that the just-sent text can be deleted from the queue (later on down)

	} else if ($_POST['action'] == 'load') {				# IF WE ARE LOADING THE WEBPAGE
		$XML =	"<s>\n" .
			"   <xml>\n";

		# lets process all the existing texts to-be-sent		https://stackoverflow.com/questions/3686177/php-to-search-within-txt-file-and-echo-the-whole-line
		if (file_exists('/home/digitalpipe/crontab/texts')) {
			$gbl_errs['error'] = "The '/home/digitalpipe/crontab/texts' file can not be opened.";
			$gbl_info['command'] = "fopen('/home/digitalpipe/crontab/texts', 'r')";
			$gbl_info['values'] = 'None';
			$handle = fopen('/home/digitalpipe/crontab/texts', 'r');
			if (! $handle) {
				$e = error_get_last();
				myErrorHandler($e['type'], $e['message'], basename($_SERVER['PHP_SELF']), __LINE__ - 3);			# NOTE: we subtract 3 from __LINE__ to know it was the fopen() call that caused the error
			}
			while (! feof($handle)) {
				$line = fgets($handle);

				# 1. Skip comments or blank lines
				if (strpos($line, '#') === 0 || $line == '') { continue; }

				# 2. Store all the cron key/value pairs
				$parts = explode("\t", $line);
				parse_str(str_replace('\%', '%', substr($parts[5], strpos($parts[5], "'")+1, -2)), $pairs);			# NOTE: this converts the unescaped ('\%' > '%') parameters list into a key/value array ($pairs)

				# 3. Convert the epoch from (hosting location) PDT to (local) EST
# LEFT OFF - the below works when the time is off by a day, but not my hours or minutes
				# if the current month is greater than the cron value -OR- the month is the same and the day is greater than the cron values, then the text is for next year, otherwise it is for this year
				if (date('m') > $parts[3] || (date('m') == $parts[3] && date('d') > $parts[2])) { $year = date('Y') + 1; } else { $year = date('Y'); }		# https://stackoverflow.com/questions/5347217/simplest-way-to-display-current-month-and-year-like-aug-2016-in-php
				$Epoch = $year.'-'.$parts[3].'-'.$parts[2].' '.$parts[1].':'.$parts[0].':00';							# format the string the the conversion call...
				$Epoch = date_create($Epoch, new DateTimeZone('PDT'))->setTimezone(new DateTimeZone('EST'))->format("Y-m-d H:i");		# perform the actual conversion   https://www.epochconverter.com/programming/php
				$epoch = explode(' ', $Epoch);													# separate the date and time into an array
				# this is an adjustment to make the UI show the correct time if 12:??pm was entered
				if (substr($epoch[1], 0, 2) == '00') { $epoch[1] = '24'.substr($epoch[1],2, 3); }

				$XML .= "	<text id='".$pairs['id']."' target='".$pairs['n']."' date='".$epoch[0]."' time='".$epoch[1]."'>".safeXML($pairs['m'])."</text>\n";
				# e.g.	"	<text id='123456' target='Dave' date='2021/04/07' time='10:30pm'>Hello world</text>\n"
			}
			fclose($handle);
		}

		$XML .=	"   </xml>\n" .
			"</s>\n";

		echo $XML;
		exit();

	} else if ($_POST['action'] == 'create') {				# IF WE ARE CREATING A NEW TEXT
		$found = 0;
		do {
			# DEV NOTE: the 'id' is the epoch when the text was saved to file
			$id = gmdate("YmdHis",time());

			# first lets check that the id is NOT already in the file		https://stackoverflow.com/questions/3686177/php-to-search-within-txt-file-and-echo-the-whole-line
			if (file_exists('/home/digitalpipe/crontab/texts')) {
				$gbl_errs['error'] = "The '/home/digitalpipe/crontab/texts' file can not be opened.";
				$gbl_info['command'] = "fopen('/home/digitalpipe/crontab/texts', 'r')";
				$gbl_info['values'] = 'None';
				$handle = fopen('/home/digitalpipe/crontab/texts', 'r');
				if (! $handle) {
					$e = error_get_last();
					myErrorHandler($e['type'], $e['message'], basename($_SERVER['PHP_SELF']), __LINE__ - 3);		# NOTE: we subtract 3 from __LINE__ to know it was the fopen() call that caused the error
				}
				while (! feof($handle)) {
					$line = fgets($handle);
					if (strpos($line, '&id='.$id.'&') !== FALSE) {
						$found = 1;
						break;
					}
				}
				fclose($handle);
			}
		} while ($found == 1);

		# If we've made it here, we are safe to write the new text to the crontab file

		# 1. Store the date and time as individual numbers for the crontab file
		$date = explode('-', $_POST['txtDate']);					# NOTE: the date will be in the format of YYYY-MM-DD, so $date[0] is the year
		if ($_POST['lstAMPM'] == 'pm') { $_POST['txtHour'] += 12; }			# if the user selected 'pm', then add 12 hours to the time

		# 1. Convert the epoch from (local) EST to (hosting location) PDT
		$epoch = $_POST['txtDate']." ".$_POST['txtHour'].":".str_pad($_POST['txtMin'],2,'0',STR_PAD_LEFT).":00";
		$cron = date_create($epoch, new DateTimeZone('EST'))->setTimezone(new DateTimeZone('PDT'))->format("i	H	d	m");	# https://www.epochconverter.com/programming/php

		# 2. Now write the line to the crontab file for texts
		file_put_contents('/home/digitalpipe/crontab/texts', $cron."\t*\tcd /home/digitalpipe/darcysvirtuallegal.com/text/code; php _text.php 'action=send&target=text&id=".$id."&t=".$_POST['lstTarget']."&n=".$_POST['name']."&m=".str_replace('%', '\%', urlencode($_POST['txtMessage']))."'\n", FILE_APPEND);

		# 3. Update the crontab jobs listing for the user
		$retval = 0;
		exec('cat /home/digitalpipe/crontab/* | crontab -', $gbl_null, $retval);	# https://stackoverflow.com/questions/732832/php-exec-vs-system-vs-passthru
		if ($retval != 0) {
			echo "<f><msg>ERROR!\n\nAn error has occurred while saving the text. Please contact your administrator to look into the problem.</msg></f>";
			exit();
		}

		echo "<s></s>";
		exit();
	}


	# WARNING: this is a separate 'if' on purpose so that after a successful send of the text, it gets deleted from the crontab file also
	if ($_POST['action'] == 'delete' || $_POST['action'] == 'send') {	# IF WE ARE DELETING A SCHEDULED TEXT -or- DELETING THE TEXT AFTER IT WAS SENT
		# first lets check that the id is NOT already in the file			https://stackoverflow.com/questions/3686177/php-to-search-within-txt-file-and-echo-the-whole-line
		if (file_exists('/home/digitalpipe/crontab/texts')) {
			$timeout = 0;

			# 1. Make sure the temp file containing the deleted text message does NOT currently exist
			while (file_exists('/home/digitalpipe/temp/texts')) {
				sleep(1);
				$timeout++;
				if ($timeout > 120) {
					$gbl_errs['error'] = "A timeout occurred while waiting to erase a deleted/sent text message.";
					$gbl_info['command'] = "file_exists('/home/digitalpipe/temp/texts')";
					$gbl_info['values'] = 'None';
					myErrorHandler(0, $gbl_errs['error'], basename($_SERVER['PHP_SELF']), __LINE__ - 7);
					exit(2);
				}
			}

			# 2. If we've made it here we are ready to transpose the to-be-sent text messages, minus the one to-be-deleted
			$gbl_errs['error'] = "The '/home/digitalpipe/temp/texts' file can not be opened.";
			$gbl_info['command'] = "fopen('/home/digitalpipe/temp/texts', 'r')";
			$gbl_info['values'] = 'None';
			$target = fopen('/home/digitalpipe/temp/texts', 'w');
			if (! $target) {
				$e = error_get_last();
				myErrorHandler($e['type'], $e['message'], basename($_SERVER['PHP_SELF']), __LINE__ - 3);			# NOTE: we subtract 3 from __LINE__ to know it was the fopen() call that caused the error
			}

			$gbl_errs['error'] = "The '/home/digitalpipe/crontab/texts' file can not be opened.";
			$gbl_info['command'] = "fopen('/home/digitalpipe/crontab/texts', 'r')";
			$gbl_info['values'] = 'None';
			$source = fopen('/home/digitalpipe/crontab/texts', 'r');
			if (! $source) {
				fclose($target);
				$e = error_get_last();
				myErrorHandler($e['type'], $e['message'], basename($_SERVER['PHP_SELF']), __LINE__ - 4);			# NOTE: we subtract 4 from __LINE__ to know it was the fopen() call that caused the error
			}
			while (! feof($source)) {												# while there are lines to read, then...
				$line = fgets($source);												#   read each line
				if (strpos($line, '&id='.$_POST['id'].'&') !== FALSE) { continue; }						#   skip the one that needs to be deleted
				fputs($target, $line);												#   transposing all the other lines
			}
			fclose($source);
			fclose($target);

			# 3. Now replace the original file with the updated one
			$gbl_errs['error'] = "Failed to rename '/home/digitalpipe/temp/texts' to '/home/digitalpipe/crontab/texts'.";
			$gbl_info['command'] = "rename('/home/digitalpipe/temp/texts', '/home/digitalpipe/crontab/texts')";
			$gbl_info['values'] = 'None';
			rename('/home/digitalpipe/temp/texts', '/home/digitalpipe/crontab/texts');

			# 4. Update the crontab jobs listing for the user
			$retval = 0;
			exec('cat /home/digitalpipe/crontab/* | crontab -', $gbl_null, $retval);	# https://stackoverflow.com/questions/732832/php-exec-vs-system-vs-passthru
			if ($retval != 0) {
				echo "<f><msg>ERROR!\n\nAn error has occurred while deleting the text. Please contact your administrator to look into the problem.</msg></f>";
				exit();
			}
		}
		echo "<s></s>";
		exit();
	}
}




# otherwise, something malicious is going on so...
# NOTE: this is NOT in an "} else {" since we want this to run even if errors above occur
echo "<f><msg>An invalid request has occurred, our staff has been notified.</msg></f>";
if (! array_key_exists('username', $gbl_user)) { $gbl_user['username'] = 'guest'; }
if (! array_key_exists('email', $gbl_user)) { $gbl_user['email'] = 'Not Provided'; }
sendMail($gbl_emailCrackers,$gbl_nameCrackers,$gbl_emailNoReply,$gbl_nameNoReply,'!!! Possible Cracking Attempt !!!',"<html>\n<body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' offset='0' bgcolor='#ffffff'>\n<table width='100%'>\n<tr>\n<td>&nbsp;</td>\n<td width='500'>\n<img src='".$gbl_uriProject."/home/guest/imgs/email_error.png' border='0' style='float:right; padding-left: 5px;' />\n<h1 style='padding: 50px 0 10px 0; font-size: 32px; font-variant: small-caps; color: #92bfe5;'>".PROJECT."</h1><br />\n<h2 style='margin-bottom: 5px; font: 12pt verdana bold; color: #808080;'>Possible Cracking Attempt</h2><br />\n<p style='font: 12px/17px verdana; color: #808080; text-align: justify;'>\nTeam,<br />\n<br />\nWe might have had a possible cracking attempt made on ".$_.", from ".$_SERVER['REMOTE_ADDR'].", while attempting to pass an invalid API value.  Please investigate and correct this problem as soon as possible.  If the problem warrants contacting the end user, please do so as well by referencing the relevant information below:<br />\n<br />\n<br />\nUsername: ".$gbl_user['username']."<br />\nAddress: ".$gbl_user['email']."<br />\n<br />\nProject: ".PROJECT."<br />\nModule: ".MODULE."<br />\nScript: ".SCRIPT."<br />\n<br />\nDB Host: ".DBHOST."<br />\nDB Name: ".DBNAME."<br />\nDB Prefix: ".PREFIX."<br />\n<br />\nOur Error: An invalid API value was passed to the script.<br />\n<br />\nSincerely,<br />\n".PROJECT." Staff\n<br />\n<br />\n[".SCRIPT."; Body]<br />\n<br />\nVar Dump:<br />\n</p>\n<pre>_POST\n".print_r($_POST, true)."</pre><br />\n<pre>_GET\n".print_r($_GET, true)."</pre><br />\n</td>\n<td>&nbsp;</td>\n</tr>\n</table>\n</body>\n</html>");

?>
