<?php
# envars.php	the global definitions used by all projects distributed by Cliquesoft.org
# Created	Unknown by Dave Henderson (dhenderson@cliquesoft.org)
# Updated	2019-07-11 by Dave Henderson (dhenderson@cliquesoft.org)


# Global Constant Definitions
define("PROJECT",'AFWS');				# the name of the overall project	DEV NOTE: can't use periods because the verification email doesn't get displayed correctly
define("TIMEOUT",0);					# the amount of time (in seconds) to wait before the users' session expires
define("CAPTCHAS",true);				# the boolean value to enable built-in captchas
define("HOSTED",false);					# whether or not this application is being hosted as a service for multiple users or for internal, single-use by an organization (currently not used by this app)

# Global Directory Definitions
$gbl_dirCron='../data/_cron';				# the directory for scheduled tasks using cron (currently not used by this app)
$gbl_dirData='../data';					# the directory where user/app data is stored (currently not used by this app)
$gbl_dirLogs='../temp';					# the directory to store log files in
$gbl_dirMail='../data';					# the directory to store email in
$gbl_dirTemp='../temp';					# the directory to store temp files in for this module (typically the same throughout the PROJECT)
$gbl_dirVerify='../data/_verify';			# the directory to store the email verification scripts

# Global Log Definitions
$gbl_logEmail='email.log';				# the log filename that records success or failure when emailing from any scripts that make up this module
$gbl_logScript=SCRIPT.'.log';				# OPTIONAL log that would have errors specific to the script itself
$gbl_logModule=MODULE.'.log';				# OPTIONAL log that would have errors specific to the module itself
$gbl_logProject=PROJECT.'.log';				# OPTIONAL global log for the overall project - WARNING: this MUST match all the other modules!

# Global URI Definitions
$gbl_uriPPV='https://www.sandbox.paypal.com/cgi-bin/webscr';	# the URI to validate the account funding with the payment processor
$gbl_uriContact='allflwindowscreen.com';		# the domain for emailing		DEV NOTE: used so that the app can (optionally) email to a separate domain; (e.g. global email system -vs- {staging|demo|www}.domain.com)
$gbl_uriProject='https://www.digital-pipe.com/cai7';	# same as above, but to segment parts of the system to be specific a MODULE

# Global Mail Definitions
$gbl_nameNoReply='Do NOT Reply';			# the TO name used by the automated emailing system for emails that shouldn't have any reply capabilities
$gbl_nameHackers='AFWS Hackers';			# same as above, but for your PROJECT developers
$gbl_nameCrackers='AFWS Crackers';			# same as above, but for your PROJECT support staff that should handle suspected malicious activity
$gbl_emailNoReply='noreply@'.$gbl_uriContact;		# the TO email address used by the automated emailing system for emails that shouldn't have any reply capabilities
$gbl_emailHackers='hackers@'.$gbl_uriContact;		# same as above, but for your PROJECT developers
$gbl_emailCrackers='crackers@'.$gbl_uriContact;		# same as above, but for your PROJECT support staff that should handle suspected malicious activity

# Global Captcha Definitions
$gbl_intFailedAuth=5;					# the number of failed authentication attempts allowed before automatically locking an account due to suspected cracking attempts
$gbl_intFailedCaptcha=5;				# same as above, but for failed captcha attempts
$gbl_keyPubCaptcha = '';				# the public key used by any included captcha system
$gbl_keyPriCaptcha = '';				# same as above, but the private key

# Global System Variables
$gbl_intMaintenance=0;					# WARNING: ONLY turn this on if the system needs maintenance!
$gbl_strMaintenance='2:30pm EST - down for 30 min';	# relates to the $gbl_maintenance variable when it's enabled and is a short message to be relayed to the users
$gbl_debug=0;						# means the project is in a DEBUG state and to be verbose with output
$gbl_info=array();					# the array to store all general information messages
$gbl_fail=array();					# same as above, but for failure/error messages
$gbl_succ=array();					# same as above, but for success messages
$gbl_warn=array();					# same as above, but for warning messages
$gbl_user=array();					# an array that stores the active users account information
$gbl_null=array();					# used when calling procSQLRequest as its first parameter when you don't want to scrub any data
$linkDB;						# used by all the scripts to maintain a connection to the SQL server
?>
