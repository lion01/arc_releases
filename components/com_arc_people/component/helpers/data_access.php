<?php
/**
 * @package     Arc
 * @subpackage  People
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( JPATH_SITE.DS.'components'.DS.'com_arc_people'.DS.'models'.DS.'objects.php' );

/**
 * Data Access Helper
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage Attendance
 * @since 0.1
 */
class ApotheosisPeopleData extends JObject
{
	
	/**
	 * Loads the profile for the specified Arc id
	 *
	 * @param string $pId  The arc user id whose profile is to be retrieved.
	 */
	function getProfile( $pId )
	{
		return new ApothProfile( $pId );
	}
	
	/**
	 * Fetches the year value from the specified users profile
	 *
	 * @param string $pId  The arc user id whose profile year is to be retrieved.
	 * @return string  The year taken from the users profile
	 */
	function getUserProfileYear( $pId )
	{
		$db = &JFactory::getDBO();
		
		$query = 'SELECT value'
			."\n".' FROM '.$db->nameQuote( '#__apoth_ppl_profiles' )
			."\n".' WHERE '.$db->nameQuote( 'person_id' ).' = '.$db->Quote( $pId )
			."\n".'   AND '.$db->nameQuote( 'property').' = '.$db->Quote( 'year' );
		$db->setQuery( $query );
		
		return $db->loadResult();
	}
	
	// #####  Award related functions  #####
	
	function addAward( $pId, $award, $caption )
	{
		$catId = ApotheosisData::_( 'people.profileCatId', 'behaviour', 'awards' );
		$value = 'id='.$award
			."\n".'caption='.$caption;
		
		$db = &JFactory::getDBO();
		
		$query = 'SELECT MAX('.$db->nameQuote('property').')'
			."\n".' FROM '.$db->nameQuote( '#__apoth_ppl_profiles' )
			."\n".' WHERE '.$db->nameQuote( 'person_id' ).' = '.$db->Quote( $pId )
			."\n".'   AND '.$db->nameQuote( 'category_id').' = '.$db->Quote( $catId )
			."\n".' GROUP BY '.$db->nameQuote( 'person_id' );
		$db->setQuery( $query );
		$max = $db->loadResult();
		$next = $max + 1;
		
		$query = 'INSERT INTO '.$db->nameQuote( '#__apoth_ppl_profiles' )
			."\n".' SET '
			."\n".     $db->nameQuote('person_id')  .' = '.$db->Quote($pId)
			."\n".', '.$db->nameQuote('category_id').' = '.$db->Quote($catId)
			."\n".', '.$db->nameQuote('property')   .' = '.$db->Quote($next)
			."\n".', '.$db->nameQuote('value')      .' = '.$db->Quote($value);
		$db->setQuery( $query );
		$db->Query();
	}
	
	function removeAward( $pId, $award, $caption )
	{
		$catId = ApotheosisData::_( 'people.profileCatId', 'behaviour', 'awards' );
		$value = 'id='.$award
			."\n".'caption='.$caption;
		
		$db = &JFactory::getDBO();
		
		$query = 'DELETE FROM '.$db->nameQuote( '#__apoth_ppl_profiles' )
			."\n".' WHERE '.$db->nameQuote('person_id')  .' = '.$db->Quote($pId)
			."\n".'   AND '.$db->nameQuote('category_id').' = '.$db->Quote($catId)
			."\n".'   AND '.$db->nameQuote('value')      .' = '.$db->Quote($value);
		$db->setQuery( $query );
		$db->Query();
	}
	
	
	// #####  Avatar related functions  #####
	
	/**
	 * Retrieves the avatar image for the given user
	 * @param string $pId  The id of the person
	 * @param string $name  The name of the avatar to use (if multiple avatars in use)
	 * @param boolean $allowUpload  If there is no avatar, should we include a link to allow uploading a new one
	 * @return string  The complete html to show the avatar
	 */
	function getAvatar( $pId, $name = 'default', $allowUpload = true )
	{
		$catId = ApotheosisData::_( 'people.profileCatId', 'people', 'personal' );
		$db = &JFactory::getDBO();
		$query = 'SELECT '.$db->nameQuote( 'value' )
			."\n".' FROM '.$db->nameQuote( '#__apoth_ppl_profiles' )
			."\n".' WHERE '.$db->nameQuote( 'category_id' ).' = '.$db->Quote( $catId )
			."\n".'   AND '.$db->nameQuote( 'property' )   .' = '.$db->Quote( 'avatars' );
		$db->setquery( $query );
		$result = $db->loadresult();
		
		if( empty($result) ) {
			$file = false;
		}
		else {
			$matches = array();
			preg_match( '~^'.preg_quote($name).'=(.*?)$~m', $result, $matches );
			
			if( empty($matches) ) {
				preg_match( '~^default=(.*?)$~m', $result, $matches );
			}
			
			if( empty($matches) ) {
				$file = false;
			}
			else {
				$file = $matches[1];
			}
		}
		
		$str = '<div id="avatar">';
		if( $file == false ) {
			$src = JURI::base().'components'.DS.'com_arc_people'.DS.'images'.DS.'no_avatar.png';
		}
		else {
			$src = self::_fName( $pId, 'avatar_'.DS.$file, true );
		}
		$str .= '<img src="'.$src.'" alt="Avatar - '.$name.'" />';
		
		if( $allowUpload ) {
// **** need to make an avatar-upload page.
//			$str .= '<br /><a href="#">Upload an avatar</a>';
		}
		$str .= '</div>';
		
		return $str;
	}
	
	function addAvatar( $pId, $name = 'default' )
	{
		
	}
	
	
	// #####  File related functions  #####
	
	/**
	 * Puts an uploaded file into a person's file area
	 *
	 * @param string $pId  The person's id
	 * @param string $inputName  The name of the form input used to upload the file
	 * @param int $offset  Optionally the index in the array of files (if sharing input name)
	 * @return string|boolean  The full name of the file, or false if it could not be saved
	 */
	function saveFile( $pId, $inputName, $offset = null )
	{
		$f = &$_FILES[$inputName];
		if( is_array($f['name']) ) {
			$id = ( is_null($offset) ? 0 : $offset );
			$base = basename($f['name'][$id]);
			$source = $f['tmp_name'][$id];
			$ok = ($f['error'][$id] == 0);
		}
		else {
			$base = basename($f['name']);
			$source = $f['tmp_name'];
			$ok = ($f['error'] == 0);
		}
		$target = ApotheosisPeopleData::_fName( $pId, $base );
		$targetDir = dirname( $target );
		
		if( $ok ) {
			if( !is_dir($targetDir) ) {
				mkdir( $targetDir );
			}
			return ( move_uploaded_file($source, $target) ? $base : false );
		}
		else {
			return false;
		}
	}
	
	/**
	 * Deletes a file from a person's file area
	 *
	 * @param string $pId  The person's id
	 * @param string $fileName  The base name of the file to be processed (no path)
	 * @return boolean  True on success, false on failure
	 */
	function deleteFile( $pId, $fileName )
	{
		$fName = ApotheosisPeopleData::_fName( $pId, $fileName );
		return ( file_exists($fName) ? unlink($fName) : false );
	}
	
	/**
	 * Retrieves a file from a person's file area
	 *
	 * @param string $pId  The person's id
	 * @param string $fileName  The base name of the file to be processed (no path)
	 * @return string|boolean  The full name of the file, or false if it doesn't exist
	 */
	function getFileName( $pId, $fileName )
	{
		$fName = ApotheosisPeopleData::_fName( $pId, $fileName );
		return ( file_exists($fName) ? $fName : false );
	}
	
	/**
	 * Retrieves a file from a person's file area
	 *
	 * @param string $pId  The person's id
	 * @param string $fileName  The base name of the file to be processed (no path)
	 * @return string|boolean  The full name of the file, or false if it doesn't exist
	 */
	function getFileLink( $pId, $fileName )
	{
		$fName = ApotheosisPeopleData::_fName( $pId, $fileName );
		$fLink = ApotheosisPeopleData::_fName( $pId, $fileName, true );
		return ( file_exists($fName) ? $fLink : false );
	}
	
	function getFileLinkList( $pId )
	{
		$files = array();
		$dir = ApotheosisPeopleData::_dName( $pId );
		if( is_dir($dir) ) {
			$dHandle = opendir( $dir );
			while( $f = readdir($dHandle) ) {
				$fullName = $dir.$f;
				if( is_file($fullName) ) {
					$files[] = $f;
				}
			}
			sort($files);
		}
		return $files;
	}
	
	/**
	 * Internal utility to generate the full path to a user's files from the person id
	 *
	 * @param string $pId  The person's id
	 * @return string  The full path to the user's files
	 */
	function _dName( $pId )
	{
		return ApotheosisData::_( 'core.datapath', 'people', $pId ).DS;
	}
	
	/**
	 * Internal utility to generate the full path for a file from the person id and file base name
	 *
	 * @param string $pId  The person's id
	 * @param string $fileName  The base name of the file to be processed (no path)
	 * @param boolean $url  Do we want a url? (if not, the filesystem path is given)
	 * @return string  The full path for the file
	 */
	function _fName( $pId, $fileName, $url = false )
	{
		if( $url ) {
			return ApotheosisLib::getActionLinkByName( 'apoth_eportfolio_file', array( 'people.arc_people'=>$pId, 'people.files'=>$fileName ) );
		}
		else {
			return self::_dName( $pId ).$fileName;
		}
	}
}

class ApotheosisData_People extends ApotheosisData
{
	function info()
	{
		return 'People component installed';
	}
	
	function displayName( $pId, $use = 'pupil', $legal = false )
	{
		static $checked = array();
		if( !isset( $checked[(int)$legal][$use][$pId] ) ) {
			$checked[(int)$legal][$use][$pId] = ApotheosisLib::getPersonName( $pId, $use, $legal );
		}
		return $checked[(int)$legal][$use][$pId];
	}
	
	/**
	 * Quick and dirty chop out of "people" method below to get personName data for many people at once
	 * 
	 * @param unknown_type $pIds
	 */
	function displayNames( $pIds, $use = 'pupil' )
	{
		$db = &JFactory::getDBO();
		
		if( empty( $pIds ) ) {
			return array();
		}
		
		foreach( $pIds as $k=>$v ) {
			$pIds[$k] = $db->quote( $v );
		}
		
		// having worked out the extra clauses needed, load the data for this list
		$query = 'SELECT DISTINCT p.id, p.title, COALESCE( p.preferred_firstname, p.firstname ) AS firstname, p.middlenames, COALESCE( p.preferred_surname, p.surname ) AS surname'
			."\n".'FROM #__apoth_ppl_people AS p'
			."\nWHERE id IN (".implode( ', ', $pIds ).' )'
			."\n".'ORDER BY p.surname, p.firstname';
		$db->setQuery($query);
		$tmp = $db->loadObjectList( 'id' );
		
		$retVal = array();
		// Use the raw data and add displayable names to it then push it to the stack
		foreach( $tmp as $k=>$v ) {
			$data = array( 'id'=>$v->id, 
				'title'=>$v->title, 
				'firstname'=>$v->firstname,
				'middlenames'=>$v->middlenames,
				'surname'=>$v->surname );
			$retVal[$k] = ApotheosisLib::getPersonName( $data, $type, false, $withTutor );
		}
		
		return $retVal;
	}
	
	function gender( $pId )
	{
		$p = $this->person( $pId );
		return $p->gender;
	}
	
	function year( $pId )
	{
		$db = &JFactory::getDBO();
		
		$query = 'SELECT value'
			."\n".' FROM '.$db->nameQuote( '#__apoth_ppl_profiles' )
			."\n".' WHERE '.$db->nameQuote( 'person_id' ).' = '.$db->Quote( $pId )
			."\n".'   AND '.$db->nameQuote( 'property').' = '.$db->Quote( 'year' );
		$db->setQuery( $query );
		
		return $db->loadResult();
	}
	
	function years( $pIds )
	{
		$db = &JFactory::getDBO();
		foreach( $pIds as $k=>$v ) {
			$pIds[$k] = $db->Quote($v);
		}
		
		$query = 'SELECT person_id, value'
			."\n".' FROM '.$db->nameQuote( '#__apoth_ppl_profiles' )
			."\n".' WHERE '.$db->nameQuote( 'person_id' ).' IN ('.implode( ', ', $pIds ).')'
			."\n".'   AND '.$db->nameQuote( 'property').' = '.$db->Quote( 'year' );
		$db->setQuery( $query );
		$tmp = $db->loadAssocList();
		if( !is_array($tmp) ) { $tmp = array(); }
		
		$retVal = array();
		foreach( $tmp as $row ) {
			$retVal[$row['person_id']] = $row['value'];
		}
		
		return $retVal;
	}
	
	function photo( $pId )
	{
		$db = &JFactory::getDBO();
		
		$query = 'SELECT photo'
			."\n".'FROM '.$db->nameQuote( '#__apoth_ppl_photos' )
			."\n".'WHERE '.$db->nameQuote( 'person_id' ).' = '.$db->Quote( $pId )
			."\n".'LIMIT 1';
		$db->setQuery( $query );
		$data = $db->loadResult();
		
		if( empty( $data ) ) {
			$data = file_get_contents( JPATH_SITE.DS.'components'.DS.'com_arc_people'.DS.'images'.DS.'no_avatar.png' );
		}
		else {
			$data = base64_decode( $data );
		}
		
		$config = &JFactory::getConfig();
		$dirName = $config->getValue('config.tmp_path');
		$tmpName = tempnam( $dirName, 'photo_'.time().'_' );
		
		$im = imagecreatefromstring( $data );
		imagejpeg( $im, $tmpName );
		
		// clear out any old temp files
		$dir = opendir( $dirName );
		if( $dir ) {
			do {
				$fName = readdir( $dir );
				$matches = array();
				preg_match( '/^photo_([\\d]+)_\\w+$/', $fName, $matches );
				if( is_file($dirName.DS.$fName) && isset($matches[1]) && ($matches[1] < (time()-600)) ) {
					unlink( $dirName.DS.$fName );
//					dump( $fName, 'deleting' );
				}
				else {
//					dump( $fName, 'leaving' );
				}
			} while( $fName !== false );
		}
		
		$tmpName = str_replace( JPATH_BASE, JURI::base(), $tmpName );
		return $tmpName;
	}
	
	function peopleListNames( $var )
	{
		if( $var ) {
			return array( 'current', 'everyone', 'pupilof.~group_id~', 'pupil', 'truant', 'parent', 'teacher', 'staff' );
		}
		else {
			return array( 'current', 'everyone', 'pupil', 'truant', 'parent', 'teacher', 'staff' );
		}
	}
	
	/**
	 * Load a list of people defined by the given name
	 * 
	 * *** This starts us down the road towards using named lists in factories
	 * and provides a first go at parsing compound list names (eg "listA AND listB")
	 * This parsing is wholely left-associative with no precedence; ie. "a or b and c" == "(a or b) and c"
	 * 
	 * @param string $listNames  The (compound) list name
	 * @param string|false $limPeople  Auth table to use for permission checks instead of the default, might be false for backend admin hack (no acl on back end)
	 * @param boolean $withTutor  Do we want to add tutor group names where appropriate
	 */
	function people( $listNames = null, $limPeople = null, $withTutor = false )
	{
		if( is_null($listNames) ) {
			return array();
		}
		$debug = ( false );
		if( is_null($limPeople) ) { $limPeople = 'people.arc_people'; }
		
		$db = &JFactory::getDBO();
		$separator = new stdClass();
		$separator->id = '';
		$separator->displayName = '';
		
		// break down the compound list name into simple list names
		$ops = array();
		$listNames = explode( ' ', $listNames );
		// get the data relevant to each list and combine according to compound elements ('AND', 'OR')
		foreach( $listNames as $opId=>$listName ) {
			$wheres = array();
			$joins = $limPeople ? array( '~LIMITINGJOIN~' ) : array();
			$listNameParts = explode( '.', $listName );
			switch( $listNameParts[0] ) {
			case( 'current' ):
				$u = ApotheosisLib::getUser();
				$id = $u->person_id;
				$wheres[] = 'p.id = '.$db->Quote( $id );
				$type = 'person';
				break;
			
			case( 'everyone' ) :
				// no conditions, no joins, just all the people
				$type = 'person';
				break;
			
			case( 'pupilof' ) :
				$wheres[] = 'gm.group_id = '.$db->Quote( $listNameParts[1] );
			case( 'pupil' ) :
				$type = 'pupil';
				$joins[] = 'INNER JOIN #__apoth_tt_group_members AS gm'
					."\n".'  ON gm.person_id = p.id'
					."\n".' AND gm.role = '.ApotheosisLibAcl::getRoleId( 'group_participant_student')
					."\n".' AND '.ApotheosisLibDb::dateCheckSql('gm.valid_from', 'gm.valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s') );
				break;
			
			case( 'parent') :
				$type = 'parent';
				$joins[] = 'INNER JOIN #__apoth_ppl_relations AS r'
					."\n".'  ON r.relation_id = p.id'
					."\n".' AND r.`parental` = r.`correspondence` = r.`reports` = 1'
					."\n".' AND r.`legal_order` = 0'
					."\n".'INNER JOIN #__apoth_tt_group_members AS gm'
					."\n".'  ON gm.person_id = r.pupil_id'
					."\n".' AND gm.role = '.ApotheosisLibAcl::getRoleId( 'group_participant_student')
					."\n".' AND '.ApotheosisLibDb::dateCheckSql('gm.valid_from', 'gm.valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s') );
				break;
			
			case( 'truant' ) :
				$type = 'pupil';
				$joins[] = 'INNER JOIN #__apoth_att_truants AS tr'
					."\n".'  ON tr.pupil_id = p.id'
					."\n".' AND '.ApotheosisLibDb::dateCheckSql('tr.valid_from', 'tr.valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s') );
				break;
				
			case( 'teacher' ) :
				$type = 'teacher';
				$joins[] = 'INNER JOIN #__apoth_tt_group_members AS gm'
					."\n".'  ON gm.person_id = p.id'
					."\n".' AND gm.role = '.ApotheosisLibAcl::getRoleId( 'group_supervisor_teacher')
					."\n".' AND '.ApotheosisLibDb::dateCheckSql('gm.valid_from', 'gm.valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s') );
				break;
			
			case( 'staff' ) :
				$type = 'staff';
				$joins[] = 'INNER JOIN jos_apoth_ppl_profiles AS pro'
					."\n".'   ON pro.person_id = p.id'
					."\n".'  AND pro.property = '.$db->Quote('facebook').''
					."\n".'  AND pro.'.$db->nameQuote('value').' IN ('.$db->Quote('*').', '.$db->Quote('?').')';
					// *** checking for a magic facebook id is not a great way to do this
				break;
			
			case( 'AND' ):
			case( 'OR' ):
				$ops[] = $listNameParts[0];
				continue 2; // operators don't need the remaining code which loads data
				break;
			}
			
			// having worked out the extra clauses needed, load the data for this list
			$query = 'SELECT DISTINCT p.id, p.title, COALESCE( p.preferred_firstname, p.firstname ) AS firstname, p.middlenames, COALESCE( p.preferred_surname, p.surname ) AS surname'
				."\n".'FROM #__apoth_ppl_people AS p'
				.( empty($joins)  ? '' : "\n".implode( "\n", $joins ) )
				.( empty($wheres) ? '' : "\nWHERE ".implode( "\n AND", $wheres ) )
				."\n".'ORDER BY p.surname, p.firstname';
			$query = $limPeople ? ApotheosisLibAcl::limitQuery( $query, $limPeople ) : $query;
			$db->setQuery($query);
			$tmp = $db->loadObjectList( 'id' );
			
			// Prepopulate the tutor groups and course names look-ups
			$tGroups = ApotheosisData::_( 'timetable.tutorgroups', array_keys($tmp) );
			if( is_array($tGroups) && !empty($tGroups) ) {
				ApotheosisData::_( 'course.names', array_unique($tGroups) );
			}
			
			// Use the raw data and add displayable names to it then push it to the stack
			foreach( $tmp as $k=>$v ) {
				$data = array( 'id'=>$v->id, 
					'title'=>$v->title, 
					'firstname'=>$v->firstname,
					'middlenames'=>$v->middlenames,
					'surname'=>$v->surname );
				$tmp[$k]->displayName = ApotheosisLib::getPersonName( $data, $type, false, $withTutor );
			}
			$stack[] = $tmp;
			if( $debug ) { debugQuery( $db, end($stack) ); }
			
			// apply any operators to combine lists
			if( !empty($ops) ) {
				$op = array_pop( $ops );
				$r = array_pop( $stack ); // get the right operand
				$l = array_pop( $stack ); // get the left operand
				switch( $op ) {
				case( 'AND' ):
					$stack[] = array_intersect_key( $l, $r );
					break;
				
				case( 'OR' ):
					$s = clone($separator);
					$s->id = '__'.$opId;
					$tmp = $l;
					$tmp['__'.$opId] = $s;
					foreach( $r as $k=>$v ) {
						if( !isset($tmp[$k]) ) {
							$tmp[$k] = $v;
							unset($r[$k]);
						}
					}
					$stack[] = $tmp;
					unset($tmp);
					break;
				}
			}
		}
		$result = $stack[0]; // no need to check for uniqueness as we have meaningful keys
		foreach( $result as $k=>$v ) {
			if( !is_object($v) ) {
				unset( $result[$k] );
			}
		}
		
		return $result;
	}
	
	/**
	 * Retrieve all the lists that the given person (str) or people (array) are a part of
	 *  
	 * @param str|array $pIds  Arc Id or Ids
	 * @return array $retVal  All the lists person or people are a part of
	 */
	function peopleListMemberships( $pIds )
	{
		// tidy up incoming info
		if( !is_array($pIds) ) {
			$pIds = array( $pIds );
		}
		
		// determine current user
		$u = ApotheosisLib::getUser();
		$currentId = $u->person_id;
		
		// prepare quoted user list
		$db = &JFactory::getDBO();
		foreach( $pIds as $pId ) {
			$quotedIds[] = $db->Quote($pId);
		}
		$quotedIds = implode( ', ', $quotedIds );
		
		
		// define each list as a suitable search clause
		$lists = array(
			'~current~'=>array(
				'where'=>'p.id = '.$db->Quote( $currentId ),
				'join'=>''
			),
			'~everyone~'=>array(
				'where'=>'',
				'join'=>''
			),
			'~pupil~'=>array(
				'where'=>'',
				'join'=>'INNER JOIN #__apoth_tt_group_members AS gm'
					."\n".'   ON gm.person_id IN ('.$quotedIds.')'
					."\n".'  AND gm.role = '.ApotheosisLibAcl::getRoleId( 'group_participant_student' )
					."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'gm.valid_from', 'gm.valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s') )
			),
			'~parent~'=>array(
				'where'=>'',
				'join'=>'INNER JOIN #__apoth_ppl_relations AS r'
					."\n".'   ON r.relation_id IN ('.$quotedIds.')'
					."\n".'  AND r.`parental` = r.`correspondence` = r.`reports` = 1'
					."\n".'  AND r.`legal_order` = 0'
					."\n".'INNER JOIN #__apoth_tt_group_members AS gm'
					."\n".'   ON gm.person_id = r.pupil_id'
					."\n".'  AND gm.role = '.ApotheosisLibAcl::getRoleId( 'group_participant_student' )
					."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'gm.valid_from', 'gm.valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s') )
			),
			'~teacher~'=>array(
				'where'=>'',
				'join'=>'INNER JOIN #__apoth_tt_group_members AS gm'
					."\n".'   ON gm.person_id IN ('.$quotedIds.')'
					."\n".'  AND gm.role = '.ApotheosisLibAcl::getRoleId( 'group_supervisor_teacher' )
					."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'gm.valid_from', 'gm.valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s') )
			),
			'~staff~'=>array(
				'where'=>'',
				'join'=>'INNER JOIN jos_apoth_ppl_profiles AS pro'
					."\n".'   ON pro.person_id IN ('.$quotedIds.')'
					."\n".'  AND pro.property = '.$db->Quote('facebook').''
					."\n".'  AND pro.'.$db->nameQuote('value').' IN ('.$db->Quote('*').', '.$db->Quote('?').')'
					// *** checking for a magic facebook id is not a great way to do this
			)
		);
		
		// compare the people against each list in turn
		$retVal = array();
		foreach( $lists as $list=>$clauses ) {
			$query = 'SELECT COUNT( p.id )'
			."\n".'FROM #__apoth_ppl_people AS p'
			.( empty($clauses['join'])  ? '' : "\n".$clauses['join'] )
			.( empty($clauses['where']) ? '' : "\n".'WHERE '.$clauses['where'] );
			$db->setQuery($query);
			if( $db->loadResult('id') > 0 ) {
				$retVal[] = $list;
			}
		}
		
		return $retVal;
	}
	
	
	/**
	 * Retrieve an object containing personal data about a given person
	 * 
	 * @param str $pId  Arc ID of the person
	 * @return obj  Personal data object for the given person
	 */
	function person( $pId )
	{
		$db = &JFactory::getDBO();
		
		$query = 'SELECT '.$db->nameQuote( 'id' )
			.', '.$db->nameQuote( 'dob' )
			.', '.$db->nameQuote( 'upn' )
			.', '.$db->nameQuote( 'firstname' )
			.', '.$db->nameQuote( 'surname' )
			.', '.$db->nameQuote( 'gender' )
			."\n".'FROM '.$db->nameQuote( '#__apoth_ppl_people' )
			."\n".'WHERE '.$db->nameQuote( 'id' ).' = '.$db->Quote( $pId ).';';
		$db->setQuery( $query );
		
		return $db->loadObject();
	}
	
	/**
	 * Retrieve an object containing relation data about a given person
	 * 
	 * @param str $pId  Arc ID of the person
	 * @return obj  Personal data object for the given person
	 */
	function relations( $pId, $checkComms = false )
	{
		$db = &JFactory::getDBO();
		
		$query = 'SELECT '
			.$db->nameQuote( 'rel' ).'.'.$db->nameQuote( 'relation_id' ).', '
			.$db->nameQuote( 'tree' ).'.'.$db->nameQuote( 'description' )
			."\n".'FROM '.$db->nameQuote( '#__apoth_ppl_relations' ).' AS '.$db->nameQuote( 'rel' )
			."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_ppl_relation_tree' ).' AS '.$db->nameQuote( 'tree' )
			."\n".'   ON '.$db->nameQuote( 'tree' ).'.'.$db->nameQuote( 'id' ).' = '.$db->nameQuote( 'rel' ).'.'.$db->nameQuote( 'relation_type_id' )
			."\n".'WHERE '.$db->nameQuote( 'rel' ).'.'.$db->nameQuote( 'pupil_id' ).' = '.$db->Quote( $pId )
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'valid_from', 'valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s') )
			.( $checkComms ?
				"\n".'  AND '.$db->nameQuote( 'parental' )
				.' = '.$db->nameQuote( 'correspondence' )
				.' = '.$db->nameQuote( 'reports' )
				.' = 1'
				.' AND '.$db->nameQuote( 'legal_order' ).' = 0'
			: '' ).';';
		$db->setQuery( $query );
		
		return $db->loadObjectList();
	}
	
	/**
	 * Retrieve an object containing relation data about a given person
	 * 
	 * @param str $pId  Arc ID of the person
	 * @return obj  Personal data object for the given person
	 */
	function relatedPupils( $pId, $checkComms = false )
	{
		$db = &JFactory::getDBO();
		
		$query = 'SELECT '
			.$db->nameQuote( 'rel' ).'.'.$db->nameQuote( 'pupil_id' ).', '
			.$db->nameQuote( 'tree' ).'.'.$db->nameQuote( 'description' )
			."\n".'FROM '.$db->nameQuote( '#__apoth_ppl_relations' ).' AS '.$db->nameQuote( 'rel' )
			."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_ppl_relation_tree' ).' AS '.$db->nameQuote( 'tree' )
			."\n".'   ON '.$db->nameQuote( 'tree' ).'.'.$db->nameQuote( 'id' ).' = '.$db->nameQuote( 'rel' ).'.'.$db->nameQuote( 'relation_type_id' )
			."\n".'WHERE '.$db->nameQuote( 'rel' ).'.'.$db->nameQuote( 'relation_id' ).' = '.$db->Quote( $pId )
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'valid_from', 'valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s') )
			.( $checkComms ?
				"\n".'  AND '.$db->nameQuote( 'parental' )
				.' = '.$db->nameQuote( 'correspondence' )
				.' = '.$db->nameQuote( 'reports' )
				.' = 1'
				.' AND '.$db->nameQuote( 'legal_order' ).' = 0'
			: '' ).';';
		$db->setQuery( $query );
		
		return $db->loadObjectList();
	}
	
	/**
	 * Retrieve an object containing personal data about a given person
	 * 
	 * @param str $pId  Arc ID of the person
	 * @return obj  Personal data object for the given person
	 */
	function address( $pId )
	{
		$db = &JFactory::getDBO();
		
		$query = 'SELECT '
			.$db->nameQuote( 'add' ).'.'.$db->nameQuote( 'apartment' ).', '
			.$db->nameQuote( 'add' ).'.'.$db->nameQuote( 'name' ).', '
			.$db->nameQuote( 'add' ).'.'.$db->nameQuote( 'number' ).', '
			.$db->nameQuote( 'add' ).'.'.$db->nameQuote( 'number_range' ).', '
			.$db->nameQuote( 'add' ).'.'.$db->nameQuote( 'number_suffix' ).', '
			.$db->nameQuote( 'add' ).'.'.$db->nameQuote( 'street' ).', '
			.$db->nameQuote( 'add' ).'.'.$db->nameQuote( 'district' ).', '
			.$db->nameQuote( 'add' ).'.'.$db->nameQuote( 'town' ).', '
			.'COALESCE( '.$db->nameQuote( 'add' ).'.'.$db->nameQuote( 'county' ).', '.$db->nameQuote( 'add' ).'.'.$db->nameQuote( 'administrative_area' ).' ) AS '.$db->nameQuote( 'county' ).', '
			.$db->nameQuote( 'add' ).'.'.$db->nameQuote( 'postcode' )
			."\n".'FROM '.$db->nameQuote( '#__apoth_ppl_people' ).' AS '.$db->nameQuote( 'ppl' )
			."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_ppl_addresses' ).' AS '.$db->nameQuote( 'add' )
			."\n".'   ON '.$db->nameQuote( 'add' ).'.'.$db->nameQuote( 'id' ).' = '.$db->nameQuote( 'ppl' ).'.'.$db->nameQuote( 'address_id' )
			."\n".'WHERE '.$db->nameQuote( 'ppl' ).'.'.$db->nameQuote( 'id' ).' = '.$db->Quote( $pId ).';';
		$db->setQuery( $query );
		
		return $db->loadObjectList();
	}
	
	/**
	 * Retrieve the profile category id for a given component and category name
	 * 
	 * @param str $com  The component whose category is being requested
	 * @param str $name  The category name that is being requested
	 * @return str  The profile category id
	 */
	function profileCatId( $com, $name )
	{
		$db = &JFactory::getDBO();
		
		$query = 'SELECT '.$db->nameQuote( 'id' )
			."\n".'FROM '.$db->nameQuote( '#__apoth_ppl_profile_categories' )
			."\n".'WHERE '.$db->nameQuote( 'component' ).' = '.$db->Quote( $com )
			."\n".'  AND '.$db->nameQuote( 'name' ).' = '.$db->Quote( $name );
		$db->setQuery( $query );
		
		return $db->loadResult();
	}
	
	/**
	 * Send an email to the people specified
	 * 
	 * @param array $from  Name(0) and email address(1) of sender
	 * @param string $pId  Arc ID of the person whose email address we want
	 * @param string $subject  The subject of the email
	 * @param string $body  The body of the email
	 * @return bool|object $sent  True if email sent, object otherwise
	 */
	function sendEmail( $from, $pId, $subject, $body )
	{
		// get recipient
		$db = &JFactory::getDBO();
		$query = 'SELECT '.$db->nameQuote('users').'.'.$db->nameQuote('email')
			."\n".'FROM '.$db->nameQuote('#__apoth_ppl_people').' AS '.$db->nameQuote('ppl')
			."\n".'INNER JOIN '.$db->nameQuote('#__users').' AS '.$db->nameQuote('users')
			."\n".'   ON '.$db->nameQuote('users').'.'.$db->nameQuote('id').' = '.$db->nameQuote('ppl').'.'.$db->nameQuote('juserid')
			."\n".'WHERE '.$db->nameQuote('ppl').'.'.$db->nameQuote('id').' = '.$db->Quote($pId);
		$db->setQuery( $query );
		$to = $db->loadResult();
		
		// make the email
		$mail = &JFactory::getMailer();
		$mail->setSender( $from );
		$mail->addRecipient( $to );
		$mail->setSubject( $subject );
		$mail->setBody( $body );
		$sent = $mail->Send();
		
		return $sent;
	}
}
?>
