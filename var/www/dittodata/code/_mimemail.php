<?php
# _mimemail.php	A standard module that provides email sent through a web
#		server instead of an SMTP mail server. This should be
#		used over the _smtpmail.php script if the hosting company
#		is handling your web -and- email hosting. The email being
#		sent with this script is MIME encoded.
#
# created	2005/11/30 by Mike Stubbs for Digital Pipe Inc
# updated	2020/12/17 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
#
# Unless a valid Cliquesoft Proprietary License (CPLv1) has been purchased
# for this device, this software is licensed under the Cliquesoft Public
# License (CPLv2) as found on the Cliquesoft website at www.cliquesoft.org
#
# This program is distributed in the hope that it will be useful, but WITHOUT
# ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
# FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.
#
# NOTES:
# this version includes some custom code specifically for the processing of the announcements made.


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
	}

	function add_attachment($message, $name="", $ctype="application/octet-stream") {
		$this->parts[] = array (
			"ctype"   => $ctype,
			"message" => $message,
			"encode"  => $encode,
			"name"    => $name
		);
	}

	function build_message($part) {
		$message  = $part["message"];
		$message  = chunk_split(base64_encode($message));
		$encoding = "base64";
		return( "Content-Type: ".$part["ctype"].($part["name"] ? "; name = \"".$part["name"]."\"" : "")."\nContent-Transfer-Encoding: $encoding\n\n$message\n" );
	}

	function build_multipart() {
		$boundary = "b".md5(uniqid(time()));
		$multipart = "Content-Type: multipart/mixed; boundary = $boundary\n\nThis is a MIME encoded message.\n\n--$boundary";

		for( $i = sizeof($this->parts)-1; $i >= 0; $i-- )
			{ $multipart .= "\n".$this->build_message($this->parts[$i]). "--$boundary"; }

		return( $multipart.=  "--\n" );
	}




	# build headers and post message
	function send() {							# http://stackoverflow.com/questions/30887610/error-with-php-mail-multiple-or-malformed-newlines-found-in-additional-header	http://stackoverflow.com/questions/2265579/php-e-mail-encoding
		global $gbl_uriContact;

		$head = "";							# for the headers
		$mime = "";							# for the mime encoded email
		$boundary = "b".md5(uniqid(time()));				# create the boundary ID

		$head .= "X-Mailer: PHP v".phpversion()."\r\n";
		$head .= "From: ".$this->from."\r\n";
		if ($this->from == 'announcement@'.$gbl_uriContact)		# if we are doing announcements, then we MUST setup an alternate 'Reply-To' address
			{ $head .= "Reply-To: noreply@".$gbl_uriContact."\r\n"; }
		else								# otherwise, just use the same address as the 'Reply-To' value
			{ $head .= "Reply-To: ".$this->from."\r\n"; }		# an attempt to send email to @yahoo.com accounts
										# https://help.yahoo.com/kb/SLN24016.html
										# https://github.com/PHPMailer/PHPMailer/releases
										# https://stackoverflow.com/questions/27404183/i-can-not-receive-from-php-mail-function
										# https://www.php.net/manual/en/function.mail.php
		$head .= "Return-Path: support@".$gbl_uriContact."\r\n";
		if (! empty($this->cc))      { $head .= "Cc: ".$this->cc."\r\n"; }
		if (! empty($this->bcc))     { $head .= "Bcc: ".$this->bcc."\r\n"; }
		if (! empty($this->headers)) { $head .= $this->headers."\r\n"; }
		$head .= "MIME-Version: 1.0\r\n";
		$head .= "Content-type: text/html; charset=utf-8\r\n";
		$head .= "Content-Transfer-Encoding: 8bit\r\n\r\n";

		$mime .= $this->body."\r\n";
		return(mail($this->to, $this->subject, $mime, $head));
	}
}
?>
