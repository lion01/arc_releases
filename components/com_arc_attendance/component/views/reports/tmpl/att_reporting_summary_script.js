/**
 * @package     Arc
 * @subpackage  Attendance
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

function summarySwitching( c ) {
	c = (c) ? c : window.document;
	
	// get div elements by ID (mooTools style)
	var sumStatDiv =      c.getElement( '#summary_table_stat' );    // statutory table div
	var sumAllDiv  =      c.getElement( '#summary_table_all' );     // all table div
	var sumAllPerDiv  =   c.getElement( '#summary_table_all_per' ); // all percent table percent
	var sumStatDivExists = ( sumStatDiv == null ) ? false : true; // do we have stat data?
	
	// get button elements by ID (mooTools style)
	var statButton    = c.getElement( '#stat_pie_button' );      // statutory pie chart button
	var allButton     = c.getElement( '#all_histo_button' );     // all stacked histogram button
	var allPerButton  = c.getElement( '#all_per_histo_button' ); // all percentage stacked histogram button
	
	// get the stat table subrows
	var statSubRows = c.getElements( '.stat_sub_row' ); // statutory table sub rows
	var statSubRowsExists = ( statSubRows == null ) ? false : true; // do we have stat table subrows
	
	// get the stat table subrows selector cells
	if( statSubRowsExists ) {
		var statSubRowsClickers = c.getElements( '.stat_sub_row_clicker' ); // statutory table sub row clickers
	}
	
	// get the all table subrows
	var allSubRows = c.getElements( '.all_sub_row' ); // all table sub rows
	var allSubRowsExists = ( allSubRows == null ) ? false : true; // do we have all table subrows
	
	// get the all table subrows selector cells
	if( allSubRowsExists ) {
		var allSubRowsClickers = c.getElements( '.all_sub_row_clicker' ); // all table sub row clickers
	}
	
	// get the all per table subrows
	var allPerSubRows = c.getElements( '.all_per_sub_row' ); // all per table sub rows
	var allPerSubRowsExists = ( allPerSubRows == null ) ? false : true; // do we have all per table subrows
	
	// get the all per table subrows selector cells
	if( allPerSubRowsExists ) {
		var allPerSubRowsClickers = c.getElements( '.all_per_sub_row_clicker' ); // all per table sub row clickers
	}
	
	// default settings
	if( sumStatDivExists ) {
		sumStatDiv.setStyle( 'display', 'inline' );
		sumAllDiv.setStyle( 'display', 'none' );
	}
	else {
		sumAllDiv.setStyle( 'display', 'inline' );
	}
	sumAllPerDiv.setStyle( 'display', 'none' );
	if( statSubRowsExists ) {
		statSubRows.each( function(row) {
			row.setStyle( 'display', 'none' );
		});
	}
	if( allSubRowsExists ) {
		allSubRows.each( function(row) {
			row.setStyle( 'display', 'none' );
		});
	}
	if( allPerSubRowsExists ) {
		allPerSubRows.each( function(row) {
			row.setStyle( 'display', 'none' );
		});
	}
	
	// add events to statButton if we have stat data
	if( sumStatDivExists ) {
		statButton.addEvent( 'mouseover', function() {
			sumStatDiv.setStyle( 'display', 'inline' );
			sumAllDiv.setStyle( 'display', 'none' );
			sumAllPerDiv.setStyle( 'display', 'none' );
		});
	}
	
	// add events to allButton
	allButton.addEvent( 'mouseover', function() {
		if( sumStatDivExists ) {
			sumStatDiv.setStyle( 'display', 'none' );
		}
		sumAllDiv.setStyle( 'display', 'inline' );
		sumAllPerDiv.setStyle( 'display', 'none' );
	});
	
	// add events to allPerButton
	allPerButton.addEvent( 'mouseover', function() {
		if( sumStatDivExists ) {
			sumStatDiv.setStyle( 'display', 'none' );
		}
		sumAllDiv.setStyle( 'display', 'none' );
		sumAllPerDiv.setStyle( 'display', 'inline' );
	});
	
	// loop through array of the stat clickers and assign relevant click events
	if( statSubRowsExists ) {
		var statHideables = new Array();
		var statRowSpans = new Array;
		
		statSubRowsClickers.each( function(statSubRowClicker, i) {
			statHideables[i] = new Array();
			statRowSpans[i] = statSubRowClicker.getFirst().getProperty( 'rowspan' );
			
			// add each of the subrows to the relevant array
			var statNextSibling = statSubRowClicker.getNext();
			while( statNextSibling.hasClass('stat_sub_row') == true ) {
				statHideables[i].include( statNextSibling );
				statNextSibling = statNextSibling.getNext();
			}
			
			// make rowspan of first clicker row = 1 as we have hidden the subrows by default
			statSubRowClicker.getFirst().setProperty( 'rowspan', '1' );
			
			// make cursor a pointer as you hover over a clicker row
			statSubRowClicker.setStyle( 'cursor', 'pointer' );
			
			// add event listener
			statSubRowClicker.addEvent( 'click', function() {
				
				//bold toggle the clicker row
				if( statSubRowClicker.getStyle('fontWeight') == 'bold' ) {
					statSubRowClicker.setStyle( 'fontWeight', 'normal' );
				}
				else {
					statSubRowClicker.setStyle( 'fontWeight', 'bold' );
				}
				
				// rowspan toggle the clicker row first cell
				if( statSubRowClicker.getFirst().getProperty( 'rowspan' ) == '1' ) {
					statSubRowClicker.getFirst().setProperty( 'rowspan', statRowSpans[i] );
				}
				else
				{
					statSubRowClicker.getFirst().setProperty( 'rowspan', '1' );
				}
				
				// toggle the visibility of the subrows
				statHideables[i].each( function(statSubRow) {
					if( statSubRow.getStyle('display') != 'none' ) {
						statSubRow.setStyle( 'display', 'none' );
					}
					else {
						statSubRow.setStyle( 'display', '' );
					}
				});
				
				return false;
			});
		});
	}
	
	// loop through array of the all clickers and assign relevant click events
	if( allSubRowsExists ) {
		var allHideables = new Array();
		var allRowSpans = new Array;
		
		allSubRowsClickers.each( function(allSubRowClicker, i) {
			allHideables[i] = new Array();
			allRowSpans[i] = allSubRowClicker.getFirst().getProperty( 'rowspan' );
			
			// add each of the subrows to the relevant array
			var allNextSibling = allSubRowClicker.getNext();
			while( allNextSibling.hasClass('all_sub_row') == true ) {
				allHideables[i].include( allNextSibling );
				allNextSibling = allNextSibling.getNext();
			}
			
			// make rowspan of first clicker row = 1 as we have hidden the subrows by default
			allSubRowClicker.getFirst().setProperty( 'rowspan', '1' );
			
			// make cursor a pointer as you hover over a clicker row
			allSubRowClicker.setStyle( 'cursor', 'pointer' );
			
			// add event listener
			allSubRowClicker.addEvent( 'click', function() {
				
				//bold toggle the clicker row
				if( allSubRowClicker.getStyle('fontWeight') == 'bold' ) {
					allSubRowClicker.setStyle( 'fontWeight', 'normal' );
				}
				else {
					allSubRowClicker.setStyle( 'fontWeight', 'bold' );
				}
				
				// rowspan toggle the clicker row first cell
				if( allSubRowClicker.getFirst().getProperty( 'rowspan' ) == '1' ) {
					allSubRowClicker.getFirst().setProperty( 'rowspan', allRowSpans[i] );
				}
				else
				{
					allSubRowClicker.getFirst().setProperty( 'rowspan', '1' );
				}
				
				// toggle the visibility of the subrows
				allHideables[i].each( function(allSubRow) {
					if( allSubRow.getStyle('display') != 'none' ) {
						allSubRow.setStyle( 'display', 'none' );
					}
					else {
						allSubRow.setStyle( 'display', '' );
					}
				});
				
				return false;
			});
		});
	}
	
	// loop through array of the all per clickers and assign relevant click events
	if( allPerSubRowsExists ) {
		var allPerHideables = new Array();
		var allPerRowSpans = new Array;
		
		allPerSubRowsClickers.each( function(allPerSubRowClicker, i) {
			allPerHideables[i] = new Array();
			allPerRowSpans[i] = allPerSubRowClicker.getFirst().getProperty( 'rowspan' );
			
			// add each of the subrows to the relevant array
			var allPerNextSibling = allPerSubRowClicker.getNext();
			while( allPerNextSibling.hasClass('all_per_sub_row') == true ) {
				allPerHideables[i].include( allPerNextSibling );
				allPerNextSibling = allPerNextSibling.getNext();
			}
			
			// make rowspan of first clicker row = 1 as we have hidden the subrows by default
			allPerSubRowClicker.getFirst().setProperty( 'rowspan', '1' );
			
			// make cursor a pointer as you hover over a clicker row
			allPerSubRowClicker.setStyle( 'cursor', 'pointer' );
			
			// add event listener
			allPerSubRowClicker.addEvent( 'click', function() {
				
				//bold toggle the clicker row
				if( allPerSubRowClicker.getStyle('fontWeight') == 'bold' ) {
					allPerSubRowClicker.setStyle( 'fontWeight', 'normal' );
				}
				else {
					allPerSubRowClicker.setStyle( 'fontWeight', 'bold' );
				}
				
				// rowspan toggle the clicker row first cell
				if( allPerSubRowClicker.getFirst().getProperty( 'rowspan' ) == '1' ) {
					allPerSubRowClicker.getFirst().setProperty( 'rowspan', allPerRowSpans[i] );
				}
				else
				{
					allPerSubRowClicker.getFirst().setProperty( 'rowspan', '1' );
				}
				
				// toggle the visibility of the subrows
				allPerHideables[i].each( function(allPerSubRow) {
					if( allPerSubRow.getStyle('display') != 'none' ) {
						allPerSubRow.setStyle( 'display', 'none' );
					}
					else {
						allPerSubRow.setStyle( 'display', '' );
					}
				});
				
				return false;
			});
		});
	}
	
}