<?php
# alert.php	emails the dittodata job details to the requested parties
#
# Unless a valid Cliquesoft Private License (CPLv1) has been purchased for your
# device, this software is licensed under the Cliquesoft Public License (CPLv2)
# as found on the Cliquesoft website at www.cliquesoft.org.
#
# This program is distributed in the hope that it will be useful, but WITHOUT
# ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
# FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.
#
# Created	2021/01/08 by Dave Henderson (support@cliquesoft.org)
# Updated	2026/07/09 by Dave Henderson (support@cliquesoft.org)




# Constant Definitions
define("MODULE",'alert');					# the name of this module (NOTE: this can be the same as the PROJECT constant in the envars.php file)
define("SCRIPT",basename($_SERVER['SCRIPT_NAME']));		# the name of this script (for tracing bugs and automated messages)
define("NAME",'alert');

# Module Requirements						  NOTE: MUST come below Module Constant Definitions
require_once('alert.cfg');
require_once('_alert.php');

# Start or resume the PHP session				  NOTE: gains access to $_SESSION variables in this script
#session_start();




# format the dates in UTC
$_ = gmdate("Y-m-d H:i:s",time());				# used this mannor so all the times will be the exact same (also see http://php.net/manual/en/function.gmdate.php)




header('Content-Type: text/plain; charset=utf-8');


if ($_GET['type'] == 'sync') { $TYPE = 'SYNC'; } else { $TYPE = 'BACKUP'; }




$mail = new mimeMail();
$mail->from = '"'.$_sAlertsName.'" <'.$_sAlertsEmail.'>';
$mail->cc = "";							# $_POST["cc"];
$mail->headers = "Errors-To: ".$_sSupportEmail;
$mail->to = '"'.$_sContactName.'" <'.$_sContactEmail.'>';
if ($_GET['status'] == 'success')
	{ $mail->subject = '--- '.$_GET['client'].' '.$TYPE.' REPORT ---'; }
else
	{ $mail->subject = '!!! '.$_GET['client'].' '.$TYPE.' FAILURE !!!'; }
$mail->body = "<html>\n<style>\nbody {margin:0; bgcolor='#fff';}\ntable {width:100%;}\nimg {float:right; padding-left:5px;}\nh1 {padding:50px 0 10px; font-size:32px; font-variant:small-caps; color:#92bfe5;}\nh2 {margin-bottom:5px; font:12pt verdana bold; color:#808080;}\np, div {font:14px verdana; color:#808080; text-align:justify;}\ndiv {text-align:right;}\n</style>\n<body>\n<table>\n<tr>\n<td>&nbsp;</td>\n<td width='500'>\n<img src='".$_sUriProject."/imgs/alert.png' border='0' />\n<h1>".PROJECT."</h1><br />\n<h2>".$mail->subject."</h2>\n<br />\n<p>\nTeam,<br />\n<br />\nThis report was sent to inform you about the scheduled job that was run recently at the clients site. If the reported size is 0, or this email did not arrive, there is a problem that has been encountered that will need to be investigated as soon as possible! Please use the below information as a reference.<br />\n</p>\n<br />\n<div>- ".PROJECT." Staff</div><br />\n<br />\n<br />\n<p>\n<u>Date:</u> ".$_." GMT<br />\n<u>Project:</u> DittoData<br />\n<u>Script:</u> ".SCRIPT."<br />\n<br />\n<u>Client:</u> ".$_GET['client']."<br />\n<br />\n<u>Job:</u> ".$_GET['name']."<br />\n<u>Type:</u> ".$_GET['type']."<br />\n<u>Archive:</u> ".$_GET['archive']."<br />\n<u>Size:</u> ".$_GET['size']."<br />\n</p>\n</td>\n<td>&nbsp;</td>\n</tr>\n</table>\n</body>\n</html>";


if( $mail->send() ) {
	if ($EML = fopen("$_sDirLogs/$_sLogEmail",'a')) { fwrite($EML,"Email successfully sent to '".$_sContactEmail."' on ".date("m/d/Y H:i:s",time()).".\n"); }

	$TEXT = "Sending backup report: [success]";
	$HTML = "<!doctype html>\n\n" .
		"<html lang='en'>\n" .
		"<head>\n" .
		"	<title>Backup Report</title>\n" .
		"</head>\n" .
		"<body>\n" .
		"	The backup report was sent successfully!\n" .
		"</body>\n";
} else {
	if ($EML = fopen("$_sDirLogs/$_sLogEmail",'a')) { fwrite($EML,"Couldn't send the email to '".$_sContactEmail."' on ".date("m/d/Y H:i:s",time()).".\n"); }

	$TEXT = "Sending backup report: [failure]";
	$HTML = "<!doctype html>\n\n" .
		"<html lang='en'>\n" .
		"<head>\n" .
		"	<title>Backup Report</title>\n" .
		"</head>\n" .
		"<body>\n" .
		"	There was an error sending the report. Please check your logs.\n" .
		"</body>\n";
}

if ($_GET['verbose'] == 'true') { echo $TEXT; }

?>

