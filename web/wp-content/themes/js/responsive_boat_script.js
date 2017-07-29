/* =================================

 LOADER

 =================================== */

// makes sure the whole site is loaded

jQuery(window).load(function() {

	// will first fade out the loading animation

	jQuery(".status").fadeOut();

	// will fade out the whole DIV that covers the website.

	jQuery(".preloader").delay(1000).fadeOut("slow");


	jQuery('.carousel').carousel('pause');

})


jQuery(document).ready(function() {
	var current_height = jQuery('.header .container').height();
	jQuery('.header').css('min-height',current_height);

});


/* show/hide reCaptcha */
jQuery(document).ready(function() {

	var thisOpen = false;
	jQuery('.contact-form .form-control').each(function(){
		if ( jQuery(this).val().length > 0 ){
			thisOpen = true;
			jQuery('.zerif-g-recaptcha').css('display','block').delay(1000).css('opacity','1');
			return false;
		}
	});
	if ( thisOpen == false && (typeof jQuery('.contact-form textarea').val() != 'undefined') && (jQuery('.contact-form textarea').val().length > 0) ) {
		thisOpen = true;
		jQuery('.zerif-g-recaptcha').css('display','block').delay(1000).css('opacity','1');
	}
	jQuery('.contact-form input, .contact-form textarea').focus(function(){
		if ( !jQuery('.zerif-g-recaptcha').hasClass('recaptcha-display') ) {
			jQuery('.zerif-g-recaptcha').css('display','block').delay(1000).css('opacity','1');
		}
	});

});


/* =================================

 ===  Bootstrap Fix              ====

 =================================== */

if (navigator.userAgent.match(/IEMobile\/10\.0/)) {

	var msViewportStyle = document.createElement('style')

	msViewportStyle.appendChild(

		document.createTextNode(

			'@-ms-viewport{width:auto!important}'

		)

	)

	document.querySelector('head').appendChild(msViewportStyle)

}



/* =================================

 ===  STICKY NAV                 ====

 =================================== */



jQuery(document).ready(function() {



	// Sticky Header - http://jqueryfordesigners.com/fixed-floating-elements/

	var top = jQuery('#main-nav').offset().top - parseFloat(jQuery('#main-nav').css('margin-top').replace(/auto/, 0));



	jQuery(window).scroll(function (event) {

		// what the y position of the scroll is

		var y = jQuery(this).scrollTop();



		// whether that's below the form

		if (y >= top) {

			// if so, ad the fixed class

			jQuery('#main-nav').addClass('fixed');
			if(jQuery('body').hasClass('admin-bar')) {
				jQuery('#main-nav').addClass('rb-fixed');
			}

		} else {

			// otherwise remove it

			jQuery('#main-nav').removeClass('fixed');
			if(jQuery('body').hasClass('admin-bar')) {
				jQuery('#main-nav').removeClass('rb-fixed');
			}
		}

	});

});


/*=================================

 ===  SMOOTH SCROLL             ====

 =================================== */

jQuery(document).ready(function(){
	jQuery('#site-navigation a[href*="#"]:not([href="#"]), header.header a[href*="#"]:not([href="#"])').bind('click',function () {
		var headerHeight;
		var hash    = this.hash;
		var idName  = hash.substring(1);    // get id name
		var alink   = this;                 // this button pressed
		// check if there is a section that had same id as the button pressed
		if ( jQuery('section [id*=' + idName + ']').length > 0 && jQuery(window).width() >= 751 ){
			jQuery('.current').removeClass('current');
			jQuery(alink).parent('li').addClass('current');
		}else{
			jQuery('.current').removeClass('current');
		}
		if ( jQuery(window).width() >= 751 ) {
			headerHeight = jQuery('#main-nav').height();
		} else {
			headerHeight = 0;
		}
		if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
			var target = jQuery(this.hash);
			target = target.length ? target : jQuery('[name=' + this.hash.slice(1) +']');
			if (target.length) {
				jQuery('html,body').animate({
					scrollTop: target.offset().top - headerHeight + 10
				}, 1200);
				return false;
			}
		}
	});
});

jQuery(document).ready(function(){
	var headerHeight;
	jQuery('.current').removeClass('current');
	jQuery('#site-navigation a[href$="' + window.location.hash + '"]').parent('li').addClass('current');
	if ( jQuery(window).width() >= 751 ) {
		headerHeight = jQuery('#main-nav').height();
	} else {
		headerHeight = 0;
	}
	if (location.pathname.replace(/^\//,'') == window.location.pathname.replace(/^\//,'') && location.hostname == window.location.hostname) {
		var target = jQuery(window.location.hash);
		if (target.length) {
			jQuery('html,body').animate({
				scrollTop: target.offset().top - headerHeight + 10
			}, 1200);
			return false;
		}
	}
});

/* ================================

 ===  PARALLAX                  ====

 ================================= */

jQuery(document).ready(function(){

	var jQuerywindow = jQuery(window);

	jQuery('div[data-type="background"], header[data-type="background"], section[data-type="background"]').each(function(){

		var jQuerybgobj = jQuery(this);

		jQuery(window).scroll(function() {

			var yPos = -(jQuerywindow.scrollTop() / jQuerybgobj.data('speed'));

			var coords = '50% '+ yPos + 'px';

			jQuerybgobj.css({

				backgroundPosition: coords

			});

		});

	});

});


/* ======================================

 ============ MOBILE NAV =============== */

jQuery('.navbar-toggle').on('click', function () {

	jQuery(this).toggleClass('active');

});


/* SETS THE HEADER HEIGHT */
jQuery(window).load(function(){
	setminHeightHeader();
});
jQuery(window).resize(function() {
	setminHeightHeader();
});
function setminHeightHeader()
{
	jQuery('#main-nav').css('min-height','75px');
	jQuery('.header').css('min-height','75px');
	var minHeight = parseInt( jQuery('#main-nav').height() );
	jQuery('#main-nav').css('min-height',minHeight);
	jQuery('.header').css('min-height',minHeight);
}
/* - */


/* STICKY FOOTER */
jQuery(window).load(fixFooterBottom);
jQuery(window).resize(fixFooterBottom);

function fixFooterBottom(){

	var header      = jQuery('header.header');
	var footer      = jQuery('footer#footer');
	var content     = jQuery('.site-content > .container');

	content.css('min-height', '1px');

	var headerHeight  = header.outerHeight();
	var footerHeight  = footer.outerHeight();
	var contentHeight = content.outerHeight();
	var windowHeight  = jQuery(window).height();

	var totalHeight = headerHeight + footerHeight + contentHeight;

	if (totalHeight<windowHeight){
		content.css('min-height', windowHeight - headerHeight - footerHeight );
	}else{
		content.css('min-height','1px');
	}
}


/*** CENTERED MENU */
var callback_menu_align = function () {

	var headerWrap		= jQuery('.header');
	var navWrap			= jQuery('#site-navigation');
	var logoWrap    	= jQuery('.responsive-logo');
	var containerWrap   = jQuery('.container');
	var classToAdd    	= 'menu-align-center';

	if ( headerWrap.hasClass(classToAdd) )
	{
		headerWrap.removeClass(classToAdd);
	}
	var logoWidth     = logoWrap.outerWidth();
	var menuWidth     = navWrap.outerWidth();
	var containerWidth  = containerWrap.width();

	if ( menuWidth + logoWidth > containerWidth ) {
		headerWrap.addClass(classToAdd);
	}
	else
	{
		if ( headerWrap.hasClass(classToAdd) )
		{
			headerWrap.removeClass(classToAdd);
		}
	}
}
jQuery(window).load(callback_menu_align);
jQuery(window).resize(callback_menu_align);

var isMobile = {
	Android: function() {
		return navigator.userAgent.match(/Android/i);
	},
	BlackBerry: function() {
		return navigator.userAgent.match(/BlackBerry/i);
	},
	iOS: function() {
		return navigator.userAgent.match(/iPhone|iPad|iPod/i);
	},
	Opera: function() {
		return navigator.userAgent.match(/Opera Mini/i);
	},
	Windows: function() {
		return navigator.userAgent.match(/IEMobile/i);
	},
	any: function() {
		return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
	}
};

/* Rollover on mobile devices */
if( isMobile.any() ) {

	/* Our team section */
	jQuery('.team-member').on('click', function(){
		jQuery('.team-member-open').removeClass('team-member-open');
		jQuery(this).addClass('team-member-open');
		event.stopPropagation();
	});
	jQuery("html").click(function() {
		jQuery('.team-member-open').removeClass('team-member-open');
	});

	/* Portfolio section */
	jQuery(document).ready(function(){
		jQuery('.cbp-rfgrid li').prepend('<p class="cbp-rfgrid-tr"></p>');
	});
	jQuery('.cbp-rfgrid li').on('click', function(){
		if ( !jQuery(this).hasClass('cbp-rfgrid-open') ){
			jQuery('.cbp-rfgrid-tr').css('display','block');
			jQuery('.cbp-rfgrid-open').removeClass('cbp-rfgrid-open');

			jQuery(this).addClass('cbp-rfgrid-open');
			jQuery(this).find('.cbp-rfgrid-tr').css('display','none');
			event.stopPropagation();
		}
	});
	jQuery("html").click(function() {
		jQuery('.cbp-rfgrid-tr').css('display','block');
		jQuery('.cbp-rfgrid-open').removeClass('cbp-rfgrid-open');
	});

}


/* fix for IE9 placeholders */

jQuery(document).ready(function(){

	if (document.createElement("input").placeholder == undefined) {

		jQuery('.contact-form input, .contact-form textarea').focus(function () {
			if ( (jQuery(this).attr('placeholder') != '') && (jQuery(this).val() == jQuery(this).attr('placeholder')) ) {
				jQuery(this).val('').removeClass('zerif-hasPlaceholder');
			}
		}).blur(function () {
			if ( (jQuery(this).attr('placeholder') != '') && (jQuery(this).val() == '' || (jQuery(this).val() == jQuery(this).attr('placeholder')))) {
				jQuery(this).val(jQuery(this).attr('placeholder')).addClass('zerif-hasPlaceholder');
			}
		});

		jQuery('.contact-form input').blur();
		jQuery('.contact-form textarea').blur();

		jQuery('form.contact-form').submit(function () {
			jQuery(this).find('.zerif-hasPlaceholder').each(function() { jQuery(this).val(''); });
		});
	}
});

jQuery( document ).ready( function(){
	if( jQuery( '.navbar-black' ).length>0 ) {
		jQuery('.navbar-black-init').css( 'display', 'bloack' );
		jQuery('.navbar-black').css( 'display', 'none' );
	}
} );

jQuery(window).on('scroll', function () {

	var rb_scrollTop = jQuery(window).scrollTop();

	if( typeof jQuery('.rb_logo').offset() != 'undefined' ) {
		var rb_elementOffset = jQuery('.rb_logo').offset().top;

		var rb_distance = rb_elementOffset - rb_scrollTop;
		if(rb_distance < 0) {
			jQuery('.navbar-black-init').css( 'display', 'none' );
			jQuery('.navbar-black').css( 'display', 'block' );
			callback_menu_align();
		}
		else {
			jQuery('.navbar-black').css( 'display', 'none' );
			jQuery('.navbar-black-init').css( 'display', 'block' );
			callback_menu_align();
		}

	}

});



jQuery(window).load(function(){
	var widthW = jQuery(window).outerWidth();
	if( widthW>992-15 ){
		latestNewsHeight(4);		
	} else if( widthW>600-17 ) {
		latestNewsHeight(2);
	} else {
		latestNewsHeight(1);
	}
});

jQuery(window).resize(function(){
	var widthW = jQuery(window).outerWidth();
	if( widthW>992-17 ){
		latestNewsHeight(4);
	} else if( widthW>600-17 ) {
		latestNewsHeight(2);
	} else {
		latestNewsHeight(1);
	}
});

function latestNewsHeight (nr) {
	var thisH = jQuery(window).width() / nr;
	jQuery( '.rb-latest-news-image' ).height( thisH );
}

