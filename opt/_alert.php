<?php
# _Contact.php
#
# Created	2005/11/30 by Mike Stubbs
# Expanded	2012/11/05 by Dave Henderson (support@cliquesoft.org)
# Updated	2025/09/04 by Dave Henderson (support@cliquesoft.org)
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
# - https://code.google.com/p/cool-php-captcha/
#
# - Errors encountered attempting to send email to @yahoo.com accounts
#	https://help.yahoo.com/kb/SLN24016.html
#	https://github.com/PHPMailer/PHPMailer/releases
#	https://stackoverflow.com/questions/27404183/i-can-not-receive-from-php-mail-function
#	https://www.php.net/manual/en/function.mail.php




# Class Declarations

class mimeMail {
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

	function add_attachment($message, $name = "", $ctype = "application/octet-stream") {
# VER2 - get this to work
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
	function send() {					# http://stackoverflow.com/questions/30887610/error-with-php-mail-multiple-or-malformed-newlines-found-in-additional-header	http://stackoverflow.com/questions/2265579/php-e-mail-encoding
		global $_sAlertsEmail,$_sSupportEmail;

		$head = "";					# for the headers
		$mime = "";					# for the mime encoded email
		$boundary = "b".md5(uniqid(time()));		# create the boundary ID

		$head .= "X-Mailer: PHP v".phpversion()."\r\n";
		$head .= "From: ".$this->from."\r\n";
		if ($this->from == $_sAlertsEmail)
			{ $head .= "Reply-To: ".$_sContactEmail."\r\n"; }
		$head .= "Reply-To: ".$this->from."\r\n";
		$head .= "Return-Path: ".$_sSupportEmail."\r\n";
		if (! empty($this->cc))      { $head .= "Cc: ".$this->cc."\r\n"; }
		if (! empty($this->bcc))     { $head .= "Bcc: ".$this->bcc."\r\n"; }
		if (! empty($this->headers)) { $head .= $this->headers."\r\n"; }
		$head .= "MIME-Version: 1.0\r\n";
		$head .= "Content-type: text/html; charset=utf-8\r\n";
		$head .= "Content-Transfer-Encoding: 8bit\r\n\r\n";

		$mime .= $this->body."\r\n";
		return( mail($this->to, $this->subject, $mime, $head) );
	}
}

?>

