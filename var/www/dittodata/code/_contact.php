<?php
# contact.php	provides the relevant page IO
#
# Created	2019/02/26 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
# Updated	2019/09/13 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
#
# Unless a valid Cliquesoft Private License (CPLv1) has been purchased for your
# device, this software is licensed under the Cliquesoft Public License (CPLv2)
# as found on the Cliquesoft website at www.cliquesoft.org.
#
# This program is distributed in the hope that it will be useful, but WITHOUT
# ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
# FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.


# Constant Definitions
define("MODULE",'Website');			# the name of this module; NOTE: MUST be the same in all php files in this MODULE
define("PREFIX",'');				# the prefix used with the database
define("SCRIPT",'contact.php');			# the name of this script (for tracing bugs and automated messages)

# Module Requirements				  NOTE: MUST come below Module Constant Definitions
require_once('../data/config.php');
require_once('./_mimemail.php');
require_once('./_global.php');

# Start or resume the PHP session		  NOTE: gains access to $_SESSION variables in this script
session_start();




// we need to send some mail!
header('Content-Type: text/xml; charset=utf-8');
echo "<?xml version='1.0' encoding='UTF-8'?>\n";

# make appropriate substitutions
$_POST['txtMsg'] = str_replace('<','&lt;',$_POST['txtMsg']);
$_POST['txtMsg'] = str_replace('>','&gt;',$_POST['txtMsg']);

# validate all submitted data
if (! validate($_POST['txtSender'],128,'[^a-zA-Z0-9_\- ]')) { exit(); }
if (! validate($_POST['txtCaptcha1'],16,'[^a-zA-Z\- ]')) { exit(); }
if (! validate($_POST['lstRecipient'],12,'{accounting|service|info|jobs|sales|abuse|support|web}')) { exit(); }
if (! validate($_POST['txtEmail'],128,'[^a-zA-Z0-9\.@\-_]')) { exit(); }
if (! validate($_POST['txtSubject'],64,'![=<>;]')) { exit(); }
#if (! validate($_POST['txtMsg'],1024,'![=<>;]')) { exit(); }		# NOTE: since this is just an email that gets sent, there is no reason to perform these checks; besides the &lt;/&gt; conversion will still retain a semicolon!

if (empty($_POST['txtCaptcha1'])) {
	echo "<f><msg>You must enter the captcha text before attempting to save or create an account.</msg></f>";
	exit();
} else if (empty($_SESSION['captcha']) || trim(strtolower($_POST['txtCaptcha1'])) != $_SESSION['captcha']) {
	echo "<f><msg>The captcha text you entered does NOT match what is found in the graphic, please try again.</msg></f>";
	exit();
}
unset($_SESSION['captcha']);

$mail = new mime_mail();
$mail->from = "noreply@".$gbl_uriContact;
$mail->cc = $_POST["txtEmail"];
$mail->headers = "Errors-To: support@".$gbl_uriContact;
$mail->to = $_POST['lstRecipient']."@".$gbl_uriContact;
$mail->subject = $_POST["txtSubject"];
$mail->body = $_POST["txtMsg"];

if( $mail->send() )
	echo "<s><msg>Your mail has been sent successfully!</msg></s>";
else
	echo "<f><msg>An error was encountered while attempting to send the mail, our staff has been alerted.</msg></f>";


?>

