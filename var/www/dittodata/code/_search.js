// _search.js	Used to process Google-style search results
//
// Created	2019/07/18 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
// Updated	2021/02/12 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
//
// Unless a valid Cliquesoft Private License (CPLv1) has been purchased for your
// device, this software is licensed under the Cliquesoft Public License (CPLv2)
// as found on the Cliquesoft website at www.cliquesoft.org.
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
// FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.


var Search4Results;				// used by the ajax() call




function search4Results(sAction,sScript,sQuery,sTable,sColumn,Value,Results,sDisplay,bNew) {
// Submits a search request for matching records in a Google-style fashion.
// sAction	the string indicating which action to take: b(usy), f(ailure), i(nactive), r(equest), s(uccess), t(imeout)		< this is typically called with 'req' to begin the ajax request
// sScript	the string indicating the server-side script to call for processing							< this is typically the '_search.php' script, but can be your own custom script
// sQuery	the string defining any additional values to add into the query to pass to the server-side script
//		NOTES:
//			Do NOT include preceeding '&' character -AND- escape your values beforehand!!!
// sTable	the string defining the database table to parse for matching records
// sColumn	the string defining the database column to match its values with 
// Value	the objects' name (or object itself) containing the value to search for in the database					< this is typically the <textbox> that the user is typing in the value to match
// Results	the objects' name (or object itself) to store all the matching result values in						< this is typically the <select> that will show automatically with the results
// sDisplay	an optional pipe separated list of database values used to format the display of each item in the results list		< these are the actual database column names (e.g. value|type|address)
//		NOTES:
//			If this value is blank or 'value', just the 'sColumn' value will be added in the results listing.
//			The values can be encapsulated with '[]' characters for visual separation (e.g. value|[type]|address > Biz Name [Vendor] 555 South Street).
//			If one of the values is encrypted in the database, proceed it's name with the '!' character so it can be decrypted (e.g. value|[type]|!address)
// bNew		the boolean value indicating whether the 'Create New Record' value is included in the results list or not
// [sValue]	the originally submitted value for the 'Value' parameter to be compared against in subsequent calls
//
// Sample Return XML:
// <s>
//	<xml>
//		<result id='1234'>matched result 1</result>
//		<result id='2345'>matched result 2</result>
//		...
//	</xml>
// </s>
   	var ElmResults = (typeof Results === "object") ? Results : document.getElementById(Results);
   	var ElmValue = (typeof Value === "object") ? Value : document.getElementById(Value);

	switch(sAction) {
		case "r":
			if (ElmValue.value == '') { return true; }				// if the user was erasing their search term, then no need to query the server!

			var sSubmitted = ElmValue.value;					// we have to store the -current- value in a variable so it gets passed correctly back into this function with the callbacks

			if (sDisplay == '') { sDisplay = "value"; }				// set a default value if one is not provided
			if (sQuery != '') { sQuery = '&'+sQuery; }				// adds the necessary preceeding '&' character to the string
			ajax(Search4Results,4,'post',sScript,'action=search&table='+escape(sTable)+'&column='+escape(sColumn)+'&value='+escape(ElmValue.value)+'&display='+escape(sDisplay)+sQuery,'','','','','',function(){search4Results('s',sScript,sQuery,sTable,sColumn,Value,Results,sDisplay,bNew,sSubmitted);},function(){search4Results('f',sScript,sQuery,sTable,sColumn,Value,Results,sDisplay,bNew,sSubmitted);},function(){search4Results('b',sScript,sQuery,sTable,sColumn,Value,Results,sDisplay,bNew,sSubmitted);},function(){search4Results('t',sScript,sQuery,sTable,sColumn,Value,Results,sDisplay,bNew,sSubmitted);},function(){search4Results('i',sScript,sQuery,sTable,sColumn,Value,Results,sDisplay,bNew,sSubmitted);});

			if (ElmResults.style.display != 'inline-block') {			// if the results <select> object is not currently showing, then...
				ElmResults.style.display = 'inline-block';			//   show it so the user can pick an item from the list
				ElmResults.options.length = 0;					//   remove any existing options from the listing
				Add2List(ElmResults,'',"Searching database...",1,1,0);		//   add a default item so the user knows the search is being conducted...
			}
			break;
		case "s":
			if (ElmValue.value != arguments[9]) { return false; }			// if the user cleared the value from the textbox -OR- a new search has been made (ignoring any callbacks leading up to the last value entered (e.g. 'tes' vs 'test')), then exit now

			var r = XML.getElementsByTagName("result");
			var p = sDisplay.split("|");
			var P = new Array();
			var t = '';								// the text to display as a search result item
			var b = false;								// boolean value indicating the use of surrounding [] characters to one of the values

			ElmResults.options.length = 0;						// remove any existing options from the listing
			if (bNew) { Add2List(ElmResults,'',"-CREATE A NEW RECORD-",1,1,0); }	// add a default item so the user can create a new database record
			if (r.length == 0) { Add2List(ElmResults,'',"-No Matching Results Found-",1,1,0); return true; }	// if no matching results were found, then let the user know!

			for (var i=0; i<r.length; i++) {					// now add all the returned results
				P = p.slice(0);							//   this preserves the original value passed to the function (since we modify it below)
				t = '';								//   reset the variable after each line has been added to the results list
				for (var j=0; j<P.length; j++) {				//   construct the value to show for each iterated item in the results list
					if (/\[/.test(P[j])) {					//     if there needs to be a surrounding bracket around one of the values, then...
						b = true;					//       indicate that via this variable value
						P[j] = P[j].replace(/\[|\]/g,'');		//       remove those characters from the value
					}
					if (b) { t += '['; }
					if (P[j] == 'value') {					//     if we need to include the child data, then...
						t += r[i].firstChild.data;
					} else {						//     otherwise this is an attribute value to add, so...
						if (P[j].substring(0, 1) == '!') { P[j] = P[j].substring(1); }	// if the attribute was encrypted, remove the preceeding '!' character from the name

						t += r[i].getAttribute(P[j]);
					}
					if (b) { t += ']'; }
					t += ' ';

					b = false;						// reset the boolean value
				}

				Add2List(ElmResults,r[i].getAttribute('id'),t,1,1,0);
			}
			break;
		case "t":
			if (!confirm("The request timed out communicating with the\nserver. Would you like to retry?")) {return 0;}
			search4Results('r',sScript,sQuery,sTable,sColumn,Value,Results,sDisplay,bNew,arguments[9]);
			break;
		case "b":
			if (!confirm("There was already a request being processed.\nWould you like to retry?")) {return 0;}
			search4Results('r',sScript,sQuery,sTable,sColumn,Value,Results,sDisplay,bNew,arguments[9]);
			break;
		case "f":
			break;
		case "i":
			break;
	}
}


function selectMatch(Index,Text,Results,Callback) {
// Makes form adjustments when one of the options in the Google-style results listbox is clicked.
// Index	the objects' name (or object itself) to store the index value of the selected item within the results			< this is typically the hidden form object
// Text		the objects' name (or object itself) to store the text value of the selected item within the results			< this is typically the <textbox> that was being typed into
// Results	the objects' name (or object itself) to store all the matching result values in						< this is typically the <select> that will show automatically with the results
// Callback	the functions' name (or anonymous function) passed to call once the user has selected a result from the list		< this typically will fill in a form or value with the selection from the results
   	var ElmIndex = (typeof Index === "object") ? Index : document.getElementById(Index);
   	var ElmText = (typeof Text === "object") ? Text : document.getElementById(Text);
   	var ElmResults = (typeof Results === "object") ? Results : document.getElementById(Results);

	if (ElmResults.value == '') {				// if the user selected "-CREATE A NEW RECORD-", then make the matching <select> disappear
		ElmResults.style.display='none';
		return true;
	}

	ElmIndex.value = ElmResults.options[ElmResults.selectedIndex].value;
	ElmText.value = ElmResults.options[ElmResults.selectedIndex].text;
	ElmResults.options.length = 0;				// blank out all the options to not pollute future matches
	ElmResults.style.display = 'none';			// hide the matches listbox now that a selection has been made

	if (typeof Callback === "function") { Callback(); } else if (Callback != '') { eval(Callback); }
}

