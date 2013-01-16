<?php
/**
 * @package     Arc
 * @subpackage  Behaviour
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );


class ApothMessage_Behaviour
{
	function __construct()
	{
		$this->tmplDir = JPATH_SITE.DS.'components'.DS.'com_arc_behaviour'.DS.'helpers'.DS.'message'.DS;
		$this->styleDir = JURI::base().'components'.DS.'com_arc_behaviour'.DS.'helpers'.DS.'message'.DS;
		$this->scriptDir = JURI::base().'components'.DS.'com_arc_behaviour'.DS.'helpers'.DS.'message'.DS;
	}
	
	function renderThreadListHeader()
	{
		ob_start();
		require( $this->tmplDir.'list_header.php');
		return ob_get_clean();
	}
	
	function renderThreadListRow( &$thread )
	{
		$this->fMsg = ApothFactory::_( 'message.Message', $this->fMsg );
		$firstId = $thread->getFirstMessageId();
		$this->thread = &$thread;
		$this->message = &$this->fMsg->getInstance( $firstId );
		
		// Work out extra information for the message(s) we're displaying
		$this->count = $this->thread->getMessageCount();
		$this->color = $this->getColor( $this->message );
		$this->students = explode( ';', $this->message->getDatum( 'student_id' ) );
		if( !empty($this->students) && is_array($this->students) ) {
			foreach( $this->students as $k=>$pId ) {
				$s = ApotheosisLib::getUser( ApotheosisLib::getJUserId( $pId ) );
				$this->studentNames[$k] = ApotheosisLib::getPersonName( $pId, 'pupil' );
			}
		}
		
		$fInc = ApothFactory::_( 'behaviour.IncidentType' );
		$incId = $this->message->getDatum( 'incident' );
		if( empty($incId) ) {
			$this->inc = $fInc->getInstance( -1 );
			$this->incType = $fInc->getInstance( ApotheosisData::_( 'behaviour.colorIncident', $this->color ) );
		}
		else {
			$this->inc = $fInc->getInstance( $incId );
			$this->incType = $fInc->getInstance( $this->inc->getParentId() );
		}
		
		ob_start();
		require( $this->tmplDir.'list_row.php' );
		return ob_get_clean();
	}
	
	function renderThreadPdf( &$thread )
	{
		// Store thread object
		$this->thread = &$thread;
		
		// Show the messages
		$this->fMsg = ApothFactory::_( 'message.Message', $this->fMsg );
		$ids = $this->thread->getMessageIds();
		$msgCount = count( $ids );
		$this->first = true;
		
		ob_start();
		$this->last = false;
		$i = 0;
		foreach( $ids as $id ) {
			$this->message = &$this->fMsg->getInstance( $id );
			if( $this->first ) {
				// Setup info for the thread headline
				$this->color = $this->getColor( $this->message );
				$this->students = explode( ';', $this->message->getDatum('student_id') );
				if( !empty($this->students) && is_array($this->students) ) {
					foreach( $this->students as $k=>$pId ) {
						$this->studentNames[$k] = ApotheosisLib::getPersonName( $pId, 'pupil' );
					}
				}
			}
			$this->author = ApotheosisLib::getUser( ApotheosisLib::getJUserId( $this->message->getAuthor() ) );
			$this->authorName = ApotheosisLib::nameCase( 'teacher', $this->author->title, $this->author->firstname, $this->author->middlenames, $this->author->surname );
			
			// Get the incident info
			$fInc = ApothFactory::_( 'behaviour.IncidentType' );
			$incId = $this->message->getDatum( 'incident' );
			if( empty($incId) ) {
				$this->inc = &$fInc->getInstance( -1 );
				$this->incType = &$fInc->getInstance( ApotheosisData::_( 'behaviour.colorIncident', $this->color ) );
				$this->incident = $this->inc->getLabel();
			}
			else {
				$this->inc = &$fInc->getInstance( $incId );
				$this->incType = &$fInc->getInstance( $this->inc->getParentId() );
				$this->incident = $this->inc->getLabel();
			}
			
			// Check to see if this was the last message
			$this->last = ( (++$i == $msgCount) ? true : false );
			
			// Call the pseudo template
			require( $this->tmplDir.'pdf_thread.php' );
		}
		return ob_get_clean();
	}
	
	function renderMessageMiniRow( &$message )
	{
		return JHTML::_( 'arc.dotMini', $this->getColor($message), false );
	}
	
	function renderMessageTooltip( &$message )
	{
		$fInc = ApothFactory::_( 'behaviour.IncidentType' );
		$inc = $fInc->getinstance( $message->getDatum('incident') );
		
		return JHTML::_( 'arc.dot', $this->getColor($message), false ).$inc->getLabel();
	}
	
	function renderMessageForm( &$message, $form )
	{
		$this->message = &$message;
		// Work out extra information for the message(s) we're displaying
		$data = json_decode( urldecode(JRequest::getVar('data')), true );
		if( isset( $data['new'] ) && $data['new'] ) {
			$this->message->setDatum( 'student_id', ( isset($data['student_id']) ? $data['student_id'] : null ) );
			$this->message->setDatum( 'room_id',    ( isset($data['room_id']   ) ? $data['room_id']    : null ) );
			$data['new'] = false;
			JRequest::setVar( 'data', json_encode($data) );
		}
		if( isset( $data['group_id'] ) ) {
			$this->message->setDatum( 'group_id',   ( isset($data['group_id']  ) ? $data['group_id']   : null ) );
		}
		
		// get all the student names for display
		$this->students = explode( ';', $this->message->getDatum( 'student_id' ) );
		if( !empty($this->students) && is_array($this->students) ) {
			foreach( $this->students as $k=>$pId ) {
				$this->studentNames[$k] = ApotheosisLib::getPersonName( $pId, 'pupil' );
			}
		}
		
		// work out the incident id
		$fInc = ApothFactory::_( 'behaviour.IncidentType' );
		$inc = $this->message->getDatum( 'incident' );
		if( is_null($inc) ) {
			$this->inc = $fInc->getInstance( -2 );
			$this->incType = $fInc->getInstance( ( isset($data['msg_inc_type']) ? $data['msg_inc_type'] : null ) );
		}
		else {
			$this->inc = $fInc->getInstance( $inc );
			$this->incType = $fInc->getInstance( $this->inc->getParentId() );
		}
		
		// figure out which form we need to display
		$parts = explode( '.', $form );
		ob_start();
		switch( $parts[0] ) {
		case( 'start' ):
			if( $parts[1] == 'edit' ) {
				require( $this->tmplDir.'edit_message.php' );
			}
			else {
				require( $this->tmplDir.'message.php' );
			}
			break;
		
		case( 'part' ):
			switch( $parts[1] ) {
			case( 'groupname' ):
				require( $this->tmplDir.'edit_message_groupname.php' );
				break;
			
			case( 'msg_sec2' ):
				require( $this->_getIncTemplateName( $this->incType ) );
				break;
			}
			break;
		}
		return ob_get_clean();
	}
	
	function _getIncTemplateName( $incType )
	{
		switch( $incType->getLabel() ) {
		case( 'Gold' ):
		case( 'Green' ):
		case( 'Amber' ):
			$r = $this->tmplDir.'edit_message_a.php';
			break;
		
		case( 'Red' ):
		case( 'Purple' ):
			$r = $this->tmplDir.'edit_message_b.php';
			break;
		
		case( 'Clear' ):
			$r = $this->tmplDir.'edit_message_c.php';
			break;
		
		default:
			$r = null;
		}
		return $r;
	}
	
	function renderExtra( $message = null )
	{
		if( is_null($message) ) {
			$u = &ApotheosisLib::getUser();
			$pId = $u->person_id;
			$incDate = time();
		}
		else {
			$pId = $message->getDatum( 'student_id' );
			$incDate = strtotime( $message->getDate() );
		}
		$url = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_be_report_panel_highlighted', array('people.arc_people'=>$pId, 'sys.date'=>'sys.date') );
		$start = date( 'Y-m-d H:i:s', $incDate-604800 ); // 1 week before incident
		$end = date( 'Y-m-d H:i:s', $incDate+604800 ); // 1 week after incident
		$highlight = date( 'Y-m-d', $incDate ); // incident date as Y-m-d
		$url = str_replace( 'start=sys.date', 'start='.$start, $url);
		$url = str_replace( 'end=sys.date',   'end='.$end,     $url);
		$url = str_replace( 'highlightDate=sys.date',   'highlightDate='.$highlight, $url);
		
		ob_start();
		?>
		<script>
		function ajaxWait()
		{
			var target = $('extra_inner_<?php echo $this->thread->getId() ?>');
			if( target == null ) {
				ajaxWait.delay( 100 );
			}
			else {
				new Ajax( '<?php echo $url ?>', {
					'method': 'get',
					'update': target
				}).request();
			}
		}
		ajaxWait();
		</script>
		<?php
		echo JHTML::_( 'arc.loading' );
		
		return ob_get_clean();
	}
	
	/**
	 * Create a reply message with appropriate data copied over from the parent message
	 * @param $message
	 */
	function getReply( $message )
	{
		if( $message->getId() < 0 ) {
			return null;
		}
		$fMsg = ApothFactory::_( 'message.Message' );
		$fThread = ApothFactory::_( 'message.Thread' );
		$thread = &$fThread->getInstance( $message->getThreadId() );
		
		$dummy = $fMsg->getDummy( ($message->getId() * -1) );
		$dummy->setDetailsShown( true );
		
		// Set the new message to have all the same tags as its parent
		$dummy->setTags( $message->getTagIds(false), $message->getTagIds(true) );
		
		// Set the new message to have all the same key data
		$dummy->setDatum( 'student_id', $message->getDatum('student_id') );
		$dummy->setDatum( 'group_id', $message->getDatum('group_id') );
		$dummy->setDatum( 'incident', $message->getDatum('incident') );
		
		return $dummy;
	}
	
	/**
	 * Post-send event handler
	 * 
	 * @param unknown_type $message
	 */
	function eventAfterSend( $message )
	{
		$ok = true;
		if( $message->getDatum('callout') && ApotheosisData::_( 'message.tweetEnabled' ) ) {
			$fInc = ApothFactory::_( 'behaviour.IncidentType' );
			$inc = $fInc->getInstance( $message->getDatum('incident') );
			
			// Send it to twitter so key staff get immediate notification
			$msg = 'URGENT: '.ApotheosisLib::getPersonName( $message->getAuthor() ).' ('.date('Y-m-d H:i:s').') - '
				.ApotheosisData::_( 'course.name', $message->getDatum( 'group_id' ) ).' in '.$message->getDatum('room_id').' - '
				.ApotheosisLib::getPersonName( $message->getDatum( 'student_id' ) ).': '.$inc->getLabel();
				// Could include url for click-through?
				// http://fla90/j_dave_clone/index.php?option=com_arc_message&view=hub&scope=summary&tags=&Itemid=341				
			
			// send this to twitter so followers are notified
			$ok = ApotheosisData::_( 'message.tweet', $msg ) && $ok;
		}
		
		$ok = $this->setScore( $message ) && $ok;
		return $ok;

		/* **** create new message for parents/pupils with limited information
		 * Parent / pupils see..
		 * Gold: incident, score, comment
		 * Green: incident, score, comment
		 * Amber: incident, score2
		 * Red: incident, score, action
		 * Purple: incident, score
		 */
	}
	
	function getDataHash( $message )
	{
		return md5( $message->getCreated()
			.'~'.$message->getAuthor()
			.'~'.$message->getDatum('student_id') );
	}
	
	function getDataValid( $message )
	{
		$d1 = $message->getDatum('student_id');
		$d2 = $message->getDatum('group_id');
		$d3 = $message->getDatum('incident');
		return !empty( $d1 ) && !is_null( $d2 ) && !is_null( $d3 );
	}
	
	/**
	 * Set points in pupil's behaviour tracking
	 */
	function setScore( $message )
	{
		$prev = $message->getPreviousMessage();
		
		$score = false;
		if( is_null($prev) ) {
			if( ($incident = $message->getDatum( 'incident' )) == 0 ) {
				$incident = ApotheosisData::_( 'behaviour.colorIncident', $this->getColor( $message ) );
			}
			$score += (int)ApotheosisData::_( 'behaviour.incidentScore', $incident );
		}
		if( ($action = $message->getDatum( 'action' )) != 0 ) {
			$score += (int)ApotheosisData::_( 'behaviour.actionScore', $action, $message->getDatum('action_number') );
		}
		
		if( $score !== false ) {
			$ok = ApotheosisData::_( 'behaviour.addScore', $message->getDatum( 'student_id' ), $message->getDatum( 'group_id' ), $score, $message->getId() );
		}
		else {
			$ok = true;
		}
		return $ok;
	}
	
	function getColor( &$message )
	{
		$attribs = $message->getTagLabels( 'attribute' );
		if(     array_search('Gold',   $attribs) !== false ) { $color = 'gold';   }
		elseif( array_search('Green',  $attribs) !== false ) { $color = 'green';  }
		elseif( array_search('Amber',  $attribs) !== false ) { $color = 'amber';  }
		elseif( array_search('Red',    $attribs) !== false ) { $color = 'red';    }
		elseif( array_search('Purple', $attribs) !== false ) { $color = 'purple'; }
		elseif( array_search('Clear',  $attribs) !== false ) { $color = 'clear';  }
		return $color;
	}
}