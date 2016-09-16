// $Id: inc.js.general-functions.js 1027 2012-07-17 20:31:10Z conor $

/**
 * Outputs information passed to the display.
 *
 * @param       text      {string}   The information to be output to the browser.
 */
function d(text)
{
	document.writeln(text + "<br />");
}

/**
 * Adds a function or an instaniated object and a method to the list of functions/methods which should be run when
 * the page has been loaded.
 *
 * @author      Conor Kerr
 * @copyright   2008-2012 Ceon (http://ceon.net)
 * @param       func      {string}   The name of a function to run.
 * @param       obj       {object}   An object from which a method should be run.
 * @param       method    {string}   The name of the method within the specified object to run.
 * @returns     {none}
 */
function CeonAddLoadEvent(func, obj, method)
{
	var oldonload = window.onload;
	
	if (typeof window.onload != 'function') {
		if (func == '') {
			window.onload = function()
			{
				obj[method]();
			}
		} else {
			window.onload = func;
		}
	} else {
		window.onload = function()
		{
			if (oldonload) {
				oldonload();
			}
			if (func == '') {
				obj[method]();
			} else {
				func();
			}
		}
	}
}

/**
 * Adds a function or an instaniated object and a method to the list of functions/methods which should be run when
 * the page has been resized.
 *
 * @author      Conor Kerr
 * @copyright   2008-2012 Ceon (http://ceon.net)
 * @param       func      {string}   The name of a function to run.
 * @param       obj       {object}   An object from which a method should be run.
 * @param       method    {string}   The name of the method within the specified object to run.
 * @returns     {none}
 */
function CeonAddResizeEvent(func, obj, method)
{
	var oldonresize = window.onresize;
	
	if (typeof window.onresize != 'function') {
		if (func == '') {
			window.onresize = function()
			{
				obj[method]();
			}
		} else {
			window.onresize = func;
		}
	} else {
		window.onresize = function()
		{
			if (oldonresize) {
				oldonresize();
			}
			if (func == '') {
				obj[method]();
			} else {
				func();
			}
		}
	}
}