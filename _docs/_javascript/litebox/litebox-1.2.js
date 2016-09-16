/**
 * @fileoverview Contains "lightbox" image displayer popup class and functions, used to display images in a window
 *               with a semi-transparent overlay placed over the current browser viewport. Supports browsing
 *               through a list of linked images!
 *               
 * @author      Conor Kerr <zen-cart@ceon.net>
 * @author      Based on Litebox 1.0 by detrate and gannon - http://doknowevil.net/litebox
 * @author      Source edited from Lightbox v2.02 by Lokesh Dhakar - http://www.huddletogether.com
 * @author      Scott Upton - http://uptonic.com
 * @author      Peter-Paul Koch - http://quirksmode.org
 * @author      Thomas Fuchs - http://mir.aculo.us
 * @license     Creative Commons Attribution 2.5 - http://creativecommons.org/licenses/by/2.5/
 * @version     $Id: litebox-1.2.js 1027 2012-07-17 20:31:10Z conor $
 */

/**
 * Configuration
 */
var litebox_images_prefix = (document.location.href.match(/sections/) ? '../_' : '_');
var fileLoadingImage = litebox_images_prefix + 'images/litebox/ceon-star-loading.gif';
var fileBottomNavCloseImage = litebox_images_prefix + 'images/litebox/closelabel.gif';
var resizeSpeed = 6; // Controls the speed of the image resizing (1=slowest and 10=fastest)
var borderSize = 8; // If you adjust the padding in the CSS, you will need to update this variable


/**
 * Global Variables
 */
var imageArray = new Array;
var activeImage;

if (resizeSpeed > 10) { resizeSpeed = 10; }
if (resizeSpeed < 1) { resizeSpeed = 1; }
resizeDuration = (11 - resizeSpeed) * 100;


/**
 * Additional methods for Element added by SU, Couloir
 * - further additions by Lokesh Dhakar (huddletogether.com)
 */
Object.extend(Element, {
	hide: function()
	{
		for (var i = 0; i < arguments.length; i++) {
			var element = $(arguments[i]);
			element.style.display = 'none';
		}
	},
	show: function()
	{
		for (var i = 0; i < arguments.length; i++) {
			var element = $(arguments[i]);
			element.style.display = '';
		}
	},
	getWidth: function(element)
	{
		element = $(element);
		return element.offsetWidth; 
	},
	setWidth: function(element,w)
	{
		element = $(element);
		element.style.width = w +"px";
	},
	getHeight: function(element)
	{
		element = $(element);
		return element.offsetHeight;
	},
	setHeight: function(element,h)
	{
		element = $(element);
		element.style.height = h +"px";
	},
	setTop: function(element,t)
	{
		element = $(element);
		element.style.top = t +"px";
	},
	setSrc: function(element,src)
	{
		element = $(element);
		element.src = src; 
	},
	setInnerHTML: function(element,content)
	{
		element = $(element);
		element.innerHTML = content;
	}
});


/**
 * Extending built-in Array object
 */
Array.prototype.removeDuplicates = function ()
{
	for(i = 1; i < this.length; i++){
		if(this[i][0] == this[i-1][0]){
			this.splice(i,1);
		}
	}
}

Array.prototype.empty = function ()
{
	for(i = 0; i <= this.length; i++){
		this.shift();
	}
}


/**
 * Structuring of code inspired by Scott Upton (http://www.uptonic.com/)
 */
var Lightbox = Class.create();

Lightbox.prototype =
{
	// initialize()
	// Constructor runs on completion of the DOM loading. Loops through anchor tags looking for 'lightbox'
	// references and applies onclick events to appropriate links. The 2nd section of the function inserts html at
	// the bottom of the page which is used to display the shadow overlay and the image container.
	//
	initialize: function()
	{
		if (!document.getElementsByTagName) { return; }
		
		var anchors = document.getElementsByTagName('a');
		
		// loop through all anchor tags
		for (var i=0; i<anchors.length; i++){
			var anchor = anchors[i];
			
			var relAttribute = String(anchor.getAttribute('rel'));
			
			// use the string.match() method to catch 'lightbox' references in the rel attribute
			if (anchor.getAttribute('href') && (relAttribute.toLowerCase().match('lightbox'))) {
				anchor.onclick = function ()
				{
					myLightbox.start(this);
					return false;
				}
			}
		}
		
		var objBody = document.getElementsByTagName("body").item(0);
		
		var objOverlay = document.createElement("div");
		objOverlay.setAttribute('id','overlay');
		objOverlay.onclick = function() { myLightbox.end(); return false; }
		objBody.appendChild(objOverlay);
		
		var objLightbox = document.createElement("div");
		objLightbox.setAttribute('id','lightbox');
		objLightbox.style.display = 'none';
		objLightbox.onclick = function() { myLightbox.end(); return false; }
		objBody.appendChild(objLightbox);
		
		var objOuterImageContainer = document.createElement("div");
		objOuterImageContainer.setAttribute('id','outerImageContainer');
		objOuterImageContainer.onclick = function(e) { /* Don't pass the onclick event on to the lightbox div as it would close the image! */ e = e||event; e.stopPropagation? e.stopPropagation() : e.cancelBubble = true; }
		objLightbox.appendChild(objOuterImageContainer);
		
		var objImageContainer = document.createElement("div");
		objImageContainer.setAttribute('id','imageContainer');
		objOuterImageContainer.appendChild(objImageContainer);
		
		var objLightboxImage = document.createElement("img");
		objLightboxImage.setAttribute('id','lightboxImage');
		objImageContainer.appendChild(objLightboxImage);
		
		var objHoverNav = document.createElement("div");
		objHoverNav.setAttribute('id','hoverNav');
		objImageContainer.appendChild(objHoverNav);
		
		var objPrevLink = document.createElement("a");
		objPrevLink.setAttribute('id','prevLink');
		objPrevLink.setAttribute('href','#');
		objHoverNav.appendChild(objPrevLink);
		
		var objNextLink = document.createElement("a");
		objNextLink.setAttribute('id','nextLink');
		objNextLink.setAttribute('href','#');
		objHoverNav.appendChild(objNextLink);
		
		
		var objLoading = document.createElement("div");
		objLoading.setAttribute('id','loading');
		objImageContainer.appendChild(objLoading);
		
		var objLoadingLink = document.createElement("a");
		objLoadingLink.setAttribute('id','loadingLink');
		objLoadingLink.setAttribute('href','#');
		objLoadingLink.onclick = function() { myLightbox.end(); return false; }
		objLoading.appendChild(objLoadingLink);
		
		var objLoadingImage = document.createElement("img");
		objLoadingImage.setAttribute('src', fileLoadingImage);
		objLoadingLink.appendChild(objLoadingImage);
		
		var objImageDataContainer = document.createElement("div");
		objImageDataContainer.setAttribute('id','imageDataContainer');
		objImageDataContainer.className = 'clearfix';
		objLightbox.appendChild(objImageDataContainer);
		
		var objImageData = document.createElement("div");
		objImageData.setAttribute('id','imageData');
		objImageDataContainer.appendChild(objImageData);
		
		var objImageDetails = document.createElement("div");
		objImageDetails.setAttribute('id','imageDetails');
		objImageData.appendChild(objImageDetails);
		
		var objCaption = document.createElement("span");
		objCaption.setAttribute('id','caption');
		objImageDetails.appendChild(objCaption);
		
		var objNumberDisplay = document.createElement("span");
		objNumberDisplay.setAttribute('id','numberDisplay');
		objImageDetails.appendChild(objNumberDisplay);
		
		var objBottomNav = document.createElement("div");
		objBottomNav.setAttribute('id','bottomNav');
		objImageData.appendChild(objBottomNav);
		
		var objBottomNavCloseLink = document.createElement("a");
		objBottomNavCloseLink.setAttribute('id','bottomNavClose');
		objBottomNavCloseLink.setAttribute('href','#');
		objBottomNavCloseLink.onclick = function() { myLightbox.end(); return false; }
		objBottomNav.appendChild(objBottomNavCloseLink);
		
		var objBottomNavCloseImage = document.createElement("img");
		objBottomNavCloseImage.setAttribute('src', fileBottomNavCloseImage);
		objBottomNavCloseLink.appendChild(objBottomNavCloseImage);
		
		overlayEffect = new fx.Opacity(objOverlay, { duration: 300 });	
		overlayEffect.hide();
		
		imageEffect = new fx.Opacity(objLightboxImage, { duration: 350, onComplete: function()
				{
					imageDetailsEffect.custom(0,1);
				}
			});
		
		imageEffect.hide();
		
		imageDetailsEffect = new fx.Opacity('imageDataContainer', { duration: 400, onComplete: function()
				{
					navEffect.custom(0,1);
				}
			});
		
		imageDetailsEffect.hide();
		
		navEffect = new fx.Opacity('hoverNav', { duration: 100 });
		navEffect.hide();
	},
	
	//
	// start()
	// Display overlay and lightbox. If image is part of a set, add siblings to imageArray.
	//
	start: function(imageLink)
	{
		hideSelectBoxes();
		
		// Stretch overlay to fill page and fade in
		var arrayPageSize = getPageSize();
		Element.setHeight('overlay', arrayPageSize[1]);
		overlayEffect.custom(0, 0.8);
		
		// IE places the overlay at the top of the page, must place it at top of viewport
		if (navigator.appVersion.match(/MSIE/) != null) {
			var arrayPageScroll = getPageScroll();
			Element.setTop('overlay', arrayPageScroll[1]);
		}
		
		imageArray = [];
		imageNum = 0;
		
		if (!document.getElementsByTagName){ return; }
		var anchors = document.getElementsByTagName('a');
		
		var lightbox_class = imageLink.getAttribute('rel');
		var desc_height = 90;
		
		// Has a "tallest image size" been specified in the lightbox class?
		// Default to 460px if not specified in class name
		var tallest_image = 460;
		
		var last_dash_pos = lightbox_class.lastIndexOf('-');
		
		if (last_dash_pos != -1) {
			if (lightbox_class.substring(last_dash_pos + 1).match(/^[0-9]+\]$/)) {
				var tallest_image =
					parseInt(lightbox_class.substring(last_dash_pos + 1, lightbox_class.length - 1));
			}
		}
		
		var max_image_and_desc_height = tallest_image + desc_height;
		
		// Should the hires images be used? (Is browser window big enough and does the lightbox class indicate that
		// this gallery has a hires option?)
		var use_hires_images = false;
		
		var arrayPageSize = getPageSize();
		
		if (lightbox_class.indexOf('hires') != -1 && arrayPageSize[3] >= max_image_and_desc_height) {
			use_hires_images = true;
		}
		
		// if image is NOT part of a set..
		if ((lightbox_class == 'lightbox')) {
			// add single image to imageArray
			imageArray.push(new Array(imageLink.getAttribute('href'), imageLink.getAttribute('title')));
		} else {
			// if image is part of a set..
			
			// Adjust first image's path for hires folder, if appropriate
			var first_image_path = imageLink.getAttribute('href')
			
			if (use_hires_images) {
				var last_slash_pos = first_image_path.lastIndexOf('/');
				
				if (last_slash_pos != -1) {
					first_image_path = first_image_path.substring(0, last_slash_pos) + '/hires/' +
						first_image_path.substring(last_slash_pos + 1);
				}
			}
			
			// Loop through anchors, find other images in set, and add them to imageArray
			for (var i = 0; i < anchors.length; i++) {
				var anchor = anchors[i];
				
				if (anchor.getAttribute('href') && (anchor.getAttribute('rel') == lightbox_class)){
					var image_href = anchor.getAttribute('href');
					
					if (use_hires_images) {
						var last_slash_pos = image_href.lastIndexOf('/');
						
						if (last_slash_pos != -1) {
							image_href = image_href.substring(0, last_slash_pos) + '/hires/' +
								image_href.substring(last_slash_pos + 1);
						}
					}
					
					imageArray.push(new Array(image_href, anchor.getAttribute('title')));
				}
			}
			imageArray.removeDuplicates();
			
			while(imageArray[imageNum][0] != first_image_path) { imageNum++; }
		}
		
		// calculate top offset for the lightbox and display 
		var arrayPageScroll = getPageScroll();
		
		if (arrayPageSize[3] <= max_image_and_desc_height) {
			// Don't waste space with largish borders on small viewports
			var lightboxTop = arrayPageScroll[1] + 10;
		} else {
			// Base vertical centring on expected average tallest image and the textual description at the bottom
			var lightboxTop = arrayPageScroll[1] + (((arrayPageSize[3] - tallest_image) / 2) - (desc_height / 2));
		}
		
		Element.setTop('lightbox', lightboxTop);
		Element.show('lightbox');
		this.changeImage(imageNum);
	},
	
	//
	// changeImage()
	// Hide most elements and preload image in preparation for resizing image container.
	//
	changeImage: function(imageNum)
	{
		activeImage = imageNum; // update global var
		
		// Hide elements during transition
		Element.show('loading');
		imageDetailsEffect.hide();
		imageEffect.hide();
		navEffect.hide();
		Element.hide('prevLink');
		Element.hide('nextLink');
		Element.hide('numberDisplay');
		
		imgPreloader = new Image();
		
		// Once image is preloaded, resize image container
		imgPreloader.onload = function()
		{
			Element.setSrc('lightboxImage', imageArray[activeImage][0]);
			myLightbox.resizeImageContainer(imgPreloader.width, imgPreloader.height);
		}
		
		imgPreloader.src = imageArray[activeImage][0];
	},
	
	//
	// resizeImageContainer()
	//
	resizeImageContainer: function( imgWidth, imgHeight)
	{
		// Get current height and width
		this.wCur = Element.getWidth('outerImageContainer');
		this.hCur = Element.getHeight('outerImageContainer');
		
		// calculate size difference between new and old image, and resize if necessary
		wDiff = (this.wCur - borderSize * 2) - imgWidth;
		hDiff = (this.hCur - borderSize * 2) - imgHeight;
		
		// Resize the outerImageContainer very sexy like
		reHeight = new fx.Height('outerImageContainer', { duration: resizeDuration });
		reHeight.custom(Element.getHeight('outerImageContainer'),imgHeight+(borderSize*2)); 
		reWidth = new fx.Width('outerImageContainer', { duration: resizeDuration, onComplete: function()
				{
					imageEffect.custom(0,1);
				}
			});
		
		reWidth.custom(Element.getWidth('outerImageContainer'),imgWidth+(borderSize*2));
		
		// If new and old image are same size and no scaling transition is necessary, do a quick pause to prevent
		// image flicker.
		if ((hDiff == 0) && (wDiff == 0)) {
			if (navigator.appVersion.indexOf("MSIE")!=-1){ pause(250); } else { pause(100); } 
		}
		
		Element.setHeight('prevLink', imgHeight);
		Element.setHeight('nextLink', imgHeight);
		Element.setWidth('imageDataContainer', imgWidth + (borderSize * 2));
		Element.setWidth('hoverNav', imgWidth + (borderSize * 2));
		
		this.showImage();
	},
	
	//
	// showImage()
	// Display image and begin preloading neighbors.
	//
	showImage: function()
	{
		Element.hide('loading');
		myLightbox.updateDetails(); 
		this.preloadNeighborImages();
	},
	
	//
	// updateDetails()
	// Display caption, image number, and bottom nav.
	//
	updateDetails: function()
	{
		Element.show('caption');
		Element.setInnerHTML( 'caption', imageArray[activeImage][1]);
		
		// If image is part of set display 'Image x of x' 
		if (imageArray.length > 1) {
			var prev_image_link = '';
			var next_image_link = '';
			
			if (activeImage != 0) {
				prev_image_link = ' <a href="javascript: return true;" id="prev-image-link">&lt;</a> ';
			}
			
			if (activeImage != (imageArray.length - 1)) {
				next_image_link = ' <a href="javascript: return true;" id="next-image-link">&gt;</a>';
			}
			
			Element.show('numberDisplay');
			Element.setInnerHTML('numberDisplay', prev_image_link + 'Image ' + eval(activeImage + 1) + ' of ' +
				imageArray.length + next_image_link);
			
			if (activeImage != 0) {
				document.getElementById('prev-image-link').onclick = function(e) {
					myLightbox.changeImage(activeImage - 1);
					
					e = e||event;
					e.stopPropagation ? e.stopPropagation() : e.cancelBubble = true;
					
					return false;
				}
			}
			
			if (activeImage != (imageArray.length - 1)) {
				document.getElementById('next-image-link').onclick = function(e)
				{
					myLightbox.changeImage(activeImage + 1);
					
					e = e||event;
					e.stopPropagation ? e.stopPropagation() : e.cancelBubble = true;
					
					return false;
				}
			}
		}
		
		myLightbox.updateNav();
	},
	//
	// updateNav()
	// Display appropriate previous and next hover navigation.
	//
	updateNav: function() {
	
		// if not first image in set, display prev image button
		if (activeImage != 0){
			Element.show('prevLink');
			
			document.getElementById('prevLink').onclick = function()
			{
				myLightbox.changeImage(activeImage - 1); return false;
			}
		}
		
		// if not last image in set, display next image button
		if (activeImage != (imageArray.length - 1)){
			Element.show('nextLink');
			
			document.getElementById('nextLink').onclick = function()
			{
				myLightbox.changeImage(activeImage + 1); return false;
			}
		}
		
		this.enableKeyboardNav();
	},
	
	//
	// enableKeyboardNav()
	//
	enableKeyboardNav: function()
	{
		document.onkeydown = this.keyboardAction; 
	},
	
	//
	// disableKeyboardNav()
	//
	disableKeyboardNav: function()
	{
		document.onkeydown = '';
	},
	
	//
	// keyboardAction()
	//
	keyboardAction: function(e)
	{
		if (e == null) {
			// IE
			keycode = event.keyCode;
		} else {
			// Mozilla
			keycode = e.which;
		}
		
		key = String.fromCharCode(keycode).toLowerCase();
		
		if ((key == 'x') || (key == 'o') || (key == 'c') || keycode == 27) {
			// Close lightbox
			myLightbox.end();
		} else if (key == 'p' || keycode == 37) {
			// Display previous image
			if (activeImage != 0) {
				myLightbox.disableKeyboardNav();
				myLightbox.changeImage(activeImage - 1);
			}
		} else if (key == 'n' || keycode == 39) {
			// Display next image
			if (activeImage != (imageArray.length - 1)) {
				myLightbox.disableKeyboardNav();
				myLightbox.changeImage(activeImage + 1);
			}
		}
	},
	
	//
	// preloadNeighborImages()
	// Preload previous and next images.
	//
	preloadNeighborImages: function()
	{
		if ((imageArray.length - 1) > activeImage) {
			preloadNextImage = new Image();
			preloadNextImage.src = imageArray[activeImage + 1][0];
		}
		if (activeImage > 0) {
			preloadPrevImage = new Image();
			preloadPrevImage.src = imageArray[activeImage - 1][0];
		}
	
	},
	
	//
	// end()
	//
	end: function()
	{
		this.disableKeyboardNav();
		Element.hide('lightbox');
		imageEffect.toggle();
		overlayEffect.custom(0.8, 0);
		showSelectBoxes();
		
		// Reset the width and height so next call to the lightbox resizes from nothing - looks much
		// better!
		Element.setWidth('outerImageContainer', 0);
		Element.setHeight('outerImageContainer', 0);
	}
}

//
// getPageScroll()
// Returns array with x,y page scroll values.
// Core code from - quirksmode.org
//
function getPageScroll()
{
	var yScroll;
	
	if (self.pageYOffset) {
		yScroll = self.pageYOffset;
	} else if (document.documentElement && document.documentElement.scrollTop){
		// Explorer 6 Strict
		yScroll = document.documentElement.scrollTop;
	} else if (document.body) {
		// All other Explorers
		yScroll = document.body.scrollTop;
	}
	
	arrayPageScroll = new Array('',yScroll) ;
	
	return arrayPageScroll;
}

//
// getPageSize()
// Returns array with page width, height and window width, height
// Core code from - quirksmode.org
// Edit for Firefox by pHaez
//
function getPageSize()
{
	var xScroll, yScroll;
	
	if (window.innerHeight && window.scrollMaxY) {
		xScroll = document.body.scrollWidth;
		yScroll = window.innerHeight + window.scrollMaxY;
	} else if (document.body.scrollHeight > document.body.offsetHeight){
		// All but Explorer Mac
		xScroll = document.body.scrollWidth;
		yScroll = document.body.scrollHeight;
	} else {
		// Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
		xScroll = document.body.offsetWidth;
		yScroll = document.body.offsetHeight;
	}
	
	var windowWidth, windowHeight;
	if (self.innerHeight) {
		// All except Explorer
		windowWidth = self.innerWidth;
		windowHeight = self.innerHeight;
	} else if (document.documentElement && document.documentElement.clientHeight) {
		// Explorer 6 Strict Mode
		windowWidth = document.documentElement.clientWidth;
		windowHeight = document.documentElement.clientHeight;
	} else if (document.body) {
		// Other Explorers
		windowWidth = document.body.clientWidth;
		windowHeight = document.body.clientHeight;
	}
	
	// For small pages with total height less then height of the viewport
	if (yScroll < windowHeight){
		pageHeight = windowHeight;
	} else { 
		pageHeight = yScroll;
	}
	
	// For small pages with total width less then width of the viewport
	if (xScroll < windowWidth){
		pageWidth = windowWidth;
	} else {
		pageWidth = xScroll;
	}
	
	arrayPageSize = new Array(pageWidth, pageHeight, windowWidth, windowHeight);
	
	return arrayPageSize;
}

//
// getKey(key)
// Gets keycode. If 'x' is pressed then it hides the lightbox.
//
function getKey(e)
{
	if (e == null) {
		// IE
		keycode = event.keyCode;
	} else {
		// Mozilla
		keycode = e.which;
	}
	
	key = String.fromCharCode(keycode).toLowerCase();
	
	if (key == 'x') {
	}
}

//
// listenKey()
//
function listenKey () { document.onkeypress = getKey; }


function showSelectBoxes()
{
	selects = document.getElementsByTagName("select");
	
	for (i = 0; i != selects.length; i++) {
		selects[i].style.visibility = "visible";
	}
}


function hideSelectBoxes()
{
	selects = document.getElementsByTagName("select");
	
	for (i = 0; i != selects.length; i++) {
		selects[i].style.visibility = "hidden";
	}
}

//
// pause(numberMillis)
// Pauses code execution for specified time. Uses busy code, not good.
// Code from http://www.faqts.com/knowledge_base/view.phtml/aid/1602
//
function pause(numberMillis)
{
	var now = new Date();
	var exitTime = now.getTime() + numberMillis;
	
	while (true) {
		now = new Date();
		if (now.getTime() > exitTime)
			return;
	}
}


function initLightbox() { myLightbox = new Lightbox(); }