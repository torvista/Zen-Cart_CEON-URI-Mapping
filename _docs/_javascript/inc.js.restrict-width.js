// $Id: inc.js.restrict-width.js 1027 2012-07-17 20:31:10Z conor $

/**
 * @fileoverview Contains CeonRestrictWidth class, which can be used to restrict the width of a HTML page to a
 *               minimum and maximum width. In-between these limits the page can expand/contract as necessary to
 *               use as much of the browser window as allowed.
 */

/**
 * Outputs a div which is prevented from rendering beyond a maximum and below a minimum width. If the browser's
 * window width is larger than the maximum width specified, the div is centered within the window. Intended to be
 * used to wrap an entire page's contents so its width can be controlled.
 *
 * @class       CeonRestrictWidth
 * @author      Conor Kerr
 * @copyright   2008-2012 Ceon (http://ceon.net)
 * @version     2.0.1
 * @param       min_width_val   {integer}   The minimum width the div can be (in pixels).
 * @param       max_width_val   {integer}   The maximum width the div can be (in pixels).
 */
function CeonRestrictWidth(min_width_val, max_width_val)
{
	var in_use = false;
	
	var prev_width = 0;
	var width = 0;
	var min_width = min_width_val;
	var max_width = max_width_val;
	
	var restrict_width_css_rule_obj = null;
	
	// Only use restrictWidth if screen is wide enough, saves CPU time for user
	if (screen.width > max_width) {
		in_use = true;
		
		_getCurrentWidth();
		
		selector = '#restrict-width';
		
		// Try to identify and use an existing rule if possible, rather than creating a new one
		restrict_width_css_rule_obj = CeonGetCSSStyleRule(selector, 'screen');
		
		if (!restrict_width_css_rule_obj) {
			// Rule must be added as could not identify one (either because none exists or because of a browser
			// security restriction, e.g. chrome with the file:// protocol)
			
			// Assume there is at least one stylesheet
			if (document.styleSheets[0].insertRule) {
				// Moz/others
				if (document.styleSheets[0].cssRules != undefined) {
					var last_rule = document.styleSheets[0].cssRules.length;
					
					try {
						var inserted_at = document.styleSheets[0].insertRule(
							'@media screen { ' + selector + '{ position:absolute; width:' + _determineHSize() +
							'; left:' + _determineHPos() + '; }' + ' }',
							last_rule);
						
						restrict_width_css_rule_obj = document.styleSheets[0].cssRules[inserted_at].cssRules[0];
						
					} catch (e) {
						// Safari can't handle media tag, must add new stylesheet for media
						CeonAddStyleSheet('screen');
						
						var last_style_sheet = document.styleSheets.length - 1;
						
						document.styleSheets[last_style_sheet].insertRule(
							selector + ' { position:absolute; width:' + _determineHSize() + ';' +
							' left:' + _determineHPos() + '; }',
							0); 
						
						restrict_width_css_rule_obj = document.styleSheets[last_style_sheet].rules[0];
					}
				} else {
					CeonAddStyleSheet('screen');
					
					var last_style_sheet = document.styleSheets.length - 1;
					
					document.styleSheets[last_style_sheet].insertRule(
						selector + ' { position:absolute; width:' + _determineHSize() + ';' +
						' left:' + _determineHPos() + '; }',
						0); 
					
					restrict_width_css_rule_obj = document.styleSheets[last_style_sheet].rules[0];
				}
			} else if (document.styleSheets[0].addRule) {
				// IE doesn't support addition of media rules, add new stylesheet for media
				CeonAddStyleSheet('screen');
				
				var last_style_sheet = document.styleSheets.length - 1;
				
				document.styleSheets[last_style_sheet].addRule(selector,
					'position:absolute; width:' + _determineHSize() + '; left:' + _determineHPos() + ';',
					0);
				
				restrict_width_css_rule_obj = document.styleSheets[last_style_sheet].rules[0];
			}
		} else {
			restrict_width_css_rule_obj.style.position = 'absolute';
		}
		
		document.write('<div id="restrict-width">');
		
		CeonAddLoadEvent('', this, 'restrictWidth');
		CeonAddResizeEvent('', this, 'restrictWidth');
	}
	
	/**
	 * Public method which outputs resizeable div end tag if necessary.
	 *
	 * @member  CeonRestrictWidth
	 * @returns {boolean}   Returns true to prevent JS errors in Safari.
	 */
	this.outputClosingTag = function()
	{
		if (in_use) {
			document.write("</div>");
		}
		return true;
	}
	
	/**
	 * Updates the CSSStyle for the resizeable div with the width it should be set to and the left position it
	 * should be placed at.
	 *
	 * @member  CeonRestrictWidth
	 */
	this.restrictWidth = function()
	{
		_getCurrentWidth();
		
		if (width != prev_width) {
			if (width <= min_width) {
				restrict_width_css_rule_obj.style.width = min_width + "px";
				restrict_width_css_rule_obj.style.left = 0;
			} else {
				// Determine width
				restrict_width_css_rule_obj.style.width = _determineHSize();
				restrict_width_css_rule_obj.style.left = _determineHPos();
			}
			prev_width = width;
		}
	}
	
	/**
	 * Private method which records the current width of the browser window.
	 *
	 * @member  CeonRestrictWidth
	 * @private
	 */
	function _getCurrentWidth()
	{
		if (window.innerWidth) {
			width = window.innerWidth;
		} else if (document.documentElement.clientWidth) {
			width = document.documentElement.clientWidth;
		} else if (document.body.clientWidth) {
			width = document.body.clientWidth;
		}
	}
	
	/**
	 * Private method which works out the string to be used to set the resizeable div to the maximum size allowed.
	 *
	 * @member  CeonRestrictWidth
	 * @private
	 * @returns {string}   The string to which the width of the resizeable div should be set.
	 */
	function _determineHSize()
	{
		if (width > max_width) {
			// Client window is greater than the max width of the page
			return max_width + "px";
		} else {
			// Client window is smaller than max width of page so use full width
			return "100%";
		}
	}
	
	/**
	 * Private method which works out the string to be used to centre the resizeable div (by adjusting the value
	 * for its left position).
	 *
	 * @member  CeonRestrictWidth
	 * @private
	 * @returns {string}   The string to which the left position of the resizeable div should be set.
	 */
	function _determineHPos()
	{
		if (width > max_width) {
			return parseInt((width - max_width) / 2) + "px";
		} else {
			return "0";
		}
	}
}