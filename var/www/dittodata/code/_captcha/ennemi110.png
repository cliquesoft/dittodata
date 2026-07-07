<?php
# _smtpmail.php	A standard module that provides email sent through an SMTP
#		server instead of a webserver. This should be used over the
#		_mimemail.php script if recipients need additional security
#		checks as to the source of the email.
#
# Created	2020/12/17 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
# Updated	2021/04/09 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
#
# Unless a valid Cliquesoft Proprietary License (CPLv1) has been purchased
# for this device, this software is licensed under the Cliquesoft Public
# License (CPLv2) as found on the Cliquesoft website at www.cliquesoft.org
#
# This program is distributed in the hope that it will be useful, but WITHOUT
# ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
# FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.
#
# ADDITIONAL:
# - Based on: https://help.dreamhost.com/hc/en-us/articles/216140597-How-do-I-send-PHP-mail-via-SMTP-
#
# - If you're using your GMAIL address to send via SMTP, you must first allow your application
#   access to your GMAIL address. If you do not do this, your email will not authenticate and
#   not send. View the following article for details:
#   https://help.dreamhost.com/hc/en-us/articles/115001719551-Troubleshooting-GMAIL-SMTP-authentication-errors
#
# - To install PEAR Mail:
#   $ pear install --alldeps Mail


error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_STRICT);

require_once "Mail.php";					# from the PEAR Mail package
require_once('../../smtpaccess');				# the file that contains the credentials to the email server




class mime_mail {				# LEFT OFF - rename this to a generic name for both this file and _smtpmail.php
	var $parts;
	var $to;
	var $from;
	var $cc;
	var $bcc;
	var $headers;
	var $subject;
	var $body;

	# constructor; initialize members to something sane
	function __construct() {
		$this->parts   = array();
		$this->to      = "";
		$this->from    = "";
		$this->cc      = "";
		$this->bcc     = "";
		$this->subject = "";
		$this->body    = "";
		$this->headers = "";

		$this->port    = "";
		$this->host    = "";

		$this->user    = "";
		$this->pass    = "";
	}




	# build headers and post message
	function send() {							# http://stackoverflow.com/questions/30887610/error-with-php-mail-multiple-or-malformed-newlines-found-in-additional-header	http://stackoverflow.com/questions/2265579/php-e-mail-encoding
		if (strpos($this->body, '<html>') === FALSE) {			# if the message to be sent is NOT formatted in HTML (e.g. text message), then...
			$headers = array(
				'From' => $this->from,
				'To' => $this->to,
				'Subject' => $this->subject,
				'Reply-To' => $this->from
			);
		} else {							# otherwise it is in HTML, so...
			$headers = array(
				'From' => $this->from,
				'To' => $this->to,
				'Subject' => $this->subject,
				'Reply-To' => $this->from,
				'Content-type' => "text/html; charset=utf-8"
			);
		}
		$smtp = Mail::factory('smtp', array(
			'host' => MAILHOST,
			'port' => MAILPORT,
			'auth' => true,
			'username' => MAILUSER,
			'password' => MAILPASS)
		);
		$mail = $smtp->send($this->to, $headers, $this->body);
		return(! PEAR::isError($mail));					# can use "$mail->getMessage()" to get the error if one is produced	NOTE: we invert the return value to keep the _global code constant
	}
}
?>
