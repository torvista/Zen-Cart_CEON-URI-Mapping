// $Id: inc.js.crossfade.js 1027 2012-07-17 20:31:10Z conor $

/**
 * @fileoverview Contains crossfade functions, used to fade between a list of HTML elements which are statically
 *               (absolutely) positioned on a page within an identifiable container.
 *
 * @author      Conor Kerr <zen-cart@ceon.net>
 * @copyright   2010-2012 Ceon (http://ceon.net)
 */

var crossfade_id = 'crossfade'; // The ID of the crossfade list
var	crossfade_wrapper_element; // The object reference to the list
var crossfade_elements; // Array that will hold all child elements of the list
var current_element; // Keeps track of which image should currently be showing
var previous_element;
var pre_init_timer;
var first_time_pausing = true;


CeonCrossfadePreInit();


function CeonCrossfadePreInit()
{
	// An inspired kludge that - in most cases - manages to initially hide the element list before
	// even onload is triggered (at which point it's normally too late, and the whole list
	// already appeared to the user before being remolded)
	if ((document.getElementById) && (crossfade_wrapper_element =
			document.getElementById(crossfade_id))) {
		//crossfade_wrapper_element.style.visibility = 'hidden';
		
		if (typeof pre_init_timer != 'undefined') {
			clearTimeout(pre_init_timer); // thanks to Steve Clay http://mrclay.org/ for this small Opera fix
		}
	} else {
		pre_init_timer = setTimeout("CeonCrossfadePreInit()", 2);
	}
}

/**
 * Helper function to deal specifically with cross-browser differences in opacity handling
 */
function CeonSetOpacity(obj, opacity)
{
	if (obj.style) {
		if (obj.style.MozOpacity != null) {  
			// Mozilla's pre-CSS3 proprietary rule
			obj.style.MozOpacity = (opacity / 100) - .001;
		} else if (obj.style.opacity != null) {
			// CSS3 compatible
			obj.style.opacity = (opacity / 100) - .001;
		} else if (obj.style.filter != null) {
			// IE's proprietary filter
			obj.style.filter = "alpha(opacity=" + opacity + ")";
		}
	}
}

function CeonCrossfadeInit()
{
	if (document.getElementById) {
		// Shouldn't be necessary, but IE can sometimes get ahead of itself and trigger
		// CeonCrossfadeInit first
		CeonCrossfadePreInit(); 
		
		crossfade_elements = new Array;
		
		var node = crossfade_wrapper_element.firstChild;
		
		// Instead of using childNodes (which also gets empty nodes and messes up the script later)
		// we do it the old-fashioned way and loop through the first child and its siblings
		while (node) {
			if (node.nodeType == 1) {
				crossfade_elements.push(node);
			}
			node = node.nextSibling;
		}
		
		for (i = 0; i < crossfade_elements.length; i++) {
			// Loop through all these child nodes and set up their styles
			/*crossfade_elements[i].style.position = 'absolute';
			crossfade_elements[i].style.top = "16px";
			crossfade_elements[i].style.right = 0;*/
			crossfade_elements[i].style.zIndex = 0;
			
			// Set their opacity to transparent
			CeonSetOpacity(crossfade_elements[i], 0);
			
			crossfade_elements[i].style.visibility = 'visible';
		}
		
		// Make the list visible again
		//crossfade_wrapper_element.style.visibility = 'visible';
		
		// Initialise a few parameters to get the cycle going
		current_element = 0;
		previous_element = crossfade_elements.length - 1;
		
		opacity = 100;
		
		CeonSetOpacity(crossfade_elements[current_element], 100);
		
		// Start the whole crossfade process after a second's pause
		window.setTimeout("CeonCrossfade(100)", 1000);
	}
}

function CeonCrossfade(opacity)
{
	if (opacity < 100) {
		// Current element not faded up fully yet...so increase its opacity
		CeonSetOpacity(crossfade_elements[current_element], opacity);
		CeonSetOpacity(crossfade_elements[previous_element], 100 - opacity); 
		
		opacity += 2;
		
		window.setTimeout("CeonCrossfade(" + opacity + ")", 30);
		
	} else {
		// Make the previous element - which is now covered by the current one - fully transparent
		CeonSetOpacity(crossfade_elements[previous_element], 0);
		
		// Current element is now previous element, as we advance in the list of elements
		previous_element = current_element;
		current_element += 1;
		
		if (current_element >= crossfade_elements.length) {
			// Start over from first element if we cycled through all elements in the list
			current_element = 0;
		}
		
		// Make sure the current image is on top of the previous one
		//crossfade_elements[previous_element].style.zIndex = 0;
		//crossfade_elements[current_element].style.zIndex = 100;
		
		// Start the crossfade after a second's pause
		opacity = 0;
		
		if (first_time_pausing) {
			first_time_pausing = false;
			
			window.setTimeout("CeonCrossfade(" + opacity + ")", 1000);
		} else {
			window.setTimeout("CeonCrossfade(" + opacity + ")", 2300);
		}
	}
}