<?php
/**
 * @package     Arc
 * @subpackage  Report
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( JPATH_COMPONENT.DS.'models'.DS.'extension.php' );

/**
 * Reports Admin Model
 * Loads data when group is set
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Reports
 * @since 0.1
 */
class ReportsModelAdmin extends ReportsModel
{
	var $_instances;
	var $_current;
	var $_config;
	
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		$this->_config = $config;
		$this->_instances = array();
		$this->_current = NULL;
		$this->setGroup( ApotheosisLibDb::getRootItem( '#__apoth_cm_courses' ) );
	}
	
	/**
	 * Switches between admin instances, and creates them if necessary
	 *
	 * @param string $g  The group id (string or int)
	 * @return string  The id that has now been set
	 */
	function setGroup( $g )
	{
		if( !is_int( $g ) && !is_string( $g ) ) {
			$g = '';
		}
		if( !array_key_exists($g, $this->_instances) ) {
			$this->_instances[$g] = new ReportsModelAdminInner( $this->_config );
			$this->_instances[$g]->setGroup( $g );
		}
		$this->_current = &$this->_instances[$g];
		return $this->_current->getGroup();
	}
	
	function getLink()
	{
		global $Itemid;
		return 'index.php?Itemid='.$Itemid.'&option=com_arc_report&view=admin&focus='.$this->_current->getGroup();
	}
	
	function getIncFiles()
	{
		$incFiles = array();
		foreach( $this->_instances as $group=>$tmpModel ) {
			$incFiles = array_merge( $incFiles, $tmpModel->getIncFiles() );
		}
		return $incFiles;
	}
	
	/* *** This would work in php5, but we are making this php4-able, so can't use.
	 * instead, must re-define every public function of ReportsModelAdminInner
	 * so we can call 'em from here.
	 * ... suck.
	function __call( $m, $a )
	{
		echo 'calling child method: '.$m.'<br />';
		return ( is_null($this->_current)
			? NULL
			: eval( '$this->_current->'.$m.'( '.implode(', ', $a).' );' ) );
	}
	 */
	
	// ##### These functions need to return by reference #####
	
	function &getFields()
	{
		return $this->_current->getFields();
	}
	
	function &getReport()
	{
		return $this->_current->getReport();
	}
	
	// ##### Re-decleration of all ReportsModelAdminInner functions so wrapping is transparent
	function getAdminCandidates() { return $this->_current->getAdminCandidates( ); }
	function getAdmins()          { return $this->_current->getAdmins(          ); }
	function getPeerCandidates()  { return $this->_current->getPeerCandidates(  ); }
	function getPeers()           { return $this->_current->getPeers(           ); }
	function getEnabled()         { return $this->_current->getEnabled(         ); }
	function getGroup()           { return $this->_current->getGroup(           ); }
	function getGroupName()       { return $this->_current->getGroupName(       ); }
	function getMarkStyle()       { return $this->_current->getMarkStyle(       ); }
	function getMarkStyles()      { return $this->_current->getMarkStyles(      ); }
	function getPageStyle()       { return $this->_current->getPageStyle(       ); }
	function getPageStyles()      { return $this->_current->getPageStyles(      ); }
	function getTwin()            { return $this->_current->getTwin(            ); }
	function getSettings()        { return $this->_current->getSettings(        ); }
	function setFields( &$f )     { return $this->_current->setFields( $f ); }
	function setAdmins()          { $tmp = func_get_args(); if( !empty($tmp) ) { foreach($tmp as $k=>$v) { $params[$k] = '$tmp['.$k.']'; } } else { $params = array(); } eval('$retVal = $this->_current->setAdmins(         '.implode(', ', $params).');'); return $retVal; }
	function addUsers()           { $tmp = func_get_args(); if( !empty($tmp) ) { foreach($tmp as $k=>$v) { $params[$k] = '$tmp['.$k.']'; } } else { $params = array(); } eval('$retVal = $this->_current->addUsers(          '.implode(', ', $params).');'); return $retVal; }
	function removeUsers()        { $tmp = func_get_args(); if( !empty($tmp) ) { foreach($tmp as $k=>$v) { $params[$k] = '$tmp['.$k.']'; } } else { $params = array(); } eval('$retVal = $this->_current->removeUsers(       '.implode(', ', $params).');'); return $retVal; }
	function setPrintName()       { $tmp = func_get_args(); if( !empty($tmp) ) { foreach($tmp as $k=>$v) { $params[$k] = '$tmp['.$k.']'; } } else { $params = array(); } eval('$retVal = $this->_current->setPrintName(      '.implode(', ', $params).');'); return $retVal; }
	function setBlurbs()          { $tmp = func_get_args(); if( !empty($tmp) ) { foreach($tmp as $k=>$v) { $params[$k] = '$tmp['.$k.']'; } } else { $params = array(); } eval('$retVal = $this->_current->setBlurbs(         '.implode(', ', $params).');'); return $retVal; }
	function setEnabled()         { $tmp = func_get_args(); if( !empty($tmp) ) { foreach($tmp as $k=>$v) { $params[$k] = '$tmp['.$k.']'; } } else { $params = array(); } eval('$retVal = $this->_current->setEnabled(        '.implode(', ', $params).');'); return $retVal; }
	function setFieldStyle()      { $tmp = func_get_args(); if( !empty($tmp) ) { foreach($tmp as $k=>$v) { $params[$k] = '$tmp['.$k.']'; } } else { $params = array(); } eval('$retVal = $this->_current->setFieldStyle(     '.implode(', ', $params).');'); return $retVal; }
	function setMarkStyle()       { $tmp = func_get_args(); if( !empty($tmp) ) { foreach($tmp as $k=>$v) { $params[$k] = '$tmp['.$k.']'; } } else { $params = array(); } eval('$retVal = $this->_current->setMarkStyle(      '.implode(', ', $params).');'); return $retVal; }
	function setPageStyle()       { $tmp = func_get_args(); if( !empty($tmp) ) { foreach($tmp as $k=>$v) { $params[$k] = '$tmp['.$k.']'; } } else { $params = array(); } eval('$retVal = $this->_current->setPageStyle(      '.implode(', ', $params).');'); return $retVal; }
	function setTwin()            { $tmp = func_get_args(); if( !empty($tmp) ) { foreach($tmp as $k=>$v) { $params[$k] = '$tmp['.$k.']'; } } else { $params = array(); } eval('$retVal = $this->_current->setTwin(           '.implode(', ', $params).');'); return $retVal; }
	function setStatement()       { $tmp = func_get_args(); if( !empty($tmp) ) { foreach($tmp as $k=>$v) { $params[$k] = '$tmp['.$k.']'; } } else { $params = array(); } eval('$retVal = $this->_current->setStatement(      '.implode(', ', $params).');'); return $retVal; }
	function updateReports()      { $tmp = func_get_args(); if( !empty($tmp) ) { foreach($tmp as $k=>$v) { $params[$k] = '$tmp['.$k.']'; } } else { $params = array(); } eval('$retVal = $this->_current->updateReports(     '.implode(', ', $params).');'); return $retVal; }
}



class ReportsModelAdminInner extends ReportsModel
{
	var $_group;
	var $_groupName;
	var $_settings;
	var $_admins = false;
	var $_adminCandidates;
	var $_peers = false;
	var $_peerCandidates;
	var $_pageStyles;
	var $_markStyles;
	var $_report;
	
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		$this->_group = false;
		$this->_groupName = '';
		$this->_markStyles = array(
			'bands'=>'Distinction, Merit, etc',
			'levels'=>'8.8, 8.5, 8.2, 7.8, ... 0.2',
			'grades'=>'A*, A, ... G, U');
	}
	
	
	function &getReport()
	{
		return $this->_report;
	}
	
	function getSettings()
	{
		if (!isset($this->_settings)) {
			$this->_loadSettings();
		}
		return $this->_settings;
	}
	
	function _loadSettings()
	{
		$db = &JFactory::getDBO();
		$qStr = 'SELECT *'
			."\n".' FROM #__apoth_rpt_style'
			."\n".'	WHERE '.$db->nameQuote('group').' = '.$db->quote($this->_group)
			."\n".'   AND '.ApotheosisLibDb::dateCheckSql( 'valid_from', 'valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s') )
			."\n".'   AND `cycle` = '.$db->Quote($this->getCycleId())
			."\n".' LIMIT 1';
		$db->setQuery( $qStr );
		$this->_settings = $db->loadObject();
	}
	
	function _saveSettings()
	{
		$db = &JFactory::getDBO();
		$settings = get_object_vars($this->_settings);
		$assignments = array();
		foreach ($settings as $k=>$v) {
			$assignments[$k] = $db->nameQuote($k).' = '.(is_null($v) ? 'NULL' : $db->Quote($v));
		}
		
		// check to see if the group already has settings in the db
		$qStr = 'SELECT *'
			."\n".' FROM #__apoth_rpt_style'
			."\n".'	WHERE '.$db->nameQuote('group').' = '.$db->quote($this->_group)
			."\n".'   AND '.ApotheosisLibDb::dateCheckSql( 'valid_from', 'valid_to', $this->getCycleStart(), $this->getCycleEnd() )
			."\n".'   AND `cycle` = '.$db->Quote($this->getCycleId());
		$db->setQuery( $qStr );
		$exists = $db->loadObjectList('group');
		
		if( empty($exists) ) {
			$assignments['group'] = $db->nameQuote('group').' = '.$db->Quote($this->_group);
			$assignments['cycle'] = $db->nameQuote('cycle').' = '.$db->Quote($this->getCycleId());
			$assignments['valid_from'] = $db->nameQuote('valid_from').'='.$db->quote(date('Y-m-d H:i:s'));
			$aStr = implode(', ', $assignments);
			$qStr = 'INSERT INTO #__apoth_rpt_style'
				."\n".' SET '.$aStr;
		}
		else {
			$aStr = implode(', ', $assignments);
			$qStr = 'UPDATE #__apoth_rpt_style'
				."\n".' SET '.$aStr
				."\n".' WHERE `group` = '.$db->Quote($this->_group)
				."\n".'   AND `cycle` = '.$db->Quote($this->getCycleId());
		}
		$db->setQuery( $qStr );
		$db->query();
		
		$this->_loadSettings();
		$this->_report = &ApothReport::newInstance( 0, $this->_group, $this->getCycleId() );
	}
	
	/**
	 * Accessor method to the model's group
	 *
	 * @return string  The id of the group for this model
	 */
	function getGroup()
	{
		return $this->_group;
	}
	
	function getGroupName()
	{
		return $this->_groupName;
	}
	
	/**
	 * Sets the group for this model and reloads the other data to match the new group
	 *
	 * @param string $g  The group id (string or int)
	 * @return string  The id that has now been set
	 */
	function setGroup( $g )
	{
		$this->_group = $g;
		$db = JFactory::getDBO();
		$query = 'SELECT fullname'
			."\n".' FROM #__apoth_cm_courses'
			."\n".' WHERE '.$db->nameQuote('id').' = '.$db->Quote($g);
		$db->setQuery( $query );
		$this->_groupName = $db->loadResult();
		$this->_loadSettings();
		$this->_loadAdmins();
		// create a new fake report for the correct group
		$this->_report = &ApothReport::newInstance( 0, $this->_group, $this->getCycleId() );
		return $this->_group;
	}
	
	function getTwin()
	{
		if( !isset($this->_settings) ) {
			$this->_loadSettings();
		}
		return $this->_settings->twin;
	}
	
	function setTwin( $pId = NULL )
	{
		$this->_settings->twin = $pId;
		$this->_saveSettings();
		return true;
	}
	
	function getEnabled()
	{
		return $this->_enabled;
	}
	
	function setEnabled( $val )
	{
		$this->_enabled = $val;
	}
	
	/**
	 * Retrieves the admins for the current course
	 *
	 * @return array  The person_id indexed array of data objects giving group, person, and valid data
	 */
	function getAdmins()
	{
		if ( $this->_admins === false ) {
			$this->_loadAdmins();
		}
		return $this->_admins;
	}
	
	/**
	 * Loads the admins for the current course from the database into this->_admins
	 * Only pulls out currently valid admins, and orders by valid_from
	 */
	function _loadAdmins()
	{
		$db = &JFactory::getDBO();
		$qStr = 'SELECT person'
			."\n".' FROM #__apoth_rpt_admins'
			."\n".'	WHERE '.$db->nameQuote('group').' = '.$db->Quote($this->_group)
			."\n".'   AND '.$db->nameQuote('cycle').' = '.$db->Quote($this->getCycleId())
			."\n".'   AND '.ApotheosisLibDb::dateCheckSql( 'valid_from', 'valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s') )
			."\n".' ORDER BY '.$db->nameQuote('valid_from').' ASC';
		$db->setQuery( $qStr );
		$tmp = $db->loadObjectList('person');
		if( is_array($tmp) && !empty($tmp) ) {
			foreach($tmp as $k=>$v) {
				$tmp[$k] = $db->Quote($v->person);
			}
			$this->_admins = ApotheosisLib::getUserList( 'WHERE p.id IN ('.implode(', ', $tmp).')', true, 'teacher');
		}
		else {
			$this->_admins = array();
		}
	}
	
	function getAdminCandidates()
	{
		if (!isset($this->_adminCandidates)) {
			$this->_loadAdminCandidates();
		}
		return $this->_adminCandidates;
	}
	
	function _loadAdminCandidates()
	{
		$db = &JFactory::getDBO();
		
		$whereStr = 'id IN ('.$this->getCycleGroupsList().')';
		$groupList = ApotheosisLibDb::getDescendants( $this->_group, '#__apoth_cm_courses', 'id', 'parent', $whereStr );
		$groups = '"'.implode('", "', array_keys($groupList)).'"';
		
		$exclude = $this->getAdmins();
		$safeAdmins = array();
		foreach($exclude as $e) {
			$safeAdmins[] = $db->Quote($e->id);
		}
		
		$this->_adminCandidates = ApotheosisLib::getUserList(
			'WHERE (gm.is_teacher = 1 OR gm.is_admin)' // *** titikaka (is this even right??)
			."\n".' AND gm.group_id IN ('.$groups.')'
			."\n".' AND '.ApotheosisLibDb::dateCheckSql( 'gm.valid_from', 'gm.valid_to', $this->_cycle->valid_from, $this->_cycle->valid_to)
			.(empty($safeAdmins) ? '' : "\n".' AND p.id NOT IN ('.implode(', ', $safeAdmins).')'), true, 'teacher' );
	}
	
	function getPeers()
	{
//		echo 'getting peers: ';var_dump_pre($this->_peers);
		if ( $this->_peers === false ) {
			$this->_loadPeers();
		}
		return $this->_peers;
	}
	
	function _loadPeers()
	{
		$db = &JFactory::getDBO();
		$qStr = 'SELECT person'
			."\n".' FROM #__apoth_rpt_peers'
			."\n".'	WHERE '.$db->nameQuote('group').' = '.$db->Quote($this->_group)
			."\n".'   AND '.$db->nameQuote('cycle').' = '.$db->Quote($this->getCycleId())
			."\n".'   AND '.ApotheosisLibDb::dateCheckSql( 'valid_from', 'valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s') )
			."\n".' ORDER BY '.$db->nameQuote('valid_from').' ASC';
		$db->setQuery( $qStr );
		$tmp = $db->loadObjectList('person');
		if( is_array($tmp) && !empty($tmp) ) {
			foreach($tmp as $k=>$v) {
				$tmp[$k] = $db->Quote($v->person);
			}
			$this->_peers = ApotheosisLib::getUserList( 'WHERE p.id IN ('.implode(', ', $tmp).')', true, 'teacher');
		}
		else {
			$this->_peers = array();
		}
	}
	
	function getPeerCandidates()
	{
		if (!isset($this->_peerCandidates)) {
			$this->_loadPeerCandidates();
		}
		return $this->_peerCandidates;
	}
	
	function _loadPeerCandidates()
	{
		$db = &JFactory::getDBO();
		$exclude = $this->getPeers();
		$safePeers = array();
		foreach($exclude as $e) {
			$safePeers[] = $db->Quote($e->id);
		}
		
		$this->_peerCandidates = ApotheosisLib::getUserList(
			'WHERE (gm.is_teacher = 1 OR gm.is_admin = 1)' // *** titikaka
			."\n".' AND '.ApotheosisLibDb::dateCheckSql( 'valid_from', 'valid_to', $this->_cycle->valid_from, $this->_cycle->valid_to )
			.(empty($safePeers) ? '' : "\n".' AND p.id NOT IN ('.implode(', ', $safePeers).')'), true, 'teacher' );
	}
	
	function addUsers( $role, $new )
	{
		$retVal = array( 'deleted'=>0, 'added'=>0, 'errors'=>0 );
		
		if( is_array($new) && !empty($new) ) {
			$db = &JFactory::getDBO();
			$now = date('Y-m-d H:i:s');
			
			foreach($new as $k=>$id) {
				$inserts[] = '('.$db->Quote($this->getCycleId()).', '.$db->Quote($this->_group).', '.$db->Quote($id).', '.$db->Quote($now).' )';
			}
			// add the new admin to the db and this object's list of current admins
			$qStr = 'INSERT IGNORE INTO #__apoth_rpt_'.$role.'s'
				."\n".' (`cycle`, `group`, `person`, `valid_from`)'
				."\n".' VALUES'
				."\n".' '.implode( ",\n ", $inserts );
			$db->setQuery( $qStr );
			if ($db->query() === false) {
				$retVal['errors'] = count($new);
			}
			else {
				$done = $db->getAffectedRows();
				$retVal['errors'] = ( count($new) - $done );
				$retVal['added'] = $done;
			}
			switch($role) {
			case('admin'):
				$this->_loadAdmins();
				break;
			
			case('peer'):
				$this->_loadPeers();
				break;
			}
		}
		
		return $retVal;
	}
	
	function removeUsers( $role, $old )
	{
		$retVal = array( 'deleted'=>0, 'added'=>0, 'errors'=>0 );
		
		if( is_array($old) && !empty($old) ) {
			$db = &JFactory::getDBO();
			$now = date('Y-m-d H:i:s');
			
			// set the valid_to date to now for any user listed for removal
			$r = '_'.$role.'s';
			foreach($old as $k=>$v) {
				unset($this->{$r}[$v]);
				$old[$k] = $db->Quote($v);
			}
			$qStr = 'UPDATE #__apoth_rpt_'.$role.'s'
				."\n".' SET valid_to = "'.$now.'"'
				."\n".' WHERE `cycle` = '.$db->Quote($this->getCycleId())
				."\n".'   AND `group` = '.$db->Quote($this->_group)
				."\n".'   AND `person` IN ('.implode(', ', $old).')'
				."\n".'   AND `valid_to` IS NULL';
			$db->setQuery( $qStr );
			if ($db->query() === false) {
				$retVal['errors'] = count($old);
			}
			else {
				$done = $db->getAffectedRows();
				$retVal['errors'] = ( count($old) - $done );
				$retVal['deleted'] = $done;
			}
		}
		
		return $retVal;
	}
	
	function getPageStyle()
	{
		if( !isset($this->_settings) ) {
			$this->_loadSettings();
		}
		return $this->_settings->page_style;
	}
	
	function setPageStyle( $style )
	{
		if(array_search($style, $this->_pageStyles) === false) {
			$style = NULL;
		}
		$this->_settings->page_style = $style;
		$this->_saveSettings();
	}
	
	
	function getPageStyles()
	{
		if (!isset($this->_pageStyles)) {
			$this->_loadPageStyles();
		}
		return $this->_pageStyles;
	}
	
	function _loadPageStyles()
	{
		$list = array();
		$dirName = JPATH_SITE.DS.'components'.DS.'com_arc_report'.DS.'pagelayouts';
		if ($dir = opendir( $dirName )) {
			while (false !== ($file = readdir($dir))) {
				if( is_file($dirName.DS.$file) && (substr($file, -4) == '.php') ) {
					$list[] = substr($file, 0, -4);
				}
			}
			closedir($dir);
		}
		else {
			echo 'There are no page styles defined. Contact a system administrator';
		}
		asort($list);
		$this->_pageStyles = $list;
	}
	
	function getMarkStyle()
	{
		if( !isset($this->_settings) ) {
			$this->_loadSettings();
		}
		return $this->_settings->mark_style;
	}
	
	function setMarkStyle( $style )
	{
		if ( !array_search($style, array_keys($this->_markStyles)) ) {
			$style = NULL;
		}
		$this->_settings->mark_style = $style;
		$this->_saveSettings();
	}
	
	function getMarkStyles()
	{
		return $this->_markStyles;
	}
	
	function setFieldStyle()
	{
		$this->_report->setFieldStyle();
	}
	
	function &getFields()
	{
		return $this->_fields;
	}
	
	function setFields( &$fields )
	{
		$this->_fields = &$fields;
	}
	
	/**
	 * @param $explicitSave boolean  Do we want to save this change? Defaults to false as this func is called just before setBlurbs.
	 */
	function setPrintName( $new, $explicitSave = false )
	{
		$tmp = $this->_report->getSubjectField();
		$old = $tmp->getValue();
		if( $old != $new ) {
			$this->_settings->print_name = $new;
		}
		if( $new == '' ) {
			$this->_settings->print_name = NULL;
		}
	}
	
	function setBlurbs( $blurbs )
	{
		foreach( $blurbs as $col=>$val ) {
			if( $val == false ) {
				$val = NULL;
			}
			$this->_settings->$col = $val;
		}
		$this->_saveSettings();
	}
	
	/**
	 * Updates all reports that use the old text of a statement (within the specified group)
	 * to use the new text of the statement. Acts only in this model's cycle
	 *
	 * @param $old string  The old text of the statement
	 * @param $new string  The new text of the statement
	 * @param $fieldName string  The name of the field in which this statement is used (and to be updated)
	 * @param $group string  The group id in which to perform the updates
	 */
	function updateReports( $old, $new, $fieldName, $group )
	{
		$report = &ApothReport::newInstance( 0, $this->_group, $this->getCycleId() );
		$field = &$report->getField($fieldName);
		$col = $field->getColumn();
		$db = &JFactory::getDBO();
		$search    = $old;
		$replace   = $new;
		
		// First, we find the merge fields in the old and new text
		// - this pattern defines how any merge fields are represented
		$pStart = preg_quote($this->_mergeStart);
		$pEnd   = preg_quote($this->_mergeEnd);
		$pattern = '~(?<='.$pStart.').+?(?='.$pEnd.')~';
		
		// get rid of regex special characters (except for those in merge fields),
		$searchSql = '';
		$pattern2 = '~('.$pStart.'.+?'.$pEnd.')~';
		$parts = preg_split( $pattern2, $search, -1, PREG_SPLIT_DELIM_CAPTURE );
		foreach($parts as $k=>$v) {
			$searchSql .= ( (preg_match( $pattern2, $v ) || ($v == '')) ? $v : preg_quote($v) );
		}
		$search = $searchSql;
		
		// - find merge fields
		$mergesSearch  = array(); // merge fields in the search
		$mergesReplace = array(); // merge fields in the replace
		preg_match_all($pattern, $search,  $mergesSearch);
		preg_match_all($pattern, $replace, $mergesReplace);
		$mergesSearch  = reset($mergesSearch);
		$mergesReplace = reset($mergesReplace);
		// - set up matches and references, along with the regex for the sql query
		//   This means that old merged values will be preserved in the new text
		foreach( $mergesSearch as $index=>$str ) {
			$strFull   = $this->_mergeStart.$str.$this->_mergeEnd;
			$strRep    = $this->mergeWordAsRegex( $str );
			$strRepSql = $this->mergeWordAsRegex( $str, false ); // mysql can't use the non-greedy '?' operator. suck.
			
			$searchSql = str_replace( $strFull, $strRepSql, $searchSql );
			if( array_search($str, $mergesReplace) !== false ) {
				$search  = str_replace( $strFull, $strRep, $search );
				$replace = str_replace( $strFull, '${'.($index+1).'}', $replace );
			}
		}
		
		// find children of given group
		$desc = ApotheosisLibDb::getDescendants( $group, '#__apoth_cm_courses', 'id', 'parent');
		$ids = array();
		foreach( $desc as $row ) {
			$ids[] = $db->quote( $row->id );
		}
		
		// find reports in the relevant group(s), with matching text, in the correct cycle
		$db = &JFactory::getDBO();
		$qStr = 'SELECT *'
			."\n".' FROM #__apoth_rpt_reports'
			."\n".' WHERE '.$db->nameQuote( 'group' ).' IN ('.implode(', ', $ids).')'
			."\n".'   AND '.$db->namequote( 'cycle' ).' = '.$db->Quote( $this->getCycleId() )
			."\n".'   AND '.$db->nameQuote( $col ).' REGEXP '.$db->Quote( $searchSql )
			."\n".' ORDER BY '.$db->nameQuote( 'group' );
		$db->setQuery( $qStr );
		$candidates = $db->loadObjectList();
		if( $candidates === false ) { $candidates = array(); }
		
		// go through each report and replace text as appropriate.
		$counter = 0;
		foreach( $candidates as $key=>$cur ) {
			// insert any remaining merge fields into the $new string we're about to replace the $old string with
			$this->setMergeDetails( $cur->student, $cur->group );
			$s = $this->mergeText( $search, true );
			$r = $this->mergeText( $replace );
			
			// do the replacement on the specified column,
			// and count up how many rows are different after the change.
			if( $cur->$col != ($cur->$col = preg_replace( '~'.$s.'~', $r, $cur->$col )) ) {
				$counter++;
				$qStr = 'UPDATE #__apoth_rpt_reports'
					."\n".' SET '.$db->nameQuote( $col ).' = '.$db->quote( $cur->$col )
					."\n".' WHERE '.$db->nameQuote( 'id' ).' = '.$db->quote( $cur->id );
				$db->setQuery( $qStr );
				$db->query();
			}
		}
		return $counter;
	}
	
	/**
	 * Retrieve the incFiles array
	 */
	function getIncFiles()
	{
		$incFiles = array();
		if( is_object($this->_report) ) {
			$incFiles[] = $this->_report->getFile();
		}
		return $incFiles;
	}
}
?>
