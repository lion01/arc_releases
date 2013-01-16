/**
 * @package     Arc
 * @subpackage  TV
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

window.addEvent( 'domready', function() {
	// get ajax ratings spinner div
	ajaxRatingsSpinner = $( 'ajax_ratings_spinner_div' );
	
	// get ajax ratings message div
	ajaxRatingsMessage = $( 'ajax_ratings_message_div' );
	
	// get ratings div then go ahead and make it sparkle
	if( (ratingsDiv = $('ratings_div')) != null ) {
		initRatingsDiv();
	}
});

/**
 * Initialise the ratings div elements
 */
function initRatingsDiv()
{
	// remove relevant events
	ratingsDiv.removeEvents( 'mouseenter' );
	ratingsDiv.removeEvents( 'mouseleave' );
	
	// get global rating input
	globalRatingInput = $( 'ratings_global' );
	
	// get user rating input
	userRatingInput = $( 'ratings_user' );
	
	// set the tooltip
	setToolTip();
	
	// get rating submit action
	ratingAction = $( 'ratings_action' ).getValue();
	
	ratingsDiv.addEvent( 'mouseenter', function(e) {
		// prevent the default event from firing / bubbling
		e = new Event(e).stop();
		
		// show user rating overlay
		userRating( true );
	});
	
	ratingsDiv.addEvent( 'mouseleave', function(e) {
		// prevent the default event from firing / bubbling
		e = new Event(e).stop();
		
		// hide user rating overlay
		userRating( false );
	});
}

/**
 * Set the tooltip for the ratings div
 */
function setToolTip()
{
	// get the values we need
	var globalRating = globalRatingInput.getValue() > 0 ? globalRatingInput.getValue() : 'No ratings.';
	var userRating = userRatingInput.getValue() > 0 ? userRatingInput.getValue() : 'Please rate me.';
	var divTitle = 'Ratings::Global rating: ' + globalRating + '<br />Your rating: ' + userRating;
		
	
	// add title and class needed for tooltip to ratings div
	ratingsDiv.setProperty( 'title', divTitle );
	ratingsDiv.addClass( 'arcTip' );
	
	// generate the tooltip
	ratingsTip = new Tips(ratingsDiv, {
		'className': 'custom',
		'initialize': function() {
			this.fx = new Fx.Style(this.toolTip, 'opacity', {
				'duration': 500,
				'wait': false
			}).set(0);
		},
		'onShow': function(toolTip) {
			this.fx.start(1);
		},
		'onHide': function(toolTip) {
			this.fx.start(0);
		}
	});
}

/**
 * Enble or disable the user rating stars overlay
 * 
 * @param boolean show  Show user ratings if true, hide if false
 */
function userRating( show )
{
	if( show ) {
		userRatingShown = true;
		
		// get coordinates of the ratings div
		ratingsDivCoords = ratingsDiv.getCoordinates();
		
		// add mousemove event for interactive rating display
		ratingsDiv.addEvent( 'mousemove', function(e) {
			// get the position of the mouse within the ratings div (0 - div width)
			mousePosX = e.clientX - ratingsDivCoords.left + 1;
			
			// calculate effective star rating of mouse position (1 - 5, integer)
			posRating = Math.ceil( (mousePosX / ratingsDivCoords.width) * 5 );
			
			// update the interactive overlay to reflect current mouse position
			ratingsDiv.setStyles({
				'background-position': calcBkgOffset( posRating, 'user' ),
				'cursor': 'pointer'
			});
		});
		
		// add the mouse click event to submit the user rating
		ratingsDiv.addEvent( 'click', function(e) {
			// prevent the default onClick event from firing / bubbling
			e = new Event(e).stop();
			
			// add the rating to the data being sent
			var inputString = '&rating=' + posRating;
			
			var raw = new Ajax( ratingAction, {
				'data': inputString,
				'onRequest': function() {
					// hide message div
					ajaxRatingsMessage.setStyle( 'display', 'none' );
					
					// show ajax spinner
					ajaxRatingsSpinner.setStyle( 'display', 'block' );
				},
				'onSuccess': function() {
					// remove ajax spinner
					ajaxRatingsSpinner.setStyle( 'display', 'none' );
					
					// capture and process XHR response
					var response = Json.evaluate( raw.response.text );
					ajaxRatingsMessage.empty();
					ajaxRatingsMessage.setText( response.message );
					ajaxRatingsMessage.setStyle( 'display', 'block' );
					globalRatingInput.setProperty( 'value', response.global );
					userRatingInput.setProperty( 'value', response.user );
					if( !userRatingShown ) {
						ratingsDiv.setStyle( 'background-position', calcBkgOffset(globalRatingInput.getValue(), 'global') );
					}
					
					// reset the tooltip by re-initialising the ratings div
					ratingsTip.hide();
					initRatingsDiv();
				},
				'onFailure': function() {
					// remove ajax spinner
					ajaxRatingsSpinner.setStyle( 'display', 'none' );
				}
			}).request();
		});
	}
	else {
		userRatingShown = false;
		
		// remove events added when we mouseenter'd
		ratingsDiv.removeEvents( 'mousemove' );
		ratingsDiv.removeEvents( 'click' );
		
		// reset image to the current global rating
		ratingsDiv.setStyles({
			'background-position': calcBkgOffset( globalRatingInput.getValue(), 'global' ),
			'cursor': ''
		});
	}
}

/**
 * Determine the background offset for a given rating value
 * 
 * @return str  Value to be set for background-position of star ratings image
 */
function calcBkgOffset( rating, type )
{
	// calc for global rating display
	if( type == 'global' ) {
		var half;
		var upper;
		var numOfStars;
		var bkgOffset;
		
		if( rating >= (half = (upper = Math.ceil(rating))- 0.5) + 0.25 ) {
			numOfStars = upper;
		}
		else if( rating < half - 0.25 ) {
			numOfStars = Math.floor( rating );
		}
		else {
			numOfStars = half;
		}
		
		bkgOffset = ( numOfStars * -48 );
	}
	// calc for user rating input
	else if( type == 'user' ) {
		bkgOffset = ((rating - 1) * -24) - 264;
	}
	
	return '0px ' + bkgOffset + 'px';
}