window.addEvent( 'domready', function() {
	
	// Subscription controls
	var channelSubUrl = $('channelSubUrl').getValue();
	initChannelControls();
	checkDelSubs();
	
	function initChannelControls()
	{
		derived = new Hash( Json.evaluate($('derived').getValue()) );
		derChannelIds = new Array();
		$('subscribed').getChildren().each( function(option) {
			if( option.getText().contains(' *derived*') ) {
				var origChannelName = option.getText();
				var derChannels = derived.get( origChannelName );
				derChannels.each( function(derChannel, index) {
					var tmp = derChannel.replace( /~/g, '' );
					tmp = tmp.charAt(0).toUpperCase() + tmp.substr(1).toLowerCase();
					derChannels[index] = tmp;
				});
				derChannels = derChannels.join(', ');
				option.setText( option.getText().replace(' *derived*', '') );
				option.setStyle( 'font-style', 'italic' );
				option.addClass( 'arcChanSubTip' );
				option.setProperty( 'title', 'This channel is subscribed to indirectly via ' + derChannels );
				derChannelIds.include( option.getProperty('value') );
			}
		});
		new Tips( $('subscribed').getElements('.arcChanSubTip'), {'className': 'custom'} );
		
		$('subscribed').addEvent( 'click', function() {
			var v = this.getValue();
			updateDetails( v.shift() );
			checkDelSubs();
		});
		
		$('global').addEvent( 'click', function() {
			var v = this.getValue();
			updateDetails( v.shift() );
		});
		
		$('public').addEvent( 'click', function() {
			var v = this.getValue();
			updateDetails( v.shift() );
		});
		
		$('private').addEvent( 'click', function() {
			var v = this.getValue();
			updateDetails( v.shift() );
		});
		
		$('addSub').addEvent( 'click', addSubs );
		$('delSub').addEvent( 'click', delSubs );
		if( $('rechannel') != null ) {
			$('rechannel').addEvent( 'click', refreshSubs );
		}
	}
	
	function checkDelSubs()
	{
		var disable = false;
		$('subscribed').getValue().each( function(cId) {
			if( derChannelIds.contains(cId) ) {
				disable = true;
			}
		});
		
		var delSubButton = $('delSub');
		if( disable ) {
			delSubButton.setProperty( 'disabled', 'disabled' );
		}
		else {
			delSubButton.removeProperty( 'disabled' );
		}
	}
	
	function refreshSubs()
	{
		var val1 = ( $('subscribers') == null ? '' : $('subscribers').getValue() );
		var val2 = ( $('subscriber_lists') == null ? '' : $('subscriber_lists').getValue() );
		new Ajax( channelSubUrl, {
			'method': 'post',
			'update': $('subs_def'),
			'data': 'task=reloadSubs'
				+'&subscribers='+Json.toString( val1 )
				+'&subscriber_lists='+Json.toString( val2 ),
			'onComplete': initChannelControls
		}).request();
	};
	
	function addSubs()
	{
		var vals = $('global').getValue().merge( $('public').getValue() ).merge( $('private').getValue() );
		new Ajax( channelSubUrl, {
			'method': 'post',
			'update': $('subs_def'),
			'data': 'task=addSubs'
				+'&channels='+Json.toString( vals ),
			'onComplete': initChannelControls
		}).request();
	};
	
	function delSubs()
	{
		var vals = $('subscribed').getValue();
		new Ajax( channelSubUrl, {
			'method': 'post',
			'update': $('subs_def'),
			'data': 'task=delSubs'
				+'&channels='+Json.toString( vals ),
			'onComplete': initChannelControls
		}).request();
	};
	
	// Channel definition controls
	var channelDefUrl = $('channelDefUrl').getValue();
	initDefinitionControls( false );
	
	function initDefinitionControls( doInputs )
	{
		if( !$defined(doInputs) ) { doInputs = true; }
		$('make_new').addEvent( 'click', function() {
			updateDetails( -1 );
		});
		
		if( doInputs ) {
			arcInitTree_group();
			ArcCombos.pop().trash();
			ArcCombos.push( new ArcCombo($('student')) );
		}
	}
	
	function updateDetails( id )
	{
		if( id != undefined ) {
			$('channel_def').setHTML( 'loading...' );
			new Ajax( channelDefUrl, {
				'method': 'post',
				'update': $('channel_def'),
				'data': 'task=loadChannel&channelId='+id,
				'onComplete': initDefinitionControls
			}).request();
		}
	};
});