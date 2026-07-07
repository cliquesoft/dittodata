// _location.js	Used to process all the Countries/States/Cities throughout
//		the website.
//
// Created	2013-01-04 by Dave Henderson (dhenderson@cliquesoft.org)
// Updated	2021-01-16 by Dave Henderson (dhenderson@cliquesoft.org)
//
// Unless a valid Cliquesoft Private License (CPLv1) has been purchased for
// this device, this software is licensed under the Cliquesoft Public
// License (CPLv2) as found on the Cliquesoft website at www.cliquesoft.org
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
// FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.




var reqLoc;

function loadCountries(strAction,strCountry,CallBack) {
// loads the states/regions for a selected country
// strAction 	this should most likely be 'req' unless you have a specific reason.
// strCountry	the name of the Country form object (combobox)
   switch(strAction) {
	case "req":
		ajax(reqLoc,4,'post',gbl_uriProject+"code/_locations.php",'load=countries','','',strCountry,'','',function(){loadCountries('succ',strCountry,CallBack);},function(){loadCountries('fail',strCountry,CallBack);},function(){loadCountries('busy',strCountry,CallBack);},function(){loadCountries('timeout',strCountry,CallBack);},function(){loadCountries('inactive',strCountry,CallBack);});
		break;
	case "busy":
		if (!confirm("There was already a request being processed.\nWould you like to retry?")) {return 0;}
		loadCountries('req',strCountry,CallBack);
		break;
	case "timeout":
		if (!confirm("The request timed out communicating with the\nserver. Would you like to retry?")) {return 0;}
		loadCountries('req',strCountry,CallBack);
		break;
	case "fail":
		break;
	case "succ":
		var Country = XML.getElementsByTagName("c");

		for (var I=0; I<Country.length; I++)
			{ Add2List(strCountry,Country[I].getAttribute('code'),Country[I].firstChild.data,0,0,0); }

		if (! CallBack) { return true; }			// if no callback was passed, no need to process anything else
		if (typeof CallBack === "function") { CallBack(); }	// otherwise we do, so...
		else { eval(CallBack); }
		break;
	case "inactive":
		// no reason to display anything because this section isn't applicable to this function
		break;
   }
}


function loadStates(strAction,strCountry,strState,strCity,intKeepFirst,CallBack) {
// loads the states/regions for a selected country
// strAction 	this should most likely be 'req' unless you have a specific reason.
// strCountry	the name of the Country form object (combobox)
// strState	same, but for the States
// strCity	same, but for the Cities
   if (typeof(strCity) === 'undefined') { strCity=''; }

   switch(strAction) {
	case "req":
		document.getElementById(strCountry).disabled=1;
		document.getElementById(strCountry).className += ' disabled';
		if (intKeepFirst)
			{ document.getElementById(strState).options.length=1; }	// resets the list to just "Please select"
		else
			{ document.getElementById(strState).options.length=0; }	// removes all prior values
		if (strCity != '') {						// if we have a city form object, then...
			document.getElementById(strCity).disabled=1;
			document.getElementById(strCity).className += ' disabled';
			if (intKeepFirst)
				{ document.getElementById(strCity).options.length=1; }
			else
				{ document.getElementById(strCity).options.length=0; }
		}

		ajax(reqLoc,4,'post',gbl_uriProject+"code/_locations.php",'country='+document.getElementById(strCountry).value,'','',strState,'','',function(){loadStates('succ',strCountry,strState,strCity,intKeepFirst,CallBack);},function(){loadStates('fail',strCountry,strState,strCity,intKeepFirst,CallBack);},function(){loadStates('busy',strCountry,strState,strCity,intKeepFirst,CallBack);},function(){loadStates('timeout',strCountry,strState,strCity,intKeepFirst,CallBack);},function(){loadStates('inactive',strCountry,strState,strCity,intKeepFirst,CallBack);});
		break;
	case "busy":
		if (!confirm("There was already a request being processed.\nWould you like to retry?")) {return 0;}
		loadStates('req',strCountry,strState,strCity,intKeepFirst,CallBack);
		break;
	case "timeout":
		if (!confirm("The request timed out communicating with the\nserver. Would you like to retry?")) {return 0;}
		loadStates('req',strCountry,strState,strCity,intKeepFirst,CallBack);
		break;
	case "fail":
		break;
	case "succ":
		var State = XML.getElementsByTagName("s");

		for (var I=0; I<State.length; I++)
			{ Add2List(strState,State[I].getAttribute('code'),State[I].firstChild.data,0,0,0); }

		if (CallBack) {						// if a callback was passed, then...	WARNING: we can't exit here or the code at the bottom will NOT run!!!
			if (typeof CallBack === "function") { CallBack(); }
			else { eval(CallBack); }
		}
		break;
	case "inactive":
		// no reason to display anything because this section isn't applicable to this function
		break;
   }
   if (strAction != 'req') {
	document.getElementById(strCountry).disabled=0;
	document.getElementById(strCountry).className = document.getElementById(strCountry).className.replace(/ disabled/g, "");
   }
}


function loadCities(strAction,strCountry,strState,strCity,intKeepFirst,CallBack) {
// loads the cities for a selected provinces/states.
// strAction 	this should most likely be 'req' unless you have a specific reason.
// strCountry	the name of the Country form object (combobox)
// strState	same, but for the States
// strCity	same, but for the Cities
// intKeepFirst	whether or not to keep the first line in the city element (e.g. if it is "Please select..." or something similar)
   switch(strAction) {
	case "req":
		document.getElementById(strState).disabled=1;
		document.getElementById(strState).className += ' disabled';
		if (intKeepFirst)
			{ document.getElementById(strCity).options.length=1; }		// resets the list to just "Please select"
		else
			{ document.getElementById(strCity).options.length=0; }		// removes all prior values

		ajax(reqLoc,4,'post',gbl_uriProject+"code/_locations.php",'country='+document.getElementById(strCountry).value+'&state='+document.getElementById(strState).value,'','',strCity,'','',function(){loadCities('succ',strCountry,strState,strCity,intKeepFirst,CallBack);},function(){loadCities('fail',strCountry,strState,strCity,intKeepFirst,CallBack);},function(){loadCities('busy',strCountry,strState,strCity,intKeepFirst,CallBack);},function(){loadCities('timeout',strCountry,strState,strCity,intKeepFirst,CallBack);},function(){loadCities('inactive',strCountry,strState,strCity,intKeepFirst,CallBack);});
		break;
	case "busy":
		if (!confirm("There was already a request being processed.\nWould you like to retry?")) {return 0;}
		loadCities('req',strCountry,strState,strCity,intKeepFirst,CallBack);
		break;
	case "timeout":
		if (!confirm("The request timed out communicating with the\nserver. Would you like to retry?")) {return 0;}
		loadCities('req',strCountry,strState,strCity,intKeepFirst,CallBack);
		break;
	case "fail":
		break;
	case "succ":
		var c = XML.getElementsByTagName("c");

		for (var i=0; i<c.length; i++)
			{ Add2List(strCity,c[i].firstChild.data,c[i].firstChild.data,0,0,0); }

		if (CallBack) {						// if a callback was passed, then...	WARNING: we can't exit here or the code at the bottom will NOT run!!!
			if (typeof CallBack === "function") { CallBack(); }
			else { eval(CallBack); }
		}
		break;
   }
   if (strAction != 'req') {
	document.getElementById(strState).disabled=0;
	document.getElementById(strState).className = document.getElementById(strState).className.replace(/ disabled/g, "");
   }
}

