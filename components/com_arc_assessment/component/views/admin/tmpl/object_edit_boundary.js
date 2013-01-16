/**
 * @package     Arc
 * @subpackage  Assessment
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

var objEditBoundary = new Class({
	// class initializer
	initialize: function( boundaries )
	{
		this.markStyle = boundaries.mark_style;
		this.displayStyle = boundaries.display_style;
		this.markValues = boundaries.mark_values;
		this.displayBounds = boundaries.display_bounds;
	},
	
	// retrieve mark style
	getMarkStyle: function()
	{
		return this.markStyle;
	},
	
	// set mark style and also display style if it is set to mirror mark style
	setMarkStyle: function( markStyle )
	{
		this.markStyle = markStyle;
		this.markValues = boundary_defaults.mark_values[markStyle];
		if( markStyle == 'comment' ) {
			this.displayStyle = '';
		}
		if( this.displayStyle === '' ) {
			this.displayBounds = boundary_defaults.display_bounds[markStyle];
		}
	},
	
	// retrieve display style
	getDisplayStyle: function()
	{
		return this.displayStyle;
	},
	
	// set display style directly or mirror mark style if required
	setDisplayStyle: function( displayStyle )
	{
		this.displayStyle = displayStyle;
		// if not -- As Marked --
		if( displayStyle !== '' ) {
			this.displayBounds = boundary_defaults.display_bounds[displayStyle];
		}
		// if -- As Marked --
		else {
			// we mirroring a numeric markstyle
			if( markstyle_info.get( this.markStyle ).type == 'numeric' ) {
				this.displayBounds = this.markValues;
			}
			// we mirroring a non-numeric markstyle
			else {
				this.displayBounds = boundary_defaults.display_bounds[this.markStyle];
			}
		}
	},
	
	// retrieve sorted array containing mark/value hash pairs
	getMarkValues: function( justMinMax, intervals )
	{
		if( justMinMax == undefined ) {
			justMinMax = false;
		}
		var markValuesArray = new Array();
		var i = 0;
		$each( this.markValues, function(value, mark) {
			markValuesArray[i++] = new Hash({
				'value': value.toInt(),
				'mark':  mark
			});
		})
		
		// add intervening pseudo default stops if numeric mark type
		if( (markstyle_info.get( this.markStyle ).type == 'numeric') && !justMinMax ) {
			var firstMark = markValuesArray[0].get( 'mark' ).toInt();
			var secondMark = markValuesArray[1].get( 'mark' ).toInt();
			var lowMark = Math.min( firstMark, secondMark );
			var markRange = Math.abs( firstMark - secondMark );
			
			// set a sensible number of intervals for numeric nobs
			if( (intervals == undefined) || (intervals > markRange) ) {
				var intervals = Math.min( markRange, 5 );
			}
			
			for( i = 1; i < intervals; i++ ) {
				var newMark = ( lowMark + (markRange * (i / intervals)) ).round(); 
				var newHash = new Hash({
					'value': ( 100 * ((newMark - lowMark) / markRange) ).round(),
					'mark': newMark
				});
				markValuesArray.include( newHash );
			}
		}
		
		// sort array ascending on value
		markValuesArray.sort( function(a, b) {
			return a.get( 'value' ) - b.get( 'value' );
		})
		
		return markValuesArray;
	},
	
	// set mark values
	setMarkValues: function( markValues )
	{
		this.markValues = markValues;
	},
	
	// set an individual mark / value pair
	setMarkValue: function( key, val )
	{
		this.markValues[key] = val;
	},
	
	// retrieve sorted array containing display/value hash pairs
	getDisplayBounds: function( justMinMax, intervals )
	{
		if( justMinMax == undefined ) {
			justMinMax = false;
		}
		var displayBoundsArray = new Array();
		var i = 0;
		$each( this.displayBounds, function(value, mark) {
			displayBoundsArray[i++] = new Hash({
				'value': value.toInt(),
				'mark':  mark
			});
		})
		
		// add intervening pseudo default stops if numeric mark type
		var displayStyle = (this.displayStyle !== '') ? this.displayStyle : this.markStyle;
		if( (markstyle_info.get( displayStyle ).type == 'numeric') && !justMinMax ) {
			var firstMark = displayBoundsArray[0].get( 'mark' ).toInt();
			var secondMark = displayBoundsArray[1].get( 'mark' ).toInt();
			var lowMark = Math.min( firstMark, secondMark );
			var markRange = Math.abs( firstMark - secondMark );
			
			// set a sensible number of intervals for numeric nobs
			if( (intervals == undefined) || (intervals > markRange) ) {
				var intervals = Math.min( markRange, 5 );
			}
			
			for( i = 1; i < intervals; i++ ) {
				var newMark = ( lowMark + (markRange * (i / intervals)) ).round(); 
				var newHash = new Hash({
					'value': ( 100 * ((newMark - lowMark) / markRange) ).round(),
					'mark': newMark
				});
				displayBoundsArray.include( newHash );
			}
		}
		
		// sort array ascending on value
		displayBoundsArray.sort( function(a, b) {
			return a.get( 'value' ) - b.get( 'value' );
		})
		
		return displayBoundsArray;
	},
	
	// set diaplay boundaries
	setDisplayBounds: function( displayBounds )
	{
		this.displayBounds = displayBounds;
	},
	
	// set an individual display bound / value pair
	setDisplayBound: function( key, val )
	{
		this.displayBounds[key] = String( val );
	},
	
	// set new range for numeric mark types
	setMarksNumRange: function( min, max )
	{
		this.markValues = {};
		this.markValues[min] = 0;
		this.markValues[max] = 100;
		if( this.displayStyle === '' ) {
			this.setDisplayNumRange( min, max );
		}
	},
	
	// set new range for numeric display types
	setDisplayNumRange: function( min, max )
	{
		this.displayBounds = {};
		this.displayBounds[min] = 0;
		this.displayBounds[max] = 100;
	},
	
	// Json encode the pertinent boundary object data
	toString: function()
	{
		return Json.toString({
			'mark_style': this.markStyle,
			'display_style': this.displayStyle,
			'mark_values': this.markValues,
			'display_bounds': this.displayBounds
		});
	}
});
