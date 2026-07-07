<?php
# _search.php	a standard module that provides the relevant page IO for
#		live (Google-style) matching results when typing in a value.
#
# Created	2019/07/18 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
# Updated	2021/02/12 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
#
# Unless a valid Cliquesoft Private License (CPLv1) has been purchased for your
# device, this software is licensed under the Cliquesoft Public License (CPLv2)
# as found on the Cliquesoft website at www.cliquesoft.org.
#
# This program is distributed in the hope that it will be useful, but WITHOUT
# ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
# FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.


# Constant Definitions
define("MODULE",'webbooks');					# the name of this module; NOTE: MUST be the same in all php files in this MODULE
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




if ($_POST['action'] == 'search') {				# search the DB and return matching results
	# validate all submitted data
	if (! validate($_POST['table'],64,'[^a-zA-Z0-9_]')) { exit(); }
	if (! validate($_POST['column'],64,'[^a-zA-Z0-9_]')) { exit(); }
# NOTE: we can't check this because we don't know what the future/3rd-party modules may accept for values
#	if (! validate($_POST['value'],128,'[^a-zA-Z0-9_]')) { exit(); }
	if (! validate($_POST['display'],64,'[^a-zA-Z0-9_\[\]\|\!]')) { exit(); }

	# remove default fields and non-SQL based characters from the SQL columns list
	$_POST['display'] = preg_replace(array('/id/','/value/'), '', $_POST['display']);					# removes the two values that will ALWAYS be present
	$_POST['display'] = preg_replace(array('/^\|+/','/\|+$/','/\[/','/\]/'), '', $_POST['display']);			# removes (multiple) '|' characters at the beginning and end as well as the '[]' characters
	$_POST['display'] = str_replace('|', ',', $_POST['display']);								# replaces the remaining '|' characters with ',' so it can be used in the SQL call below

	# make any necessary changes to the _POST values
	$_POST['table'] = PREFIX.$_POST['table'];
	$_POST['value'] = '%'.$_POST['value'].'%';

	# NOTE: we don't need to call loadUser since this information is already provided to ALL users
	if (! connect2DB(DBHOST,DBNAME,DBUNRO,DBPWRO)) { exit(); }				# the connect2DB has its own error handling so we don't need to do it here!

	$XML = '';
	$Values  = '';										# used to store additional database values using the 'display' value
	$Results = '';
	$Columns = 'id,'.$_POST['column'];							# set the default SQL columns list
	if ($_POST['display'] != '') { $Columns .= ','.$_POST['display']; }			# append the additional requested columns to the list
	$Columns = str_replace('!', '', $Columns);						# this removes any '!' characters (indicating the field is encrypted) from the SQL syntax

	# first we need to verify the submitted 'table' value is not SQL injection by making sure it exists in the database (since we can't prepare a statement with a variable table name)
# NOTE: these two SQL statements draaaaaaag the search to multiple seconds... we need a faster solution
#	$gbl_errs['error'] = "The SQL table to query can not be found in the database.";
#	$gbl_info['command'] = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME=?";				# https://stackoverflow.com/questions/15815233/php-show-tables-mysqli-query-not-working
#	$gbl_info['values'] = '[s] '.$_POST['table'];
#	$stmt = $linkDB->prepare($gbl_info['command']);
#	$stmt->bind_param('s', $_POST['table']);
#	$stmt->execute();
#	$Table = $stmt->get_result();
#	$Table->fetch_object();
#	if ($Table->num_rows > 0) {								# if the table exists, then...
#		# next we need to verify the submitted 'key' value is not SQL injection by making sure it exists in the database (since we can't prepare a statement with a variable column name)
#		$gbl_errs['error'] = "The requested search terms can not be found in the database.";
#		$gbl_info['command'] = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME=? AND COLUMN_NAME=?";		# https://stackoverflow.com/questions/1526688/get-table-column-names-in-mysql
#		$gbl_info['values'] = '[s] '.$_POST['table'].', [s] '.$_POST['column'];
#		$stmt = $linkDB->prepare($gbl_info['command']);
#		$stmt->bind_param('ss', $_POST['table'], $_POST['column']);
#		$stmt->execute();
#		$Column = $stmt->get_result();
#		if ($Column->num_rows > 0) {							# if the column exists, then...
			$gbl_errs['error'] = "The requested search terms can not be found in the database.";
			$gbl_info['command'] = "SELECT ".$Columns." FROM ".$_POST['table']." WHERE ".$_POST['column']." LIKE ? GROUP BY ".$_POST['column'];	# we can now add in the submitted values here in the query without fear of SQL injection
			$gbl_info['values'] = '[s] '.$_POST['value'];
			$stmt = $linkDB->prepare($gbl_info['command']);
			$stmt->bind_param('s', $_POST['value']);
			$stmt->execute();
			$Result = $stmt->get_result();

			while ($result = $Result->fetch_assoc()) {
				if ($_POST['display'] != '') {					# if we have additional values we need to return, then...
					$XML = '';						#   (re)set the value for every iterated record
					$Values = explode(',', $_POST['display']);		#   store them individually in an array
					for ($i=0; $i<count($Values); $i++) {			#   individually iterate each one and append as a string to include below
						if (substr($Values[$i], 0, 1) == '!') {		#      if the first character is '!', then the field is encrypted and needs to be decrypted so...
							# store the decryption key and create the cipher
							$salt = file_get_contents('../../denaccess');

							$key = substr($Values[$i], 1);
							$val = Cipher::decrypt($result[$key], $salt);

							$XML .= ' '.$key.'="'.safeXML($val).'"';
						} else { $XML .= ' '.$Values[$i].'="'.safeXML($result[$Values[$i]]).'"'; }	# otherwise there wasn't a proceeding '!' character, so...
					}
				}

				$Results .= "	   <result id='".$result['id']."'".$XML.">".safeXML($result[$_POST['column']])."</result>\n";
			}
#		}
#	}

	echo "<s>\n";
	echo "   <xml>\n";
	echo "	<results>\n";
	echo $Results;
	echo "	</results>\n";
	echo "   </xml>\n";
	echo "</s>\n";
	exit();

} else {

	echo "<f><msg>An error has occurred in your search request.</msg></f>";
	exit();
}

