/**
 * @package     Arc
 * @subpackage  Message
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

window.addEvent('domready', function() {
	var allNoneCheckBox = $('all_none');
	var checkBoxes = $$('.thread_checkbox');
	var checkBoxCount = checkBoxes.length;
	pdf = $('pdf_link');
	if( pdf != null ) {
		origPdfLink = pdf.getProperty('href');
		
		var btn = new Element( 'button', {
			'class':'btn',
			'id': 'button_pdf',
			'title': pdf.innerHTML,
			'events': {
				'click': function(e) { new Event( e ).stop(); location.href=pdf.getProperty( 'href' ); }
			}
		} );
		btn.innerHTML = pdf.innerHTML;
		
		btn.injectBefore( pdf );
		pdf.setStyle( 'display', 'none' );
	}
	
	checkBoxes.each( function(checkBox) {
		checkBox.addEvent( 'click', function() {
			allNoneCheckBox.checked = ( getChecked().length == checkBoxCount );
			updatePdfLink();
		});
	});
	
	if( allNoneCheckBox != null ) {
		allNoneCheckBox.addEvent( 'click', function() {
			checkBoxes.each( function(chk) {
				chk.checked = this.checked;
			}.bind( this ));
			updatePdfLink();
		});
	}
	
	$( 'controls' ).getElements( 'button' ).each( function(btn) {
		btn.addEvent( 'click', function(e) {
			$('thread_ids').value = Json.toString( getChecked() );
		});
	});
});

function updatePdfLink()
{
	escMsgIds = escape( getChecked() );
	var ext = ( escMsgIds ? '&threads=' + escMsgIds : '' );
	$('pdf_link').setProperty('href', origPdfLink + ext );
}

function getChecked()
{
	msgIds = new Array();
	$$('.thread_checkbox').each( function(chk) {
		if( chk.checked ) {
			parts = chk.name.match( /(.*\[)(.*)(\].*)/ );
			msgIds.include( parts[2] );
		}
	});
	
	return msgIds;
}