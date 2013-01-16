/**
 * @package     Arc
 * @subpackage  **subpackage_name**
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

window.addEvent( 'domready', function() {
	var typeSelect = $('msg_inc_type');
	var formPartUrl = $('formPartUrl').getValue();
	
	if( typeSelect !== null ) {
		$('msg_sec3').setStyle( 'visibility', 'hidden' )
		typeSelect.addEvent( 'change', function() {
			new Ajax( formPartUrl.replace( '%7EformPart%7E', 'msg_sec2' ), {
				'method': 'post',
				'update': $('msg_sec2'),
				'data': 'data='+escape( Json.toString( {msg_inc_type: typeSelect.getValue()} ) ),
				'onComplete': function() {
					window.fireEvent( 'arcload' )
					window.removeEvents( 'arcload' );
				},
				'evalScripts': true
			}).request();
			
			if( typeSelect.getValue() == 0 ) {
				$('msg_sec3').setStyle( 'visibility', 'hidden' )
			}
			else {
				$('msg_sec3').setStyle( 'visibility', 'visible' )
			}
		});
	}
	
	if( $('now_or_date') != null ) {
		$('now_or_date').setStyle( 'display', 'inline' );
		$('msg_date[date]').disabled = true;
		$('msg_date[time]').disabled = true;
		$('msg_date[date]_img').setStyle( 'visibility', 'hidden' );
		
		$$('input[name=nowbox]').addEvent( 'click', function() {
			if( this.value=="now") {
				$('msg_date[date]').disabled = true;
				$('msg_date[time]').disabled = true;
				$('msg_date[date]_img').setStyle( 'visibility', 'hidden' );
			}
			else {
				$('msg_date[date]').disabled = false;
				$('msg_date[time]').disabled = false;
				$('msg_date[date]_img').setStyle( 'visibility', 'visible' );
			}
		});
	}
	
	// Group selection
	var grpSlider = new Fx.Slide( $('grp_list'), {
		'duration': 500,
		'onComplete': function() {
			if( this.wrapper.offsetHeight != 0 ) {
				this.wrapper.setStyle( 'height', 'auto' );
			}
		}
	});
	grpSlider.hide();
	
	$('change_grp').addEvent( 'click', function() {
		grpSlider.toggle();
	});
	
	
	//	monitor groups
	var ser = new PHP_Serializer();
	$( 'grp_list' ).addEvent( 'click', function() {
		var v = ser.unserialize( $('groups').getValue() );
		
		if( v.length == 0 ) {
			$('msg_data_group_id').value = '';
		}
		else {
			$('msg_data_group_id').value = v.pop();
		}
		
		new Ajax( formPartUrl.replace( '%7EformPart%7E', 'groupname' ), {
			'method': 'post',
			'update': $('group_name'),
			'data': 'data='+escape( Json.toString( {group_id: $('msg_data_group_id').getValue()} ) )
		}).request();
	});
	
});

function monitorGroups()
{
	if( $( 'groups' ) == null ) {
		monitorGroups.delay( 100 );
		console.log( 'wait' );
	}
	else {
		console.log( 'gottit' );
//		$('grp_list').addEvent( 'change', function() {
//			console.log( 'hello' );
//		})
		
		$('groups').addEvent( 'change', function() {
			console.log( Json.decode( this.getValue() ) );
		});
	}
	
}