// _filedrop.js	the associated javascript for the filedrop upload plugin
//
// Created	2014-06-16 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
// Updated	2021-04-01 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
//
// Unless a valid Cliquesoft Private License (CPLv1) has been purchased for your
// device, this software is licensed under the Cliquesoft Public License (CPLv2)
// as found on the Cliquesoft website at www.cliquesoft.org.
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
// FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.


var intFileDropDelay = 0;




function initFileDrop(sFileDrop,sCheckID,sCheckVal,sPath,sPathExt,sRename,iOverwrite,sThumb,iSizeLimit,CB='') {
// initializes a filedrop instance within the opened module
// sFileDrop	the id of the filedrop instance that needs to initialized
// sCheckID	the id of the form object that needs to have a value (sCheckVal) checked before allowing uploads; blank disables
// sCheckVal	the default value of the form object (sCheckID) that prevents uploads
// sPath	the path where the uploads are going to be stored (this needs to be pre-escaped)	WARNING: end the path with a trailing '/'!!!
// sPathExt	the name of the form object(s) to extended the sPath by					WARNING: end the path with a trailing '/'!!!
//		NOTES:
//		- this value can also be used for variable parts of the path and can contain of any (mix) of the values listed on the following line!
//		- the values can be strings (as element id's), variables (via '!Variable'), attributes (via 'element>attribute'), or reference a global variable (via '@Variable' for entire path value)
//		- this can contain multiple values separated via the pipe (e.g. 'txtUsername|cmbModule')
// sRename	if the uploaded file should be renamed after it is uploaded (preserving the extension); can supply a name or a special value: EPOCH (YYYYMMDDhhmmss), DATE (YYYYMMDD), TIME (hhmmss)
// iOverwrite	if the uploaded file should overwrite one that exists with the same name; 0=no, 1=yes, 2=yes despite a different extension
// sThumb	if the uploaded file should have a thumbnail created for it; to enable this functionality pass the dimensions desired as the value in WxH format (e.g. 800x600)
// iSizeLimit	checks that the file being uploaded is less than the limit passed (in bytes); 0 disables
// [callback]	a string or function to call once the upload has been completed
//		NOTES:
//		- if the value is a string, several values will dynamically be replaced by actual values:
//			'FILENAME' will be replaced by the actual filename just uploaded
//			'FILESIZE' will be replaced by the actual file size
//			'CRCHASH' will be replaced by the CRC32B hash of the file just uploaded
//			'MD5HASH' will be replaced by the MD5 hash of the file just uploaded
//			'SHAHASH' will be replaced by the SHA256 hash of the file just uploaded
	var options = {iframe: {url: 'code/_filedrop.php'}};					// Tell FileDrop we can deal with iframe uploads using this URL
	var zone = new FileDrop(sFileDrop, options);						// Attach FileDrop to an area ('zone' is an ID but you can also give a DOM node)

	// Do something when a user chooses or drops a file:
	zone.event('send', function(files) {
		// if a value was passed to check -AND- only allow uploads if a module record is loaded...
		if (sCheckID != '') {
			if (document.getElementById(sCheckID).type == "text") {
				if (document.getElementById(sCheckID).value == sCheckVal) {
					alert("You must enter a value before uploads are allowed.");
					return false;
				}
			} else if (document.getElementById(sCheckID).type == "select" || document.getElementById(sCheckID).type == "select-one" || document.getElementById(sCheckID).type == "select-multiple") {
				if (document.getElementById(sCheckID).selectedIndex == sCheckVal) {
					alert("You must select a record before uploads are allowed.");
					return false;
				}
			}
		}

		// if we've made it here, we're clear to begin uploads!
		files.each(function(file) {							// Depending on browser support files (FileList) might contain multiple items.
			if (iSizeLimit > 0 && iSizeLimit < file.size) {				// check that the iterated file is within the size limits (if provided)		https://stackoverflow.com/questions/4112575/client-checking-file-size-using-html5
				alert("The size of the \""+file.name+"\" file is larger than the limit allowed.");
				return false;
			}

			var index = document.getElementById(sFileDrop).getElementsByTagName('div').length-1;	// this is used so that each file upload is independent

			// create a file upload icon with progress bar in the FileDrop <div>
			document.getElementById(sFileDrop).innerHTML += "<div id='divFileDrop"+index+"' class='divFileDrop' title=\""+file.name+"\" data-hashes='Uploading...' onMouseOver=\"toggleHashes('show',this,event);\" onMouseOut=\"toggleHashes('hide',this,event);\"><img src='home/"+gbl_nameUser+"/imgs/upload.png' onMouseOver=\"toggleHashes('show',this,event);\" onMouseOut=\"toggleHashes('hide',this,event);\" /><span id='progressBar"+index+"' class='spanProgressBar'></span></div>"

			// Reset the progress when a new upload starts
			file.event('xhrSend', function(){ fd.byID('progressBar'+index).style.width = 0; })

			// now queue the uploads
			if (sRename != 'EPOCH' && sRename != 'DATE' && sRename != 'TIME') {							// if we are not renaming the files, then there is no reason to queue/delay the upload, so process them immediately
				sendFileDrop(sFileDrop,sCheckID,sCheckVal,sPath,sPathExt,sRename,iOverwrite,sThumb,iSizeLimit,CB,file,index);
			} else {								// otherwise we need to queue/delay, by 1 second, each upload so the generated name does not overwrite another uploaded file
				setTimeout(function(){sendFileDrop(sFileDrop,sCheckID,sCheckVal,sPath,sPathExt,sRename,iOverwrite,sThumb,iSizeLimit,CB,file,index);}, intFileDropDelay);
				intFileDropDelay += 1000;
			}
		});
		intFileDropDelay = 0;								// this reset the value so another batch of uploads starts immediately instead of what the value ended with in the above .each loop
	});

	// React on successful iframe fallback upload (this is a separate mechanism for proper AJAX upload, hence another handler)
	zone.event('iframeDone', function(xhr){
		// if the repsonse contains 'ERROR', then...
		if (xhr.responseText.substr(0, 8) == '<f><msg>') {
			alert(xhr.responseText.substr(8, xhr.responseText.length - 18));
			return false;
		}
		if (xhr.responseText.substr(0, 7) == 'ERROR: ') {
			alert('The following error has occurred while uploading the file(s):\n\n' + xhr.responseText.substr(7));
			return false;
		}
		// otherwise, we had a successful upload, so...
		var RESPONSE = xhr.responseText.split("\n");
		var NAME = RESPONSE[1].substring((RESPONSE[1].indexOf("Stored name: ")+13));
		var SIZE = RESPONSE[2].substring((RESPONSE[2].indexOf("Size: ")+6));
		var CRC32B = RESPONSE[3].substring((RESPONSE[3].indexOf("CRC32B: ")+8));
		var MD5 = RESPONSE[4].substring((RESPONSE[4].indexOf("MD5: ")+5));
		var SHA256 = RESPONSE[5].substring((RESPONSE[5].indexOf("SHA256: ")+8));

		//alert('You have successfully uploaded the file(s) with these details:\n\n' + xhr.responseText);
		document.getElementById('divFileDrop'+index).setAttribute('data-hashes',xhr.responseText.replace(/[\n]/g,'<br />'));

		if (CB != '') {									// if we have a callback function, then...
			if (typeof CB === "function") {						//   if we have something like "function(){whatever(a,b,c);}" as this value, then call it! Also you can reference the just-uploaded-filename via the passed value (e.g. initFileDrop(...,function(NAME){alert(NAME);}) )
				CB(NAME,SIZE,CRC32B,MD5,SHA256);
			} else {								//   otherwise, we need to eval because the function was passed as a string (e.g. "whatever(a,b,c)") while replacing any reference to special names
				var objMap = { FILENAME:NAME, FILESIZE:SIZE, CRCHASH:CRC32B, MD5HASH:MD5, SHAHASH:SHA256 };	// https://stackoverflow.com/questions/15604140/replace-multiple-strings-with-multiple-other-strings
				var reMatch = new RegExp(Object.keys(objMap).join("|"),"g");
				CB = CB.replace(reMatch, function(matched){ return objMap[matched]; });

				eval(CB);
			}
		}
	});

	// A bit of sugar - toggling multiple selection
	fd.addEvent(fd.byID('multiple'), 'change', function(e){
		zone.multiple(e.currentTarget || e.srcElement.checked);
	});
}


function sendFileDrop(sFileDrop,sCheckID,sCheckVal,sPath,sPathExt,sRename,iOverwrite,sThumb,iSizeLimit,CB,file,index) {
// responsible for actually sending the file(s)
	// Update progress when browser reports it
	file.event('progress', function(current, total){
		var width = current / total * 100 + '%'
		fd.byID('progressBar'+index).style.width = width;
	})

	// React to errors:
	file.event('error', function(e, xhr){
		alert('The following error has occurred while uploading the file(s):\n\n' + xhr.status + ': ' + xhr.statusText);
	})

	// React on successful AJAX upload:
	file.event('done', function(xhr){
		// if the repsonse contains 'ERROR', then...
		if (xhr.responseText.substr(0, 8) == '<f><msg>') {
			alert(xhr.responseText.substr(8, xhr.responseText.length - 18));
			return false;
		}
		if (xhr.responseText.substr(0, 7) == 'ERROR: ') {
			alert('The following error has occurred while uploading the file(s):\n\n' + xhr.responseText.substr(7));
			return false;
		}
		// otherwise, we had a successful upload, so...
		var RESPONSE = xhr.responseText.split("\n");
		var NAME = RESPONSE[1].substring((RESPONSE[1].indexOf("Stored name: ")+13));
		var SIZE = RESPONSE[2].substring((RESPONSE[2].indexOf("Size: ")+6));
		var CRC32B = RESPONSE[3].substring((RESPONSE[3].indexOf("CRC32B: ")+8));
		var MD5 = RESPONSE[4].substring((RESPONSE[4].indexOf("MD5: ")+5));
		var SHA256 = RESPONSE[5].substring((RESPONSE[5].indexOf("SHA256: ")+8));

		fd.byID('progressBar'+index).style.width = '100%';				// so the progress bar always ends at 100%

		// 'this' here points to fd.File instance that has triggered the event.
		//alert('You have successfully uploaded the file(s) with these details:\n\n' + xhr.responseText);
		document.getElementById('divFileDrop'+index).setAttribute('data-hashes',xhr.responseText.replace(/[\n]/g,'<br />'));

		if (CB != '') {									// if we have a callback function, then...
			if (typeof CB === "function") {						//   if we have something like "function(){whatever(a,b,c);}" as this value, then call it! Also you can reference the just-uploaded-filename via the passed value (e.g. initFileDrop(...,function(NAME){alert(NAME);}) )
				CB(NAME,SIZE,CRC32B,MD5,SHA256);
			} else {								//   otherwise, we need to eval because the function was passed as a string (e.g. "whatever(a,b,c)") while replacing any reference to special names
				var objMap = { FILENAME:NAME, FILESIZE:SIZE, CRCHASH:CRC32B, MD5HASH:MD5, SHAHASH:SHA256 };	// https://stackoverflow.com/questions/15604140/replace-multiple-strings-with-multiple-other-strings
				var reMatch = new RegExp(Object.keys(objMap).join("|"),"g");
				CB = CB.replace(reMatch, function(matched){ return objMap[matched]; });

				eval(CB);
			}
		}
	});

	// Send the file:
	var ext = '';
	if (sPathExt != '') {
		var obj = sPathExt.split('|');							// if the developer needs to have multiple form object values added to the upload path, then obtain each objects name here
		for (var I=0; I<obj.length; I++) {						// now go through each object and add it's value to the path
			if (obj[I].indexOf('@') == 0)						//   if we need to use a global variable...
				{ ext += eval(obj[I].substring(1)) + '/'; }
			else if (obj[I].indexOf('!') == 0)					//   if we need to use a variable in a '|' separated list...
				{ ext += eval(obj[I].substring(1)) + '/'; }
			else if (obj[I].indexOf('>') > -1)					//   if we need to use an elements' attribute value...
				{ ext += document.getElementById(obj[I].split('>')[0]).getAttribute(obj[I].split('>')[1]) + '/'; }
			else if (document.getElementById(obj[I]).type == "text" || document.getElementById(obj[I]).type == "hidden")
				{ ext += document.getElementById(obj[I]).value + '/'; }
			else if (document.getElementById(obj[I]).type == "select" || document.getElementById(obj[I]).type == "select-one" || document.getElementById(obj[I]).type == "select-multiple")
				{ ext += document.getElementById(obj[I]).options[document.getElementById(obj[I]).selectedIndex].value + '/'; }
		}
	}
	// WARNING:	We can NOT use POST to send additional data since the file stream is using that to send the file.	http://filedropjs.org/#scusdat
	//		As a result, do NOT send any private values as they will be exposed in the URL!!!			+'&username='+encodeURIComponent(document.getElementById('hidUsername').value)+'&SID='+encodeURIComponent(document.getElementById('hidSID').value)
	//		As a work around, do NOT allow access to the upload area if the user is not logged in.
	file.sendTo('code/_filedrop.php?rename='+sRename+'&overwrite='+iOverwrite+'&thumb='+sThumb+'&path='+encodeURIComponent(sPath)+'&ext='+encodeURIComponent(ext)+'&limit='+iSizeLimit);
}


function toggleHashes(sAction,This,Event) {
// shows a popup with the hashes of the file just uploaded
// sAction	the action to execute within the function: show, hide
// This		pass the calling object into this function as 'this'
// Event	pass the event into this function as 'event'
	var Elm = document.getElementById('spanHashes');

	if (sAction != 'show') {
		Elm.style.display = 'none';
	} else {
		if (! Event) {									// if the user wants a set-position showing, then...
			Elm.style.display = 'block';
			Elm.innerHTML = This.getAttribute('data-hashes');
		} else {									// otherwise, move the 'hash span' were the cursor goes
			Elm.style.top = Event.clientY + 'px';
			Elm.style.left = Event.clientX + 'px';
		}
	}
}


