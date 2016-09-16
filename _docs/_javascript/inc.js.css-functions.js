// $Id: inc.js.css-functions.js 1027 2012-07-17 20:31:10Z conor $

/**
 * Looks for the very last occurrence (and therefore most significant) of a specified CSS style in the current
 * document. Checks every stylesheet, including imported stylesheets. An optional media type can be specified to
 * limit the search to that media type only.
 *
 * @author      Conor Kerr
 * @copyright   2008-2012 Ceon (http://ceon.net)
 * @param       selector  {string}   The selector for the style.
 * @param       media     {string}   The optional media type for the style.
 * @returns     {CSSStyle,boolean}   The CSSStyle object matching or false if not found.
 */
function CeonGetCSSStyleRule(selector, media)
{
	var style_rule = false;
	
	for (i = 0; i < document.styleSheets.length; i++) {
		var current_style_rule =
			CeonSearchStyleSheetForCSSStyleRule(document.styleSheets[i], selector, media);
		
		if (current_style_rule != false) {
			style_rule = current_style_rule;
		}
	}
	
	return style_rule;
}

function CeonSearchStyleSheetForCSSStyleRule(ss, selector, media)
{
	var style_rule = false;
	
	if (media == undefined || media == null || media.length == 0) {
		media = null;
	}
	
	if (ss.imports) {
		// IE
		// IE doesn't support specifying media after import statement and lumps all CSS Rules (inc Media Rules)
		// into same media as main stylesheet
		// Only attempt to match any CSS Rules if the stylesheet if of the selected media type
		var ss_media = ss.media;
		
		if (ss_media == '') {
			ss_media = 'screen';
		} else if (typeof ss_media != 'string') {
			ss_media = String(ss_media);
		}
		
		if (media != null && ss_media.indexOf(media) == -1 && ss_media.indexOf('all') == -1) {
			return false;
		}
		
		var num_ss_import_rules = ss.imports.length;
		
		for (var i2 = 0; i2 < num_ss_import_rules; i2++) {
			var current_style_rule =
				CeonSearchStyleSheetForCSSStyleRule(ss.imports[i2], selector, media);
			
			if (current_style_rule != false) {
				style_rule = current_style_rule;
			}
		}
	} else if (ss.cssRules != undefined){
		// Other browsers
		var num_potential_ss_import_rules = ss.cssRules.length;
		
		for (var i2 = 0; i2 < num_potential_ss_import_rules; i2++) {
			if (ss.cssRules[i2].type == 3 && ss.cssRules[i2].styleSheet != undefined) {
				var embedded_stylesheet = ss.cssRules[i2].styleSheet;
				
				var current_style_rule =
					CeonSearchStyleSheetForCSSStyleRule(embedded_stylesheet, selector, media);
				
				if (current_style_rule != false) {
					style_rule = current_style_rule;
				}
			}
		}
	}
	
	if (ss.rules != undefined && ss.cssRules == undefined) {
		// IE
		for (var j = 0; ss.rules.length > j; j++) {
			rule = ss.rules[j];
			
			try {
				var current_selector = rule.selectorText
				
				if (current_selector == selector) {
					style_rule = rule;
				}
			} catch (e) { d(e) }
		}
	} else if (ss.cssRules != undefined) {
		// Other browsers
		var ss_media = (ss.media != null ? ss.media.mediaText : '');
		
		if (ss_media == '') {
			ss_media = 'screen';
		}
		
		var ss_media_matches = false;
		
		if (ss_media.indexOf(media) != -1 || ss_media.indexOf('all') != -1) {
			ss_media_matches = true;
		}
		
		for (var j = 0; j < ss.cssRules.length; j++) {
			var rule = ss.cssRules[j];
			
			if (rule.type == CSSRule.MEDIA_RULE) {
				if (media == null || rule.media.mediaText.indexOf(media) != -1) {
					// Examine any rules embedded within this media rule
					var num_media_rules = rule.cssRules.length;
					
					for (var m_i = 0; m_i < num_media_rules; m_i++) {
						var m_rule = rule.cssRules[m_i];
						
						if (m_rule.type == CSSRule.STYLE_RULE) {
							try {
								var current_selector = m_rule.selectorText
								
								if (current_selector == selector) {
									style_rule = m_rule;
								}
							} catch (e) { d(e) }
						}
					}
				}
			} else if (ss_media_matches && rule.type == CSSRule.STYLE_RULE) {
				try {
					var current_selector = rule.selectorText
					
					if (current_selector == selector) {
						style_rule = rule;
					}
				} catch (e) { d(e) }
			}
		}
	}
	
	return style_rule;
}


/**
 * Creates a new stylesheet of the specified media type and adds it to the current document.
 *
 * @author      Conor Kerr
 * @copyright   2008-2012 Ceon (http://ceon.net)
 * @param       media     {string}   The optional media type to assign to the newly created stylesheet.
 */
function CeonAddStyleSheet(media)
{
	if (media == undefined) {
		media = 'all';
	}
	
	var style_node = document.createElement('style');
	style_node.setAttribute('type', 'text/css');
	style_node.setAttribute('media', media);
	document.getElementsByTagName("head")[0].appendChild(style_node);
}