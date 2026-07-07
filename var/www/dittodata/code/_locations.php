<?php
# _locations.php	provides the relevant page IO
#
# Created	2020/07/24 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
# Updated	2021/01/16 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
#
# Unless a valid Cliquesoft Private License (CPLv1) has been purchased for your
# device, this software is licensed under the Cliquesoft Public License (CPLv2)
# as found on the Cliquesoft website at www.cliquesoft.org.
#
# This program is distributed in the hope that it will be useful, but WITHOUT
# ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
# FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.


header('Content-Type: text/xml; charset=utf-8');
echo "<?xml version='1.0' encoding='UTF-8'?>\n\n";




if ($_POST['state'] != '') {							# if we need to load the cities of the selected state
	$XML =	"<s>\n" .
		"   <xml>\n";

	if (file_exists('_locations/'.$_POST['country'])) {			# check if the directory exists
		chdir('_locations/'.$_POST['country']);				# change into the target directory
		if (file_exists($_POST['state'].'.txt')) {			# check if the file exists
			$fn = fopen($_POST['state'].'.txt','r');
			while (! feof($fn)) { $XML .= "	<c>".trim(fgets($fn))."</c>\n"; }
			fclose($fn);
		}
	}

	$XML = str_replace('<c></c>', '', $XML);				# removes any blank lines

	$XML .=	"   </xml>\n" .
		"</s>\n";
	echo $XML;
	exit();

} else if ($_POST['country'] != '') {						# if we need to load the states of selected country
	$XML =	"<s>\n" .
		"   <xml>\n";

	if (file_exists('_locations')) {					# check if the directory exists
		chdir('_locations');						# change into the target directory
		if (file_exists($_POST['country'].'.txt')) {			# check if the file exists
			$fn = fopen($_POST['country'].'.txt','r');
			while (! feof($fn)) {
				$line = trim(fgets($fn));
				$part = explode('	', $line);
				$XML .= "	<s code='".$part[0]."'>".$part[1]."</s>\n";
			}
			fclose($fn);
		}
	}

	$XML .=	"   </xml>\n" .
		"</s>\n";

	$XML = str_replace("<s code=''></s>", '', $XML);			# removes any blank lines

	echo $XML;
	exit();

} else if ($_POST['load'] == 'countries') {					# if we need to load the countries of the world
	$XML =	"<s>\n" .
		"   <xml>\n";

	if (file_exists('_locations')) {					# check if the directory exists
		chdir('_locations');						# change into the target directory
		if (file_exists('countries.txt')) {				# check if the file exists
			$fn = fopen('countries.txt','r');
			while (! feof($fn)) {
				$line = trim(fgets($fn));
				$part = explode('	', $line);
				$XML .= "	<c code='".$part[0]."'>".$part[1]."</c>\n";
			}
			fclose($fn);
		}
	}

	$XML = str_replace("<c code=''></c>", '', $XML);			# removes any blank lines

	$XML .=	"   </xml>\n" .
		"</s>\n";

	echo $XML;
	exit();

} else {
	echo "<f><msg>The location was unable to be processed, please try again later.</msg></f>";
}

?>
