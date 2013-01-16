<?php
/**
 * @package     Arc
 * @subpackage  Core
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Utility class for creating different select lists
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
class JHTMLGroups
{
	/**
	 * Generates the link which holds the information on the children of the given node
	 */
	function nodelink( $id = false, $actionId = null )
	{
		if( is_null($actionId) ) {
			$actionId = ApotheosisLib::getActionId();
		}
		
		if( $id === false ) {
			$retVal = ApotheosisLib::getActionLinkByName( 'apoth_sys_grouptree', array('core.action'=>$actionId) );
		}
		else {
			$retVal = ApotheosisLib::getActionLinkByName( 'apoth_sys_grouptree_node', array('core.action'=>$actionId, 'core.treenode'=>$id) );
		}
		
		return $retVal;
	}
	
	/**
	 * Build the group selection list for the given node.
	 * Outputs it as xml to be used in a node.load() call
	 * @param int $parent  The id of the node whose children are to be fetched
	 * @param boolean $onlyCurrent  Do we want to restrict this to only currently timetabled groups
	 */
	function grouplist( $parent, $actionId, $onlyCurrent = true )
	{
		$u = &ApotheosisLib::getUser();
		$db = &JFactory::getDBO();
		$date = date('Y-m-d H:i:s');
		
		// get all the children of the given node
		// ... first figure out all the courses that we want to consider
		// (id we're allowed to see them and (if specified) they're current)
		$query = 'CREATE TABLE ~TABLE~ ENGINE=MyISAM AS'
			."\n".'SELECT c.id, c.shortname, c.fullname, c.parent, gm.is_teacher' // *** titikaka fail (need to use new acl)
			."\n".'FROM #__apoth_cm_courses AS c'
			."\n".'INNER JOIN #__apoth_cm_courses_ancestry AS ca'
			."\n".'   ON ca.ancestor = c.id'
			."\n".'  AND c.deleted = 0'
			."\n".'~LIMITINGJOIN~'
			."\n".'LEFT JOIN #__apoth_tt_group_members AS gm'
			."\n".'  ON gm.group_id = c.id'
			."\n".' AND gm.person_id = '.$db->Quote($u->person_id)
			."\n".' AND '.ApotheosisLibDb::dateCheckSql( 'gm.valid_from',  'gm.valid_to',  $date, $date );
		if( $onlyCurrent ) {
			$query .= "\n".'LEFT JOIN #__apoth_tt_timetable AS t'
			."\n".'    ON t.course = c.id'
			."\n".'   AND '.ApotheosisLibDb::dateCheckSql( 't.valid_from', 't.valid_to', $date, $date )
			."\n".' WHERE ( (c.type != '.$db->Quote('normal').')'
			."\n".'         OR ((c.type = '.$db->Quote('normal').') AND (COALESCE(t.valid_from, 1) != 1))'
			."\n".'       )'
			."\n".'   AND '.ApotheosisLibDb::dateCheckSql( 'c.start_date', 'c.end_date', $date, $date );
		}
		else {
			$query .= "\n".' WHERE 1=1';
		}
		$query .= "\n".'   AND ((c.ext_type IS NULL) OR (c.ext_type != '.$db->Quote('course').'))'
			."\n".' GROUP BY c.id;'
			."\n"
			."\n".'ALTER TABLE ~TABLE~'
			."\n".' ADD PRIMARY KEY (`id`),'
			."\n".' ADD INDEX (`parent`);';
		$query = ApotheosisLibAcl::limitQuery( $query, 'timetable.groups', 'ca', false, false, $actionId );
		$tableName = ApotheosisLibDbTmp::initTable( $query, false, 'timetable', 'grouplist', false, $actionId );
		
		// ... then get the children (and a count of their children)
		$query = 'SELECT c.id, c.shortname, c.fullname, COUNT( ch.id ) AS children, c.is_teacher' // *** titikaka
			."\n".'FROM `'.$tableName.'` AS c'
			."\n".'LEFT JOIN `'.$tableName.'` AS ch'
			."\n".'  ON ch.parent = c.id'
			."\n".'WHERE c.parent = '.$db->Quote($parent)
			."\n".'  AND c.id != '.$db->Quote($parent) // prevent root from showing repeatedly
			."\n".'GROUP BY c.id'
			."\n".'ORDER BY c.fullname';
		$db->setQuery( $query );
		$results = $db->loadObjectList( 'id' );
		if( !is_array($results) ) { $results = array(); }
		
		return $results;
	}
	
	function grouptree( $id, $actionId = null, $multiple = true, $defaults = false, $partGroups = false )
	{
		$dataUrl = JHTML::_( 'groups.nodelink', false, $actionId );
		// Include mootools framework
		JHTMLBehavior::mootools();
		JHTML::script( 'mootree.js' );
		JHTML::stylesheet( 'mootree.css' );
		
		$document = &JFactory::getDocument();
		$params = array(
			'div'=>$id.'_div',
			'theme'=>JURI::root(true).'/components/com_arc_core/helpers/images/arc_mootree.gif',
			'loader'=> array(
				'icon'=>ApotheosisLib::arcLoadImgUrl(),
				'text'=>'Loading...',
				'color'=>'#a0a0a0'
			)
		);
		$root = array();
		static $trees;
		if ( !isset($trees) ) {
			$trees = array();
		}
		if ( isset($trees[$id]) && ($trees[$id]) ) {
			return;
		}
		
		// Setup options object
		$opt['div']    = ( array_key_exists('div',    $params) ) ? $params['div']       : $id.'_tree';
		$opt['mode']   = ( array_key_exists('mode',   $params) ) ? $params['mode']      : 'folders';
		$opt['grid']   = ( array_key_exists('grid',   $params) ) ? '\\'.$params['grid'] : '\\true';
		$opt['theme']  = ( array_key_exists('theme',  $params) ) ? $params['theme']     : null;
		$opt['loader'] = ( array_key_exists('loader', $params) ) ? $params['loader']    : null;
		
		// Event handlers
		if( $multiple ) {
			$opt['onClick']  = "\\function(node) { this.toggleNode( node, true, true ); }";
			$opt['onSelect'] = null;
			$opt['onExpand'] = "\\function(node, state) { if(state) { this.setDefaults(node); } }";
		}
		else {
			$opt['onClick']  = null;
			$opt['onSelect'] = "\\function(node, state) { this.toggleNode( node, true, false, state ); }";
			$opt['onExpand'] = null;
		}
		$options = JHTMLBehavior::_getJSObject( $opt );
		
		// Setup root node
		$rt['text'] =    ( array_key_exists('text',     $root) ) ? $root['text']      : 'Root';
		$rt['id']=       ( array_key_exists('id',       $root) ) ? $root['id']        : null;
		$rt['color']=    ( array_key_exists('color',    $root) ) ? $root['color']     : null;
		$rt['open']=     ( array_key_exists('open',     $root) ) ? '\\'.$root['open'] : '\\true';
		$rt['icon']=     ( array_key_exists('icon',     $root) ) ? $root['icon']      : null;
		$rt['openicon']= ( array_key_exists('openicon', $root) ) ? $root['openicon']  : null;
		$rt['data']    = ( array_key_exists('data',     $root) ) ? $root['data']      : null;
		$rootNode = JHTMLBehavior::_getJSObject( $rt );
		
		$treeName = ( array_key_exists('treeName', $params) ) ? $params['treeName'] : '';
		$initFuncName = 'arcInitTree_'.$id;
		
		$js = 'window.addEvent(\'domready\', '.$initFuncName.');'
			."\n".'function '.$initFuncName.'()'
			."\n\t".'{'
			."\n\t".'tree'.$treeName.' = new ArcMooTreeControl( '.$options.','.$rootNode.', $(\''.$id.'\'), $(\''.$id.'_unselect\'), $(\''.$id.'_partials\') );'
			."\n\t".'tree'.$treeName.'.adopt(\''.$id.'_list\');'
			."\n\t".'tree.root._load_err = function(req) {};' // suppress the error which comes up if you navigate away from the page before this has finished
			."\n\t".'tree.root.load(\''.$dataUrl.'\', \'\');'
			."\n\t".'tree.root.id = \''.ApotheosisLibDb::getRootItem('#__apoth_cm_courses').'\';'
			."\n\t".'tree.checkRootNode();' //  check if the root node is a default value
			."\n".'}';
		
		// Set static array
		$trees[$id] = true;
		
		$document->addScriptDeclaration($js);
		$document->addScript( JURI::root().'administrator'.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'js'.DS.'variable.php_serializer.js' );
		$document->addScript( JURI::root().'components'.DS.'com_arc_core'.DS.'helpers'.DS.'html'.DS.'arcmootree.js' );
		
		// After headers and javascript are set we now deal with creating the output
		
		// Get groups
		if( is_array($defaults) ) {
			$tmpGroups = array();
			foreach( $defaults as $v ) {
				$tmpGroups[] = (string)$v;
			}
			$value = serialize( $tmpGroups );
		}
		else {
			$tmpValues = unserialize( JRequest::getVar($id, '') );
			if( is_array($tmpValues) ) {
				foreach( $tmpValues as $k=>$tmpValue ) {
					$tmpValues[$k] = (string)$tmpValue;
				}
				$value = serialize( $tmpValues );
			}
			else {
				$value = '';
			}
		}
		
		// Get partials
		if( is_array($partGroups) ) {
			$tmpPartGroups = array();
			foreach( $partGroups as $v ) {
				$tmpPartGroups[] = (string)$v;
			}
			$partGroupsValue = serialize( $tmpPartGroups );
		}
		else {
			$tmpPartGroupsValues = unserialize( JRequest::getVar($id.'_partials', '') );
			if( is_array($tmpPartGroupsValues) ) {
				foreach( $tmpPartGroupsValues as $k=>$tmpPartGroupsValue ) {
					$tmpPartGroupsValues[$k] = (string)$tmpPartGroupsValue;
				}
				$partGroupsValue = serialize( $tmpPartGroupsValues ); 
			}
			else {
				$partGroupsValue = '';
			}
		}
		
		// Get partials if we have groups on first load
		if( ($value != '') && ($partGroupsValue == '') ) {
			$tmpGroups = unserialize( $value );
			$tmpPartGroups = array();
			foreach( $tmpGroups as $group ) {
				$groupAncs = ApotheosisLibDb::getAncestors( $group, '#__apoth_cm_courses' );
				$tmpPartGroups = array_merge( $tmpPartGroups, $groupAncs );
			}
			foreach( $tmpPartGroups as $k=>$groupObjs ) {
				$tmpPartGroups[$k] = (string)$groupObjs->id;
			}
			$tmpPartGroups = array_diff( array_unique($tmpPartGroups), $tmpGroups );
			$partGroupsValue = serialize( $tmpPartGroups );
		}
		
		ob_start();
		?>
		<div id="<?php echo $id; ?>_div_outer" class="arc_moo_tree_control">
			<div style="height:200px; width:16em; background: #FFF; border: solid 1px black; overflow: auto;">
				<div id="<?php echo $id; ?>_div"><ul id="<?php echo $id; ?>_list"></ul></div>
			</div>
			<div class="buttons">
				<input type="button" class="btn" id="<?php echo $id; ?>_unselect" value="Unselect All" />
				<input type="hidden" class="search_default" name="<?php echo $id; ?>_unselect" />
			</div>
			<input type="hidden" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo htmlspecialchars( $value ); ?>" />
			<input type="hidden" id="<?php echo $id; ?>_partials" name="<?php echo $id; ?>_partials" value="<?php echo htmlspecialchars( $partGroupsValue ); ?>" />
		</div>
		<?php
		return ob_get_clean();
	}
}
?>