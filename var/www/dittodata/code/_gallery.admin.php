<?php
# gallery.admin.php	provides the relevant page IO
#
# Created	2019/10/16 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
# Updated	2020/07/23 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
#
# Copyright 2019 blu Boards LLC


# Constant Definitions
define("MODULE",'DFW');				# the name of this module (NOTE: this can be the same as the PROJECT constant in the envars.php file)
define("PREFIX",'');				# the prefix used with the database
define("SCRIPT",'gallery.admin.php');		# the name of this script (for tracing bugs and automated messages)

# Module Requirements				  NOTE: MUST come below Module Constant Definitions
require_once('../../sqlaccess');
require_once('../data/config.php');
if (file_exists('../data/config.'.strtolower(MODULE).'.php')) { require_once('../data/config.'.strtolower(MODULE).'.php'); }
require_once('./_mimemail.php');
require_once('./_global.php');

# Start or resume the PHP session		  NOTE: gains access to $_SESSION variables in this script
session_start();

# Function Definitions
function getThumbName($strFile)
	{ return substr($strFile, 0, strrpos($strFile,'.')).'.thumb'.substr($strFile, strrpos($strFile,'.')); }

function getNormalName($strFile)
	{ return str_replace('.thumb.', '.', $strFile); }




// format the dates in UTC
$_ = gmdate("Y-m-d H:i:s",time());		# used this mannor so all the times will be the exact same (also see http://php.net/manual/en/function.gmdate.php)

header('Content-Type: text/xml; charset=utf-8');
echo "<?xml version='1.0' encoding='UTF-8'?>\n";




# define common values for any encountered errors
$gbl_info['name']='Anonymous';							# define general info for any error generated below
$gbl_info['contact']='None';
$gbl_info['other']='None';




if ($_POST['action'] == 'refresh' && $_POST['target'] == 'listing') {		# REFRESH THE IMAGES LISTED IN THE DIRECTORY
	# validate all submitted data
	if (! validate($_POST['SID'],40,'[^a-zA-Z0-9]')) { exit(); }
	if (! validate($_POST['username'],64,'[^a-zA-Z0-9@\._\-]')) { exit(); }
	if (! validate($_POST['path'],128,'![=<>;]')) { exit(); }

	# make some adjustments to the variables passed
	$_POST['path'] = str_replace("../", "", $_POST['path']);		# remove the ability for malicious activity to go back up the directory structure using multiple '../../../...'
	$_POST['path'] = '../'.$_POST['path'];					# adding the '../' will put us in the root of the directory structure

	# if the path does not exist, create it! (to prevent unneccessary errors from occuring)
	if (! file_exists($_POST['path'])) {
		$gbl_errs['error'] = "Can not make the \"".$_POST['path']."\" album/directory.";
		$gbl_info['command'] = "mkdir(\"".$_POST['path']."\", 0775, true)";
		$gbl_info['values'] = 'none';
		if (! mkdir($_POST['path'], 0775, true)) {
			echo "<f><msg>An error occured while trying to create the new group!</msg></f>";
			exit();
		}
	}

	$XML =	"<s>\n" .
		"   <xml>\n";

	if (file_exists($_POST['path'])) {				# https://stackoverflow.com/questions/1086105/get-the-files-inside-a-directory/1086110#1086110
		foreach (new FilesystemIterator($_POST['path']) as $parent) {
			if ($parent->isDir()) {					# if we have reached a 'Group', then...
				$XML .=	"	<group name=\"".$parent->getFilename()."\">\n";
				foreach (new FilesystemIterator($_POST['path'].'/'.$parent->getFilename()) as $child) {
					if ($child->isDir()) { continue; }	#   skip any sub-directories (to prevent sub-folders of sub-folders of sub-folders, etc)

					$priority = 0;
					if (file_exists($_POST['path'].'/'.$parent->getFilename().'/'.getThumbName($child->getFilename()))) { continue; }	# skip any files that have thumbnails (so the thumbnails get shown instead)
					if (strpos($child->getFilename(), '-')) { $priority = ltrim(substr($child->getFilename(),0,3), '0'); }			# obtain the priority value (if one exists)
					$XML .=	"		<file priority='".$priority."'>".$child->getFilename()."</file>\n";
				}
				$XML .=	"	</group>\n";
				continue;					#   now go to the next item in the parent listing
			}
			continue;						#   skip processing any root-level (aka 'Unsorted') images at this point, just focus on 'Grouped' images
		}


		$XML .=	"	<unsorted>\n";
		foreach (new FilesystemIterator($_POST['path']) as $parent) {
			if ($parent->isDir()) { continue; }			# if we have reached a 'Group', then skip it (since it was processed above already)

			$priority = 0;
			if (file_exists($_POST['path'].getThumbName($parent->getFilename()))) { continue; }
			if (strpos($parent->getFilename(), '-')) { $priority = ltrim(substr($parent->getFilename(),0,3), '0'); }
			$XML .=	"		<file priority='".$priority."'>".$parent->getFilename()."</file>\n";
		}
		$XML .=	"	</unsorted>\n";
	}

	$XML .=	"   </xml>\n" .
		"</s>\n";

# REMOVED 2020/07/24 - this was removed here and added in the .js file
	# remove an empty 'Unsorted' group/album
	#$XML = str_replace("	<unsorted>\n	</unsorted>\n", "", $XML);

	echo $XML;
	exit();


} else if ($_POST['action'] == 'create' && $_POST['target'] == 'group') {	# CREATE A NEW GROUP OF IMAGES
	# validate all submitted data
	if (! validate($_POST['SID'],40,'[^a-zA-Z0-9]')) { exit(); }
	if (! validate($_POST['username'],64,'[^a-zA-Z0-9@\._\-]')) { exit(); }
	if (! validate($_POST['path'],128,'![=<>;]')) { exit(); }

	# make some adjustments to the variables passed
	$_POST['path'] = str_replace("../", "", $_POST['path']);		# remove the ability for malicious activity to go back up the directory structure using multiple '../../../...'
	$_POST['path'] = '../'.$_POST['path'];					# adding the '../' will put us in the root of the directory structure

	if (file_exists($_POST['path'])) {
		echo "<f><msg>There is a group that already exists with that name!</msg></f>";
	} else {
		$gbl_errs['error'] = "Can not make the \"".$_POST['path']."\" album/directory.";
		$gbl_info['command'] = "mkdir(\"".$_POST['path']."\", 0775, true)";
		$gbl_info['values'] = 'none';
		if (! mkdir($_POST['path'], 0775, true)) {
			echo "<f><msg>An error occured while trying to create the new group!</msg></f>";
			exit();
		}
		echo "<s><msg>The group was created successfully!</msg></s>";
	}
	exit();


} else if ($_POST['action'] == 'delete' && $_POST['target'] == 'group') {	# DELETE AN EXISTING GROUP OF IMAGES
	# validate all submitted data
	if (! validate($_POST['SID'],40,'[^a-zA-Z0-9]')) { exit(); }
	if (! validate($_POST['username'],64,'[^a-zA-Z0-9@\._\-]')) { exit(); }
	if (! validate($_POST['path'],128,'![=<>;]')) { exit(); }

	# make some adjustments to the variables passed
	$_POST['path'] = str_replace("../", "", $_POST['path']);		# remove the ability for malicious activity to go back up the directory structure using multiple '../../../...'
	$_POST['path'] = '../'.$_POST['path'];					# adding the '../' will put us in the root of the directory structure

	if (! file_exists($_POST['path'])) {
		echo "<f><msg>The requested group does not appear to exist!</msg></f>";
	} else {
		if (delTree($_POST['path']))
			{ echo "<s><msg>The image group has been deleted successfully</msg></s>"; }
	}
	exit();


} else if ($_POST['action'] == 'rename' && $_POST['target'] == 'group') {	# RENAME AN EXISTING GROUP OF IMAGES
	# validate all submitted data
	if (! validate($_POST['SID'],40,'[^a-zA-Z0-9]')) { exit(); }
	if (! validate($_POST['username'],64,'[^a-zA-Z0-9_\-]')) { exit(); }
	if (! validate($_POST['path'],128,'![=<>;]')) { exit(); }

	# make some adjustments to the variables passed
	$_POST['path'] = str_replace("../", "", $_POST['path']);		# remove the ability for malicious activity to go back up the directory structure using multiple '../../../...'
	$_POST['path'] = '../'.$_POST['path'];					# adding the '../' will put us in the root of the directory structure

	if (file_exists($_POST['path']."/".$_POST['new'])) {
		echo "<f><msg>The requested new group already exist! Please try another name.</msg></f>";
	} else {
		$gbl_errs['error'] = "Can not rename the requested directory to the new name.";
		$gbl_info['command'] = "rename(\"".$_POST['path'].$_POST['old']."\", \"".$_POST['path'].$_POST['new']."\")";
		$gbl_info['values'] = '[s] '.$_POST['path'].', [s] '.$_POST['old'].', [s] '.$_POST['new'];
		rename($_POST['path'].$_POST['old'], $_POST['path'].$_POST['new']);
		echo "<s><msg>The group has been renamed successfully!</msg></s>";
	}
	exit();


} else if ($_POST['action'] == 'implement' && $_POST['target'] == 'changes') {	# IMPLEMENT THE VARIOUS CHANGES (move, delete, re-order)
	# validate all submitted data
	if (! validate($_POST['SID'],40,'[^a-zA-Z0-9]')) { exit(); }
	if (! validate($_POST['username'],64,'[^a-zA-Z0-9@\._\-]')) { exit(); }
	if (! validate($_POST['path'],128,'![=<>;]')) { exit(); }

	# make some adjustments to the variables passed
	$_POST['path'] = str_replace("../", "", $_POST['path']);		# remove the ability for malicious activity to go back up the directory structure using multiple '../../../...'
	$_POST['path'] = '../'.$_POST['path'];					# adding the '../' will put us in the root of the directory structure

	$gbl_errs['error'] = "Can not change into the requested directory while in \"".getcwd()."\".";
	$gbl_info['command'] = "chdir(\"".$_POST['path']."\")";
	$gbl_info['values'] = '[s] '.$_POST['path'];
	chdir($_POST['path']);							# change into the directory containing the pictures	WARNING: this is NOT going into the individual groups/albums, just the root of that directory

	foreach ($_POST as $key => $val) {					# now perform checks against all dynamic values
		if (strpos($key, 'btn') !== false) { continue; }		# skip any buttons
		if (strpos($key, 'txt') !== false) { continue; }		# skip any textboxes
		if ($key == 'action' || $key == 'target' || $key == 'username' || $key == 'SID' || $key == 'path') { continue; }		# skip any of these values
		if ($val == '') { continue; }					# if we've made it here, then the $key value is for an image, but skip any that don't have any action to perform

		$key = str_replace(':', ' ', str_replace(':-:', '/', str_replace('_', '.', $key)));	# first convert '_ > .', then ':-: > /', and finally ': > ' to work around php converting some HTML5 id values into other characters
		if ($val == '_priority_') {					# if we are adding an image as a priority, then...		https://stackoverflow.com/questions/12801370/count-how-many-files-in-directory-php
			if (strpos($key, '/')) { $dir = substr($key, 0, strrpos($key, '/')+1); } else { $dir = ''; }		# if there is a directory (group), then store it!
			if (strpos($key, '/')) { $bare = substr($key,strrpos($key,'/')+1); } else { $bare = $key; }		# store the bare filename without any preceeding directory (group)
			$count = 1;						# store the count of files returned by 'glob'
			$files = glob($dir."???-??????????????.[jpegifbmn34]*");						# matches file extensions: jpeg, jpg, gif, bmp, png, mpeg[3|4], mpg[3|4], mp[3|4] 	https://stackoverflow.com/questions/7388623/rename-all-files-in-order-in-php
			if ($files) { $count = count($files) + 1; }

			$gbl_errs['error'] = "Can not rename \"".$key."\" as a priority file.";
			$gbl_info['command'] = "rename(\"".$key."\", \"".$dir.str_pad($count, 3, '0', STR_PAD_LEFT)."-".$bare."\")";
			$gbl_info['values'] = '[s] '.$key.', [s] '.$dir.', [s] '.$bare.', [i] '.$count;
			rename($key, $dir.str_pad($count, 3, '0', STR_PAD_LEFT).'-'.$bare);
			if (strpos($key, '.thumb.')) {
				$gbl_errs['error'] = "Can not rename \"".getNormalName($key)."\" as a priority thumbnail file.";
				$gbl_info['command'] = "rename(\"".getNormalName($file)."\", \"".$dir.str_pad($count, 3, '0', STR_PAD_LEFT).'-'.getNormalName($bare)."\")";
				$gbl_info['values'] = '[s] '.$key.', [s] '.$dir.', [s] '.$bare.', [i] '.$count;
				rename(getNormalName($key), $dir.str_pad($count, 3, '0', STR_PAD_LEFT).'-'.getNormalName($bare));
			}


		} else if ($val == '_normal_') {				# if we are moving an image from priority to normal status again, then...
			$full = $key;						# this is so that the 'unlink' call at the bottom works regardless if the 'if' below is entered or not
			if (strpos($full, '.thumb.')) { $full = getNormalName($full); }						# remove '.thumb.' from the filename (if that was what was submitted) so the code works as originally designed
			if (strpos($full, '/')) { $dir = substr($full, 0, strrpos($full, '/')+1); } else { $dir = ''; }		# if there is a directory (group), then store it!

			if (strpos($full, '-')) {				# if the file that was just deleted was a priority ranked, then...	NOTE: this is the same block of code as found above, just using $full instead of $key and an additional 'if' in the 'foreach'
				$offset = 1;
				#$dir = '';					# this is commented out so as to not overwrite the value previously assigned
				if (strpos($full, '/')) { $bare = substr($full, strrpos($full,'/')+5); } else { $bare = substr($full,4); }
				#$full = '';					# this is commented out so as to not overwrite the value previously assigned
				$name = '';
				$count = 1;
				$files = glob($dir."???-??????????????.[jpegifbmn34]*");					# matches file extensions: jpeg, jpg, gif, bmp, png, mpeg[3|4], mpg[3|4], mp[3|4]
				if ($files) { $count = count($files) + 1; }

				foreach($files as $i => $file) {
					if (strpos($file, '/')) { $name = substr($file, strrpos($file, '/')+5); } else { $name = substr($file, 4); }
					if ($name == $bare) {			# if we have reached the submitted file that needs to be adjusted, then...
						$full = $file;			#   set the value to the "updated" name of the iterated file (since it might have already been renamed/deleted from a prior action)
						continue;			#   skip it for now so the 'unlink' call at the bottom will work correctly
					}
					if ($val == $offset) { $offset++; }	# if we HAVE reached the to-be ranking value of the specified file, then increment the offset to make this placement value available for later code

					# if we have made it here, we need to adjust the ranking placement of the file either forward or backward (as indicated by the 'offset' value)
					$gbl_errs['error'] = "Can not change \"".$file."\" ranking placement (iterated).";
					$gbl_info['command'] = "rename(\"".$file."\", \"".$dir.str_pad($i+1+$offset, 3, '0', STR_PAD_LEFT).'-'.substr($file, strrpos($file, '-')+1)."\")";
					$gbl_info['values'] = '[s] '.$file.', [s] '.$dir.', [i] '.$i.', [i] '.$offset;
					rename($file, $dir.str_pad($offset, 3, '0', STR_PAD_LEFT).'-'.substr($file, strrpos($file, '-')+1));
					if (file_exists(getThumbName($file))) {
						$gbl_errs['error'] = "Can not change \"".getThumbName($file)."\" ranking placement (iterated thumbnail).";
						$gbl_info['command'] = "rename(\"".getThumbName($file)."\", \"".$dir.str_pad($offset, 3, '0', STR_PAD_LEFT).'-'.substr(getThumbName($file), strrpos(getThumbName($file), '-')+1)."\")";
						$gbl_info['values'] = '[s] '.$file.', [s] '.$dir.', [i] '.$i.', [i] '.$offset;
						rename(getThumbName($file), $dir.str_pad($offset, 3, '0', STR_PAD_LEFT).'-'.substr(getThumbName($file), strrpos(getThumbName($file), '-')+1));
					}

					$offset++;
				}
			}

			if (strpos($full, '/')) { $bare = substr($full,strrpos($full,'/')+1); } else { $bare = $full; }		# store the bare filename without any preceeding directory (group)

			$gbl_errs['error'] = "Can not rename \"".$full."\" as a normal file.";
			$gbl_info['command'] = "rename(\"".$full."\", \"".$dir.substr($bare, strrpos($bare, '-')+1)."\")";
			$gbl_info['values'] = '[s] '.$full.', [s] '.$dir.', [s] '.$bare;
			rename($full, $dir.substr($bare, strrpos($bare, '-')+1));
			if (file_exists(getThumbName($full))) {
				$gbl_errs['error'] = "Can not rename \"".getThumbName($full)."\" as a normal thumbnail file.";
				$gbl_info['command'] = "rename(\"".getThumbName($full)."\", \"".$dir.substr(getThumbName($bare), strrpos(getThumbName($bare), '-')+1)."\")";
				$gbl_info['values'] = '[s] '.$full.', [s] '.$dir.', [s] '.$bare;
				rename(getThumbName($full), $dir.substr(getThumbName($bare), strrpos(getThumbName($bare), '-')+1));
			}


		} else if (is_numeric($val)) {					# if we are re-ordering a prioritized file, then...
			if (strpos($key, '.thumb.')) { $key = getNormalName($key); }						# remove '.thumb.' from the filename (if that was what was submitted) so the code works as originally designed

			$offset = 1;						# the offset to increase/decrease the new filename index by; the default is to increment (e.g. 2nd to 3rd place)
			if (strpos($key, '/')) { $dir = substr($key, 0, strrpos($key, '/')+1); } else { $dir = ''; }		# if there is a directory (group), then store it!
			if (strpos($key, '/')) { $bare = substr($key,strrpos($key,'/')+5); } else { $bare = substr($key,4); }	# store the bare filename without any preceeding ranking placement value (e.g. '004-')
			$full = '';						# later used to store the new filename with the updated preceeding ranking placement value
			$name = '';						# later used to store the iterated filename without any preceeding ranking placement value
			$count = 1;						# store the count of files returned by 'glob'
			$files = glob($dir."???-??????????????.[jpegifbmn34]*");						# matches file extensions: jpeg, jpg, gif, bmp, png, mpeg[3|4], mpg[3|4], mp[3|4]
			if ($files) { $count = count($files) + 1; }

			foreach($files as $i => $file) {			# NOTE: all $i values have a plus one since this 'foreach' starts at 0, but our numbering starts at 1
				if (strpos($file, '/')) { $name = substr($file, strrpos($file, '/')+5); } else { $name = substr($file, 4); }
				if ($bare == $name) {				# if we have reached the file that needs ranking adjustment (without the preceeding ranking placement value since that changes), then...
					$full = $file;				#   store the complete new filename with updated preceeding ranking placement value
					continue;				#   now go to the next file
				}
				if ($val == $offset) { $offset++; }		# if we HAVE reached the to-be ranking value of the specified file, then increment the offset to make this placement value available for later code

				# if we have made it here, we need to adjust the ranking placement of the file either forward or backward (as indicated by the 'offset' value)
				$gbl_errs['error'] = "Can not change \"".$file."\" ranking placement (iterated).";
				$gbl_info['command'] = "rename(\"".$file."\", \"".$dir.str_pad($i+1+$offset, 3, '0', STR_PAD_LEFT).'-'.substr($file, strrpos($file, '-')+1)."\")";
				$gbl_info['values'] = '[s] '.$file.', [s] '.$dir.', [i] '.$i.', [i] '.$offset;
				rename($file, $dir.str_pad($offset, 3, '0', STR_PAD_LEFT).'-'.substr($file, strrpos($file, '-')+1));
				if (file_exists(getThumbName($file))) {
					$gbl_errs['error'] = "Can not change \"".getThumbName($file)."\" ranking placement (iterated thumbnail).";
					$gbl_info['command'] = "rename(\"".getThumbName($file)."\", \"".$dir.str_pad($offset, 3, '0', STR_PAD_LEFT).'-'.substr(getThumbName($file), strrpos(getThumbName($file), '-')+1)."\")";
					$gbl_info['values'] = '[s] '.$file.', [s] '.$dir.', [i] '.$i.', [i] '.$offset;
					rename(getThumbName($file), $dir.str_pad($offset, 3, '0', STR_PAD_LEFT).'-'.substr(getThumbName($file), strrpos(getThumbName($file), '-')+1));
				}

				$offset++;					# now increment this value to maintain contiguous numerical order
			}
			if ($offset < $val) { $val = $offset; }			# if, after all the other changes, the specified priority ranking is too low (e.g. there is only 2 images remaining, but the user selected to be in the 4th spot), the set to the last position (e.g. 3)

			# now that we have incremented/decremented the other existing files (to maintain consecutive numeric prefixes), we now need to rename the file that has been specified to have its placement adjusted
			$gbl_errs['error'] = "Can not change \"".$file."\" ranking placement (specified).";
			$gbl_info['command'] = "rename(\"".$file."\", \"".$dir.str_pad($val, 3, '0', STR_PAD_LEFT).'-'.substr($full, strrpos($full, '-')+1)."\")";
			$gbl_info['values'] = '[s] '.$full.', [s] '.$dir.', [i] '.$val;
			rename($full, $dir.str_pad($val, 3, '0', STR_PAD_LEFT).'-'.substr($full, strrpos($full, '-')+1));
			if (file_exists(getThumbName($full))) {
				$gbl_errs['error'] = "Can not change \"".getThumbName($full)."\" ranking placement (specified thumbnail).";
				$gbl_info['command'] = "rename(\"".getThumbName($full)."\", \"".$dir.str_pad($val, 3, '0', STR_PAD_LEFT).'-'.substr(getThumbName($full), strrpos(getThumbName($full), '-')+1)."\")";
				$gbl_info['values'] = '[s] '.$full.', [s] '.$dir.', [i] '.$val;
				rename(getThumbName($full), $dir.str_pad($val, 3, '0', STR_PAD_LEFT).'-'.substr(getThumbName($full), strrpos(getThumbName($full), '-')+1));
			}


		} else if ($val == '_delete_') {				# if we are deleting the iterated file, then...
			$full = $key;						# this is so that the 'unlink' call at the bottom works regardless if the 'if' below is entered or not
			if (strpos($full, '.thumb.')) { $full = getNormalName($full); }		# remove '.thumb.' from the filename (if that was what was submitted) so the code works as originally designed

			if (strpos($full, '-')) {				# if the file that was just deleted was a priority ranked, then...	NOTE: this is the same block of code as found above, just using $full instead of $key and an additional 'if' in the 'foreach'
				$offset = 1;
				if (strpos($full, '/')) { $dir = substr($full, 0, strrpos($full, '/')+1); } else { $dir = ''; }
				if (strpos($full, '/')) { $bare = substr($full,strrpos($full,'/')+5); } else { $bare = substr($full,4); }
				#$full = '';					# this is commented out so as to not overwrite the value previously assigned
				$name = '';
				$count = 1;
				$files = glob($dir."???-??????????????.[jpegifbmn34]*");				# matches file extensions: jpeg, jpg, gif, bmp, png, mpeg[3|4], mpg[3|4], mp[3|4]
				if ($files) { $count = count($files) + 1; }

				foreach($files as $i => $file) {
					if (strpos($file, '/')) { $name = substr($file, strrpos($file, '/')+5); } else { $name = substr($file, 4); }
					if ($name == $bare) {			# if we have reached the submitted file that needs to be adjusted, then...
						$full = $file;			#   set the value to the "updated" name of the iterated file (since it might have already been renamed/deleted from a prior action)
						continue;			#   skip it for now so the 'unlink' call at the bottom will work correctly
					}
					if ($val == $offset) { $offset++; }

					# if we have made it here, we need to adjust the ranking placement of the file either forward or backward (as indicated by the 'offset' value)
					$gbl_errs['error'] = "Can not change \"".$file."\" ranking placement (iterated).";
					$gbl_info['command'] = "rename(\"".$file."\", \"".$dir.str_pad($i+1+$offset, 3, '0', STR_PAD_LEFT).'-'.substr($file, strrpos($file, '-')+1)."\")";
					$gbl_info['values'] = '[s] '.$file.', [s] '.$dir.', [i] '.$i.', [i] '.$offset;
					rename($file, $dir.str_pad($offset, 3, '0', STR_PAD_LEFT).'-'.substr($file, strrpos($file, '-')+1));
					if (file_exists(getThumbName($file))) {
						$gbl_errs['error'] = "Can not change \"".getThumbName($file)."\" ranking placement (iterated thumbnail).";
						$gbl_info['command'] = "rename(\"".getThumbName($file)."\", \"".$dir.str_pad($offset, 3, '0', STR_PAD_LEFT).'-'.substr(getThumbName($file), strrpos(getThumbName($file), '-')+1)."\")";
						$gbl_info['values'] = '[s] '.$file.', [s] '.$dir.', [i] '.$i.', [i] '.$offset;
						rename(getThumbName($file), $dir.str_pad($offset, 3, '0', STR_PAD_LEFT).'-'.substr(getThumbName($file), strrpos(getThumbName($file), '-')+1));
					}

					$offset++;
				}
			}

			$gbl_errs['error'] = "Can not delete the \"".$full."\" file.";
			$gbl_info['command'] = "unlink(\"".$full."\")";
			$gbl_info['values'] = '[s] '.$full;
			unlink($full);
			if (file_exists(getThumbName($full))) {
				$gbl_errs['error'] = "Can not delete the \"".$full."\" thumbnail file.";
				$gbl_info['command'] = "unlink(\"".getThumbName($full)."\")";
				$gbl_info['values'] = '[s] '.getThumbName($full);
				unlink(getThumbName($full));
			}


		} else {							# if we are moving the iterated file into a group, then...
			$full = $key;						# this is so that the 'unlink' call at the bottom works regardless if the 'if' below is entered or not
			if (strpos($full, '.thumb.')) { $full = getNormalName($full); }		# remove '.thumb.' from the filename (if that was what was submitted) so the code works as originally designed

			if (strpos($full, '-')) {				# if the file that was just deleted was a priority ranked, then...	NOTE: this is the same block of code as found above, just using $full instead of $key and an additional 'if' in the 'foreach'
				$offset = 1;
				if (strpos($full, '/')) { $dir = substr($full, 0, strrpos($full, '/')+1); } else { $dir = ''; }
				if (strpos($full, '/')) { $bare = substr($full,strrpos($full,'/')+5); } else { $bare = substr($full,4); }
				#$full = '';					# this is commented out so as to not overwrite the value previously assigned
				$name = '';
				$count = 1;
				$files = glob($dir."???-??????????????.[jpegifbmn34]*");				# matches file extensions: jpeg, jpg, gif, bmp, png, mpeg[3|4], mpg[3|4], mp[3|4]
				if ($files) { $count = count($files) + 1; }

				foreach($files as $i => $file) {
					if (strpos($file, '/')) { $name = substr($file, strrpos($file, '/')+5); } else { $name = substr($file, 4); }
					if ($name == $bare) {			# if we have reached the submitted file that needs to be adjusted, then...
						$full = $file;			#   set the value to the "updated" name of the iterated file (since it might have already been renamed/deleted from a prior action)
						continue;			#   skip it for now so the 'unlink' call at the bottom will work correctly
					}
					if ($val == $offset) { $offset++; }

					# if we have made it here, we need to adjust the ranking placement of the file either forward or backward (as indicated by the 'offset' value)
					$gbl_errs['error'] = "Can not change \"".$file."\" ranking placement (iterated).";
					$gbl_info['command'] = "rename(\"".$file."\", \"".$dir.str_pad($i+1+$offset, 3, '0', STR_PAD_LEFT).'-'.substr($file, strrpos($file, '-')+1)."\")";
					$gbl_info['values'] = '[s] '.$file.', [s] '.$dir.', [i] '.$i.', [i] '.$offset;
					rename($file, $dir.str_pad($offset, 3, '0', STR_PAD_LEFT).'-'.substr($file, strrpos($file, '-')+1));
					if (file_exists(getThumbName($file))) {
						$gbl_errs['error'] = "Can not change \"".getThumbName($file)."\" ranking placement (iterated thumbnail).";
						$gbl_info['command'] = "rename(\"".getThumbName($file)."\", \"".$dir.str_pad($offset, 3, '0', STR_PAD_LEFT).'-'.substr(getThumbName($file), strrpos(getThumbName($file), '-')+1)."\")";
						$gbl_info['values'] = '[s] '.$file.', [s] '.$dir.', [i] '.$i.', [i] '.$offset;
						rename(getThumbName($file), $dir.str_pad($offset, 3, '0', STR_PAD_LEFT).'-'.substr(getThumbName($file), strrpos(getThumbName($file), '-')+1));
					}

					$offset++;
				}
			}

			if ($val == 'Unsorted') { $val = '.'; }			#   if we are moving into the 'Unsorted' Group, then we need to change the value to the root level
			if (strpos($full, '/')) { $dir = substr($full, 0, strrpos($full, '/')+1); } else { $dir = ''; }		# if there is a directory (group), then store it!
			if (strpos($full, '/')) { $bare = substr($full,strrpos($full,'/')+1); } else { $bare = $full; }		# store the bare filename without any preceeding directory (group)
			if (strpos($bare, '-')) { $bare = substr($bare,strrpos($bare,'-')+1); }					# also remove any priority ranking value from the filename

			# move the file
			$gbl_errs['error'] = "Can not move \"".$full."\" into the \"".$val."\" group.";
			$gbl_info['command'] = "rename(\"".$full."\", \"".$val.'/'.$bare."\")";
			$gbl_info['values'] = '[s] '.$full.', [s] '.$dir.', [s] '.$bare.', [s] '.$val;
			rename($full, $val.'/'.$bare);
			if (file_exists(getThumbName($full))) { rename(getThumbName($full), $val.'/'.getThumbName($bare)); }
		}
	}

	echo "<s><msg>All changes have been implemented successfully!</msg></s>";
	exit();


}
?>
