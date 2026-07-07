<?php
# gallery.php	provides the relevant page IO
#
# Created	2019/09/13 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
# Updated	2020/06/16 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
#
# Copyright 2019 blu Boards LLC


# Constant Definitions
define("MODULE",'Website');			# the name of this module (NOTE: this can be the same as the PROJECT constant in the envars.php file)
define("PREFIX",'');				# the prefix used with the database
define("SCRIPT",'gallery.php');			# the name of this script (for tracing bugs and automated messages)

# Module Requirements				  NOTE: MUST come below Module Constant Definitions
#require_once('../../sqlaccess');
require_once('../data/config.php');
require_once('./_mimemail.php');
require_once('./_global.php');

# Start or resume the PHP session		  NOTE: gains access to $_SESSION variables in this script
session_start();




// format the dates in UTC
$_ = gmdate("Y-m-d H:i:s",time());		# used this mannor so all the times will be the exact same (also see http://php.net/manual/en/function.gmdate.php)

header('Content-Type: text/xml; charset=utf-8');
echo "<?xml version='1.0' encoding='UTF-8'?>\n";




if ($_POST['action'] == 'show' && $_POST['target'] == 'pictures') {		# SHOW THE PRODUCT IMAGES
	# validate all submitted data
	if (! validate($_POST['SID'],40,'[^a-zA-Z0-9]')) { exit(); }
	if (! validate($_POST['username'],64,'[^a-zA-Z0-9_\-]')) { exit(); }
	if (! validate($_POST['path'],128,'![=<>;]')) { exit(); }
	if (! validate($_POST['prefix'],32,'![=<>;]')) { exit(); }

	if (! file_exists('../'.$_POST['path'])) {
		echo "<f><msg>The requested album does not appear to be valid.</msg></f>";
# currently unused
#		echo "<f><msg>The requested picture does not appear valid in the album.</msg></f>";
		exit();
	}

	# make some adjustments to the variables passed
	$_POST['path'] = str_replace("../", "", $_POST['path']);		# remove the ability for malicious activity to go back up the directory structure using multiple '../../../...'
	$_POST['path'] = '../'.$_POST['path'];					# adding the '../' will put us in the root of the directory structure
	$_POST['prefix'] = str_replace("../", "", $_POST['prefix']);

	chdir($_POST['path']);							# change into the target directory

	$count=0;
	$files='';
	foreach (glob($_POST['prefix']."*") as $file) {			# count how many prior files are present (for file naming purposes below)
		if ($file == '.' || $file == '..' || strpos($file, '.thumb.') !== false || is_dir($file)) { continue; }		# do NOT count '.', '..', thumbnails, or directories!!!

		$files .= $file.'|';
		$count++;
	}
	if ($files == '') { $count=1; $files = 'unknown.png,'; }		# if there are no files uploaded, specify the unknown graphic then

	echo "<s><data count='".$count."'>".substr($files,0,-1)."</data></s>";
	exit();




} else if ($_POST['action'] == 'list' && $_POST['target'] == 'pictures') {	# LIST THE IMAGE GROUPS (and images)
	# validate all submitted data
	if (! validate($_POST['SID'],40,'[^a-zA-Z0-9]')) { exit(); }
	if (! validate($_POST['username'],64,'[^a-zA-Z0-9_\-]')) { exit(); }
	if (! validate($_POST['path'],128,'![=<>;]')) { exit(); }
	if (! validate($_POST['prefix'],32,'![=<>;]')) { exit(); }

	if (! file_exists('../'.$_POST['path'])) {
		echo "<f><msg>There does not appear to be any images available in that album.</msg></f>";
		exit();
	}

	# make some adjustments to the variables passed
	$_POST['path'] = str_replace("../", "", $_POST['path']);		# remove the ability for malicious activity to go back up the directory structure using multiple '../../../...'
	$_POST['prefix'] = str_replace("../", "", $_POST['prefix']);

	$default = '';								# used to store the first group/album listed on website
	$group = '';								# used to store the iterated group/album name (for safeXML() call)


	echo "<s>\n";
	echo "   <xml>\n";

	# process all the custom groups/albums...
	foreach (new FilesystemIterator('../'.$_POST['path']) as $parent) {
		if ($parent->isDir()) {						# if we have reached a 'Group', then...
# currently unused
#			if ($default == '') { $default = $parent->getFilename(); }		# store the first group/album name
			$group = $parent->getFilename();			# since we can't pass '$parent->getFilename()', we have to store in a variable (not a constant)

			echo "	<group title=\"".safeXML($group)."\" path=\"".$_POST['path']."/".$parent->getFilename()."\">\n";

			$priority = [];						# reset these values
			$normal = [];

			# WARNING: this method is used so the ordering matches that of the _gallery plugin 'show' action
			$prior = getcwd();
			chdir('../'.$_POST['path'].'/'.$parent->getFilename());						# change into the target directory
			foreach (glob($_POST['prefix']."*") as $file) {							# count how many prior files are present (for file naming purposes below)
				if ($file == '.' || $file == '..' || strpos($file, '.thumb.') === false) { continue; }	# do NOT count '.', '..', or thumbnails!!!

				$i = -1;
				if (strpos($file, '-')) { $i = ltrim(substr($file,0,3), '0'); }
				if ($i >= 0) { $priority[$i] = $file; }
				else { $normal[] = $file; }
			}
			chdir($prior);

			# now cycle the images to display to the user
			for ($i=1; $i<=count($priority); $i++) { echo "		<image src=\"".$_POST['path']."/".$parent->getFilename()."/".$priority[$i]."\" />\n"; }
			for ($j=$i; $j<count($normal)+$i; $j++) { echo "		<image src=\"".$_POST['path']."/".$parent->getFilename()."/".$normal[$j-$i]."\" />\n"; }

			echo "	</group>\n";
		}
		continue;							# skip processing any root-level (aka 'Unsorted') images at this point, just focus on 'Grouped' images
	}

	# process the 'Unsorted' group/album...
	$priority = [];								# reset these values
	$normal = [];
	$prior = getcwd();
	chdir('../'.$_POST['path']);						# change into the target directory
	foreach (glob($_POST['prefix']."*") as $file) {				# count how many prior files are present (for file naming purposes below)
		if ($file == '.' || $file == '..' || strpos($file, '.thumb.') === false || is_dir($file)) { continue; }	# do NOT count '.', '..', thumbnails, or directories!!!

		$i = -1;
		if (strpos($file, '-')) { $i = ltrim(substr($file,0,3), '0'); }
		if ($i >= 0) { $priority[$i] = $file; }
		else { $normal[] = $file; }
	}
	chdir($prior);

	if (! empty($priority) || ! empty($normal)) {			# if there are images in the 'Unsorted' group/album, then include it too!
		echo "	<group title=\"Unsorted\" path=\"".$_POST['path']."\">\n";

		# now cycle the images to display to the user
		for ($i=1; $i<=count($priority); $i++) { echo "		<image src=\"".$_POST['path']."/".$priority[$i]."\" />\n"; }
		for ($j=$i; $j<count($normal)+$i; $j++) { echo "		<image src=\"".$_POST['path']."/".$normal[$j-$i]."\" />\n"; }

		echo "	</group>\n";
	}


	echo "   </xml>\n";
	echo "</s>\n";
	exit();

}

?>
