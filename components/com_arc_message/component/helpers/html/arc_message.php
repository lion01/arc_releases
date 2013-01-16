 <?php
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

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Utility class for generating arc message specific HTML entities
 *
 * @static
 * @package    Arc
 * @subpackage Message
 * @since      1.5
 */
class JHTMLArc_Message
{
	/**
	 * Render a message
	 * 
	 * @param unknown_type $method
	 * @param unknown_type $type
	 * @param unknown_type $id
	 * @param unknown_type $param
	 */
	function render( $method, $type, $id, $param = null )
	{
		return ApotheosisData::_( 'message.helperData', $method, $type, $id, $param );
	}
	
	/**
	 * List of all tags
	 * 
	 * @param string $name  The input name
	 * @param string $default  The default value
	 * @param boolean $multiple  Should multiple selections be allowed
	 */
	function tags( $name, $default = null, $multiple = false )
	{
		$default = ( is_null($default) ? '' : $default );
		$oldVal = JRequest::getVar($name, $default);
		
		$fTag = ApothFactory::_( 'message.Tag' );
		
		$r = $fTag->getInstances( array(), true, true );
		$r = $fTag->getStructure( array() );
		$opt = new stdClass();
		$opt->id = '';
		$opt->label = '';
		$options = array( $opt );
		
		foreach( $r as $info ) {
			$tmp = $fTag->getInstance( $info['id'] );
			$opt = new stdClass();
			$opt->id = $info['id'];
			$opt->label = str_repeat( '- ', $info['level'] ).$tmp->getLabel();
			$options[] = $opt;
		}
		
		$attribs = ($multiple ? 'multiple="multiple" class="multi_medium"' : '');
		$name = ($multiple ? $name.'[]' : $name);
		$retVal = JHTML::_('select.genericList', $options, $name, $attribs , 'id', 'label', $oldVal);
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class="search_default"' );
		return $retVal;
	}
	
	/**
	 * List of folders
	 * 
	 * @param string $name  The input name
	 * @param string $default  The default value
	 * @param boolean $multiple  Should multiple selections be allowed
	 */
	function folders( $name, $default = null, $multiple = false )
	{
		$default = ( is_null($default) ? '' : $default );
		$oldVal = JRequest::getVar($name, $default);
		
		$db = &JFactory::getDBO();
		$query = 'SELECT id'
			."\n".'FROM #__apoth_msg_tags'
			."\n".'WHERE id = parent'
			."\n".'  AND category = "folder"'
			."\n".'  AND enabled = 1'
			."\n".'LIMIT 1';
		$db->setQuery( $query );
		$rootId = $db->loadResult();
		
		$query = 'SELECT id, parent, label'
			."\n".'FROM #__apoth_msg_tags'
			."\n".'WHERE category = "folder"'
			."\n".'  AND enabled = 1';
		$db->setQuery( $query );
		$result = $db->loadAssocList( 'id' );
		if( !is_array($result) ) { $result = array(); };
		$options = array();
		
		$queue = array( $rootId=>0 );
		while( !empty($queue) ) {
			$indent = end( $queue );
			$parent = key( $queue );
			unset( $queue[$parent] );
			
			// put the current head of the queue into the folder list
			$o = new stdClass();
			$o->id = $parent;
			$o->label = str_repeat('- ', $indent).$result[$parent]['label'];
			$options[] = $o;
			
			$indent++;
			// queue up the current item's children
			foreach( $result as $entry ) {
				if( ($entry['parent'] == $parent) && ($entry['id'] != $parent) ) {
					$queue[$entry['id']] = $indent;
				}
			}
		}
		
		$attribs = ($multiple ? 'multiple="multiple" class="multi_medium"' : '');
		$name = ($multiple ? $name.'[]' : $name);
		$retVal = JHTML::_('select.genericList', $options, $name, $attribs , 'id', 'label', $oldVal);
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class="search_default"' );
		return $retVal;
	}

	function folderIcon ( $id, $text ) {
		$l = ApotheosisLib::getActionLinkByName( 'apoth_msg_hub', array('message.tags'=>$id, 'message.scopes'=>'list') );

		if( $l ) {	
			$r = '<a href="'.$l.'" ><span class="'.strtolower($text).'">'.$text.'</span></a><br />';
		}
		return $r;
	}
	
	
	/**
	 * List of privacy levels for use setting up a channel
	 * 
	 * @param string $name  The input name
	 * @param string $default  The default value
	 * @param boolean $multiple  Should multiple selections be allowed
	 */
	function privacyLevels( $name, $default = null, $multiple = false )
	{
		$default = ( is_null($default) ? '' : $default );
		$oldVal = JRequest::getVar($name, $default);
		
		$r = array();
		if(  ApotheosisLibAcl::getUserLinkAllowed( 'apoth_msg_channel_restricted' ) !== false ) {
			$r[] = array('id'=>0, 'label'=>'Global');
		}
		$r[] = array('id'=>1, 'label'=>'Public');
		$r[] = array('id'=>2, 'label'=>'Private');
				
		$options = array();
		foreach( $r as $row ) {
			$o = new stdClass();
			$o->id = $row['id'];
			$o->label = $row['label'];
			$options[] = $o;
		}
		
		$attribs = ($multiple ? 'multiple="multiple" class="multi_medium"' : '');
		$name = ($multiple ? $name.'[]' : $name);
		$retVal = JHTML::_('select.genericList', $options, $name, $attribs , 'id', 'label', $oldVal);
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class="search_default"' );
		return $retVal;
	}
	
	function methods( $name, $default = null, $multiple = false )
	{
		$default = ( is_null($default) ? '' : $default );
		$oldVal = JRequest::getVar($name, $default);
		
		$r = array(
			array('id'=>'', 'label'=>''),
			array('id'=>'send', 'label'=>'Send'),
			array('id'=>'draft', 'label'=>'Save draft')
			);
				
		$options = array();
		foreach( $r as $row ) {
			$o = new stdClass();
			$o->id = $row['id'];
			$o->label = $row['label'];
			$options[] = $o;
		}
		
		$attribs = ($multiple ? 'multiple="multiple" class="multi_medium"' : '');
		$name = ($multiple ? $name.'[]' : $name);
		$retVal = JHTML::_('select.genericList', $options, $name, $attribs , 'id', 'label', $oldVal);
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class="search_default"' );
		return $retVal;
	}
	
	function isFirst( $name, $default = null, $multiple = false )
	{
		$default = ( is_null($default) ? '' : $default );
		$oldVal = JRequest::getVar($name, $default);
		
		$r = array(
			array('id'=>'',  'label'=>''),
			array('id'=>'1', 'label'=>'First'),
			array('id'=>'2', 'label'=>'Not first')
			);
				
		$options = array();
		foreach( $r as $row ) {
			$o = new stdClass();
			$o->id = $row['id'];
			$o->label = $row['label'];
			$options[] = $o;
		}
		
		$attribs = ($multiple ? 'multiple="multiple" class="multi_medium"' : '');
		$name = ($multiple ? $name.'[]' : $name);
		$retVal = JHTML::_('select.genericList', $options, $name, $attribs , 'id', 'label', $oldVal);
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class="search_default"' );
		return $retVal;
	}
	
	function times( $name, $default = null, $multiple = false )
	{
		$default = ( is_null($default) ? '' : $default );
		$oldVal = JRequest::getVar($name, $default);
		
		$r = array(
			array('id'=>'',         'label'=>''),
			array('id'=>'lesson',   'label'=>'Lesson'),
			array('id'=>'tutor',    'label'=>'Tutor'),
			array('id'=>'untaught', 'label'=>'Untaught')
			);
				
		$options = array();
		foreach( $r as $row ) {
			$o = new stdClass();
			$o->id = $row['id'];
			$o->label = $row['label'];
			$options[] = $o;
		}
		
		$attribs = ($multiple ? 'multiple="multiple" class="multi_medium"' : '');
		$name = ($multiple ? $name.'[]' : $name);
		$retVal = JHTML::_('select.genericList', $options, $name, $attribs , 'id', 'label', $oldVal);
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class="search_default"' );
		return $retVal;
	}
}
?>