// Created	unknown by Ronnie T. Moore
// Updated	2020-06-29 by Dave Henderson (dhenderson@cliquesoft.org)


// Create and/or set the default values of used variables for this file
var expDays = 30;
var exp = new Date();
exp.setTime(exp.getTime() + (expDays*24*60*60*1000));




function setCookie(strName, strValue) {
// Adds a cookie to the browser via javascript.
// strName:	the name of the cookie to create
// strValue:	the value you want to assign to the cookie
// object list:	additional parameters include (optional, but in this order): expiration date, path (directory name), domain name, access, secure (ssl xfer only)
	var argv = setCookie.arguments;
	var argc = setCookie.arguments.length;
	var date = new Date('January 1, 2025 00:01:00');

	var expires = (argc > 2) ? argv[2] : null;
	var path = (argc > 3) ? argv[3] : null;
	var domain = (argc > 4) ? argv[4] : null;
	var access = (argc > 5) ? argv[5] : null;
	var secure = (argc > 6) ? argv[6] : false;
	document.cookie = strName + "=" + escape(strValue) +
		((expires == null) ? "; expires="+date : ("; expires=" + expires.toGMTString())) +
		((path == null) ? "; path='/'" : ("; path=" + path)) +
		((domain == null) ? "" : ("; domain=" + domain)) +
		((access == null) ? "; SameSite=Strict" : ("; SameSite=" + access)) +
		((secure == true) ? "; secure" : "");
}


function delCookie(strName) {
// Deletes a cookie from the browser via javascript.
// strName:	the name of the cookie to delete
// object list:	additional parameters include (optional, but in this order): path (directory name), domain name, secure (ssl xfer only)
	var exp = new Date();
	    exp.setTime (exp.getTime() - 1);
	var argv = delCookie.arguments;
	var argc = delCookie.arguments.length;

	var cval = getCookie(strName);
	var path = (argc > 1) ? argv[1] : null;
	var domain = (argc > 2) ? argv[2] : null;
	var secure = (argc > 3) ? argv[3] : false;

	document.cookie = strName + "=" + cval + "; expires=Thu, 01 Jan 1970 00:00:01 GMT" +
		((path == null) ? "; path='/'" : ("; path=" + path)) +
		((domain == null) ? "" : ("; domain=" + domain)) +
		((secure == true) ? "; secure" : "");
}


function getCookie(strName) {
// Obtains an existing cookie value stored in the browser via javascript.
	var arg = strName + "=";
	var alen = arg.length;
	var clen = document.cookie.length;
	var i = 0;

	while (i < clen) {
		var j = i + alen;
		if (document.cookie.substring(i, j) == arg) { return getCookieVal(j); }
		i = document.cookie.indexOf(" ", i) + 1;
		if (i == 0) break;
	}
	return null;
}


function getCookieVal(offset) {
// A dependency function for the 'getCookie' function.
	var endstr = document.cookie.indexOf (";", offset);

	if (endstr == -1) { endstr = document.cookie.length; }
	return unescape(document.cookie.substring(offset, endstr));
}

