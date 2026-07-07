// _tabstrip.js	Used to modify the "tabs" of a form to make one selected
//		while unselecting all the others.
//
// Created	2004/07/10 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
// Updated	2020/12/25 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
//
// Unless a valid Cliquesoft Private License (CPLv1) has been purchased for your
// device, this software is licensed under the Cliquesoft Public License (CPLv2)
// as found on the Cliquesoft website at www.cliquesoft.org.
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
// FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.
//
// RESOURCES:
// http://stackoverflow.com/questions/2793688/how-do-i-put-unordered-list-items-into-an-array




function adjTabs(strUL,strLIMatch,strLIClass,Selected,Callback) {
// Sets the tab (<li>) that needs to represent which is selected out of the tab list - INDEPENDENT of numeric indices!
// NOTE:
//	- this works with nested <ul>'s too!
//	- any <li> containing a "rel='...'" attribute with the name of a corresponding <div> ID, its 'display' value will be toggled depending on the selected state of the tab
// strUL	the <ul> ID containing the tabs (<li>)
// strLIMatch	the <li> css class name that matches actual tabs (e.g. liTab) - useful if some of the <li>'s are used for navigation which would be skipped by not giving them this class name
// strLIClass	the <li> css class name to apply to the selected tab
// Selected	the <li> that was just clicked; this value can typically be passed as 'this'; can pass as string or object
// Callback	code that should execute processing this function; can pass as string or function
   	var objSel = (typeof Selected === "object") ? Selected : document.getElementById(Selected);
	if (! objSel) { return true; }								// if the tab (<li>) no longer exists, then exit this function

	var LIs = document.getElementById(strUL).getElementsByTagName('li');			// stores all the <li> nodes in a variable
	var REM = new RegExp(strLIMatch, 'g');							// allows a variable to be used in the below .match() call with additional parameters ('g')
	var REP = new RegExp(strLIClass, 'g');							// allows a variable to be used in the below .replace() call with additional parameters ('g')

	for (var i=0; i<LIs.length; i++) {							// cycle EACH <li> - everyone of them!
		if (LIs[i].className.match(REM)) {						// filter only the relevant <li> based on the passed matching class name
			LIs[i].className = LIs[i].className.replace(REP, "");			// strip the "selected class" from each iterated tab
			if (LIs[i].getAttribute('rel') && document.getElementById(LIs[i].getAttribute('rel'))) { document.getElementById(LIs[i].getAttribute('rel')).style.display='none'; }	// hide each associated <div> if each iterated <li>'s "rel='...'" value
		}
	}

	objSel.className += ' ' + strLIClass;							// add the selected class name to the tab (<li>)
	if (objSel.getAttribute('rel') && document.getElementById(objSel.getAttribute('rel'))) { document.getElementById(objSel.getAttribute('rel')).style.display='block'; }			// show the associated <div> if the <li> has a "rel='...'" value

	if (typeof Callback === "function") { Callback(); }
	else if (Callback != '') { eval(Callback); }						// execute the callback if it was passed
}


function adjTabs2(strLIClass,strLIPrefix,strScrPrefix,intIndex,Callback) {
// Sets the tab (<li>) that needs to represent which is selected out of the tab list - DEPENDENT of numeric indices!
// NOTE:
//	- this works with nested <ul>'s too!
//	- there can NOT be any non-consecutive index values; e.g. we can't have 'liTab0', 'liTab1', 'liTab3' - there has to be a 'liTab2' also!
// strLIClass	the <li> css class name to apply to the selected tab
// strLIPrefix	the naming convention prefix of the tab (<li>) IDs, for example, id="liTab0" would mean strLIPrefix="liTab".
// strScrPrefix	the optional naming convention prefix of the corresponding tabs' (<li>) screen (usually a div)
// intIndex	the index of the tab (and corresponding screen) clicked; given the above example, this value would be '0'.
// Callback	code that should execute processing this function
	var i=0;
	var regEx = new RegExp(strLIClass, 'g');						// allows a variable to be used in the below .replace() call

	while (document.getElementById(strLIPrefix + i)) {					// while we have a valid tab (the first non-existing occurance completes this 'while' loop), then...
		if (document.getElementById(strScrPrefix + i)) { document.getElementById(strScrPrefix + i).style.display = 'none'; }		// hide each associated tab screen
		document.getElementById(strLIPrefix + i).className = document.getElementById(strLIPrefix + i).className.replace(regEx, "");	// strip the "selected class" from each iterated tab
		i++;
	}

	// now make the changes to reflect the newly selected tab
	if (document.getElementById(strScrPrefix + intIndex)) { document.getElementById(strScrPrefix + intIndex).style.display = 'block'; }
	document.getElementById(strLIPrefix + intIndex).className += ' ' + strLIClass;

	if (typeof Callback === "function") { Callback(); }
	else if (Callback != '') { eval(Callback); }						// execute the callback if it was passed
}


function clearTabs(strUL,strLIMatch,strLIClass,Callback) {
// this is a function to clear selected tabs from clusters, if all the tabs are not in one <ul> - INDEPENDENT of numeric indices!
// NOTE:
//	- this works with nested <ul>'s too!
//	- any <li> containing a "rel='...'" attribute with the name of a corresponding <div> ID, its 'display' value will be toggled depending on the selected state of the tab
// strUL	the <ul> ID containing the tabs (<li>)
// strLIMatch	the <li> css class name that matches actual tabs (e.g. liTab) - useful if some of the <li>'s are used for navigation which would be skipped by not giving them this class name
// strLIClass	the <li> css class of the selection to remove
	var LIs = document.getElementById(strUL).getElementsByTagName('li');			// stores all the <li> nodes in a variable
	var REM = new RegExp(strLIMatch, 'g');							// allows a variable to be used in the below .match() call with additional parameters ('g')
	var REP = new RegExp(strLIClass, 'g');							// allows a variable to be used in the below .replace() call with additional parameters ('g')

	for (var i=0; i<LIs.length; i++) {							// cycle EACH <li> - everyone of them!
		if (LIs[i].className.match(REM)) {						// filter only the relevant <li> based on the passed matching class name
			LIs[i].className = LIs[i].className.replace(REP, "");			// strip the "selected class" from each iterated tab
			if (LIs[i].getAttribute('rel') && document.getElementById(LIs[i].getAttribute('rel'))) { document.getElementById(LIs[i].getAttribute('rel')).style.display='none'; }	// hide each associated <div> if each iterated <li>'s "rel='...'" value
		}
	}

	if (typeof Callback === "function") { Callback(); }
	else if (Callback != '') { eval(Callback); }						// execute the callback if it was passed
}
