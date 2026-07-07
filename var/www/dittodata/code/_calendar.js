// _calendar.js	this file allows a calendar to be included in your project so
//		handling dates can be easier for the user.
//
// Created	unknown by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
// updated	2021/04/02 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
//
// Unless a valid Cliquesoft Private License (CPLv1) has been purchased for your
// device, this software is licensed under the Cliquesoft Public License (CPLv2)
// as found on the Cliquesoft website at www.cliquesoft.org.
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
// FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.




// editable global variables that can be changed for different languages
var aryMonths = new Array("January","February","March","April","May","June","July","August","September","October","November","December");
var aryDays = new Array("Sun","Mon","Tue","Wed","Thu","Fri","Sat");
var strCloseID = 'close.png';							// the name of the 'X' graphic to close the calendar

// non-editable global variables
var objCalTarget='';								// the id of the object to receive the selected date; blank value disables
var codeCallback='';								// any callback that should be executed upon clicking a date in the calendar - should be passed like "function(){...}"; blank value disables




function writeCalendar(boolHide,boolClose) {
// this function creates the html code for the calendar
// boolHide	if this is set to 1 then upon clicking a date, the calendar will 'hide'.
// boolClose	if this is set to 1 then the calendar can be closed without selecting a date.
	var now = new Date;
	var dow = now.getDay();
	var dd  = now.getDate();
	var mm  = now.getMonth();
	var yyyy = now.getFullYear();
	var cell = 0;

	var html = "<table class='tblCalendar'>";

	// draw the titlebar at the top of the calendar
	html += "<tr><td class='tdTitle' colspan='7'>";
	if (boolClose) { html += "<img src='home/"+gbl_nameUser+"/imgs/"+strCloseID+"' onClick=\"codeCallback=''; document.getElementById('divCalendar').style.display='none';\" />"; }
	html += "<select size='1' class='listbox' onChange=\"fillCalendar(this.parentNode.parentNode.parentNode);\">";
	for (var i=0; i<=11; i++) {						// cycle through the months and "select" the current month
		if (i==mm)
			{ html += "<option value='"+i+"' selected>"+(i+1)+' '+aryMonths[i]+"</option>"; }
		else
			{ html += "<option value='"+i+"'>"+(i+1)+' '+aryMonths[i]+"</option>"; }
	}
	html += "</select><span onClick=\"this.previousElementSibling.selectedIndex='"+mm+"'; this.nextElementSibling.value='"+yyyy+"'; fillCalendar(this.parentNode.parentNode.parentNode);\">Today</span><input type='textbox' value='"+yyyy+"' maxlength='4' class='textbox' onKeyUp=\"if(this.value.length==4){fillCalendar(this.parentNode.parentNode.parentNode);}\" /></td></tr>";

	// draw the Days of the Week header
	html += "<tr>";
	for (var i=0; i<=6; i++) { html += "<th>" + aryDays[i] + "</th>"; }
	html += "</tr>";

	// draw all the days within the calendar
	for (var i=0; i<=5; i++) {						// for each row of the calendar
		html += "<tr>";
		for (var j=0; j<=6; j++) {					// for each cell of the table...
			var CLASS='';						// (re)set the value each iteration
			if (j==0 || j==6) { CLASS='tdWeekend'; }

			html += "<td class='"+CLASS+"'><a href='#' onClick=\"selCalendarDay(this);";
			if (boolHide) { html += " codeCallback=''; document.getElementById('divCalendar').style.display='none';"; }
			html += "\">&nbsp;</a></td>";
			cell += 1;
		}
		html += "</tr>";
	}
	html += "</table>";
	document.write(html);
	fillCalendar('divCalendar');
}


function selCalendarDay(objCell) {
// this function performs several changes once a cell has been clicked which includes changing the background color, performing any callbacks, etc
// objCell	the cell of the calendar that was just clicked
	var year  = objCell.parentNode.parentNode.parentNode.getElementsByTagName('input')[0];
	var month = objCell.parentNode.parentNode.parentNode.getElementsByTagName('select')[0];
	var cells = objCell.parentNode.parentNode.parentNode.getElementsByTagName('a');		// store all the <a> elements within the calendar to cycle below

	for (var I=0; I<cells.length; I++)					// remove the 'selected' class from each cell in the table (so that the one cell that does have it will be affected)
		{ cells[I].className = cells[I].className.replace(/ aSel/g,'');; }
	objCell.className += ' aSel';						// set the cell that was just clicked the having the 'selected' class

	if (typeof codeCallback === "function") { codeCallback(); } else if (codeCallback != '') { eval(codeCallback); }	// if a callback was passed, then execute it!

	// set any passed object to the value of the date selected
	if (objCalTarget != '') { document.getElementById(objCalTarget).value = year.value + '-' + (parseInt(month.options[month.selectedIndex].value)+1) + '-' + objCell.innerHTML; }
}


function fillCalendar(Wrapper) {
// this function fills the calendar in with the appropriate date values
// Wrapper	the 'id' or the object itself that contains the calendar <table> from writeCalendar() above
	if (typeof Wrapper === "string") { var wrapper = document.getElementById(Wrapper); } else { var wrapper = Wrapper; }
	var now = new Date;

	var dow = now.getDay();
	var dd = now.getDate();
	var mm = now.getMonth();
	var yyyy = now.getFullYear();
	var temp;

	var prevMonth;
	var currYear = parseInt(wrapper.getElementsByTagName('input')[0].value);
	var currMonth = parseInt(wrapper.getElementsByTagName('select')[0].value);
	var mmyyyy = new Date();
	var arrN = new Array(41);

	if (currMonth != 0) { prevMonth = currMonth - 1; } else { prevMonth = 11; }
	mmyyyy.setFullYear(currYear);
	mmyyyy.setMonth(currMonth);
	mmyyyy.setDate(1);
	var day1 = mmyyyy.getDay();
	if (day1 == 0) { day1 = 7; }

	for (var i=0; i<day1; i++) { arrN[i] = maxDays((prevMonth),currYear) - day1 + i + 1; }	// sets the last days of the previous month
	temp = 1;
	for (var i=day1; i<=day1+maxDays(currMonth,currYear)-1; i++) {		// sets the days for the current month
		arrN[i] = temp;
		temp += 1;
	}
	temp = 1;
	for (var i=day1+maxDays(currMonth,currYear); i<=41; i++) {		// sets the days for the next month
		arrN[i] = temp;
		temp += 1;
	}
	var dCount = 0;

	var cells = wrapper.getElementsByTagName('a');				// store all the <a> elements within the calendar to cycle below
	for (var i=0; i<cells.length; i++) {
		if (((i<7) && (arrN[i]>20)) || ((i>27) && (arrN[i]<20))) {	// for all "non current month dates"
			cells[i].innerHTML = arrN[i];
			cells[i].className = 'aNC';
		} else {							// for all current month dates
			cells[i].innerHTML = arrN[i];
			if (dCount==0 || dCount==6) { cells[i].className = 'aWeekend'; } else { cells[i].className = 'aWeekday'; }
			if (arrN[i]==dd && mm==currMonth && yyyy==currYear)
				{ cells[i].className = 'aToday'; }
		}
		dCount += 1;
		if (dCount > 6) { dCount=0; }
	}
}


function maxDays(mm, yyyy) {
// this is a complementary function to fillCalendar()
	var mDay;
	if((mm == 3) || (mm == 5) || (mm == 8) || (mm == 10)){
		mDay = 30;
	} else {
		mDay = 31;
		if (mm == 1) { if (yyyy/4 - parseInt(yyyy/4) != 0) {mDay = 28;} else {mDay = 29;} }
	}
	return mDay;
}
