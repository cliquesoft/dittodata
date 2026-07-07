function addChange(value2Adjust,intDecimals) {
// This function makes the change portion of a money textbox correct (performing rounding as well).
// Simply call this routine using the following syntax:
// addChange(123.45678,2);		This will automatically convert the value in the textbox.
   var i,parts,xVal=1;

   if (intDecimals < 0) { alert("The addChange function can only accept decimal places as 0 or a positive integer"); return 0; }
   for (i=0; i<intDecimals; i++) { xVal *= 10; }				// gets the correct number to multiple/divide by

   value2Adjust = Math.round(value2Adjust * xVal) / xVal;			// performs the rounding
   value2Adjust = String(value2Adjust);						// convert the passed number into a string (if it isn't already one)

   parts = value2Adjust.split(".");
   if (parts.length > 1) {
	for (i=intDecimals; i>parts[1].length; i--) { value2Adjust += '0'; }	// add any trailing 0's
   } else {
	if (intDecimals != 0) { value2Adjust += '.'; }				// add the decimal point
	for (i=0; i<intDecimals; i++) { value2Adjust += '0'; }
   }
   return value2Adjust;								// NOTE: we can NOT parseFloat() here since it will remove trailing zero's
}


function roundFloat(num, precision) {
// Since javascript's number handling is terrible, here is a solution
// WARNING: this does NOT retain trailing zero's!!! Use this when working with math, not displaying output (see addChange() above)
// https://stackoverflow.com/questions/10015027/javascript-tofixed-not-rounding
    return parseFloat((+(Math.round(+(num + 'e' + precision)) + 'e' + -precision)).toFixed(precision));
}
