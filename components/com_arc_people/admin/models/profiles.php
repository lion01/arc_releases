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

// Give us access to the joomla model class
jimport( 'joomla.application.component.model' );

/**
 * People Admin Profiles Model
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage People
 * @since      1.6
 */
class PeopleAdminModelProfiles extends JModel
{
	/**
	 * Get a list of template IDs
	 * 
	 * @return Array  An array of template IDs
	 */
	function getTemplateIds()
	{
		return $this->_templateIds;
	}
	
	/**
	 * Set a list of template IDs
	 */
	function setTemplateIds()
	{
		$db = JFactory::getDBO();
		
		$query = 'SELECT DISTINCT '.$db->nameQuote('person_type')
			."\n".'FROM '.$db->nameQuote('#__apoth_ppl_profile_templates');
		$db->setQuery( $query );
		
		$this->_templateIds = $db->loadResultArray();
	}
	
	/**
	 * Get the current profile type
	 * 
	 * @return string  The current profile type
	 */
	function getCurType()
	{
		return $this->_curType;
	}
	
	/**
	 * Set the given profile type as current
	 * 
	 * @param string $type  The profile type that we want to set as current
	 */
	function setCurType( $type )
	{
		$this->_curType = $type;
	}
	
	/**
	 * Get the current IDs
	 * 
	 * @return array  The current profile IDs
	 */
	function getCurIds()
	{
		return $this->_curIds;
	}
	
	/**
	 * Set the given profile IDs as current
	 * 
	 * @param array $ids  The profile IDs that we want to set as current
	 */
	function setCurIds( $ids )
	{
		$this->_curIds = $ids;
	}
	
	/**
	 * Get the current profiles
	 * 
	 * @return array  A list of all the entries for the specified profiles
	 */
	function getProfiles()
	{
		return $this->_profiles;
	}
	
	/**
	 * Set the given profiles
	 * 
	 * @param array $ids  The profile IDs that we want to set
	 */
	function setProfiles( $ids )
	{
		$this->_profiles = array();
		if( !is_array( $ids ) ) {
			return false;
		}
		$db = JFactory::getDBO();
		
		foreach( $ids as $k=>$id ) {
			$ids[$k] = $db->Quote( $id );
		}
		$quotedIds = implode( ', ', $ids );
		
		switch( $this->_curType ) {
			case( 'template' ):
				$query = 'SELECT *'
					."\n".'FROM '.$db->nameQuote('#__apoth_ppl_profile_templates')
					."\n".'WHERE '.$db->nameQuote('person_type').' IN ('.$quotedIds.')'
					."\n".'ORDER BY '.$db->nameQuote('person_type').', '.$db->nameQuote('category_id').', ABS('.$db->nameQuote('property').')';
				
				$fieldName = 'person_type';
				break;
				
			case( 'profile' ):
				$query = 'SELECT *'
					."\n".'FROM '.$db->nameQuote('#__apoth_ppl_profiles')
					."\n".'WHERE '.$db->nameQuote('person_id').' IN ('.$quotedIds.')'
					."\n".'ORDER BY '.$db->nameQuote('person_id').', '.$db->nameQuote('category_id').', ABS('.$db->nameQuote('property').')';
				
				$fieldName = 'person_id';
				break;
		}
		
		$db->setQuery( $query );
		$profileEntries = $db->loadAssocList();
		
		foreach( $profileEntries as $entry ) {
			$this->_profiles[$entry[$fieldName]][] = $entry;
		}
	}
	
	/**
	 * Get a mapping of category id's, category name and owning component
	 * 
	 * @return array  Array of mappings indexed on id 
	 */
	function getCategoryMap()
	{
		return $this->_catMap;
	}
	
	/**
	 * Set a mapping of category id's, category name and owning component
	 */
	function setCategoryMap()
	{
		$db = JFactory::getDBO();
		
		$query = 'SELECT *'
			."\n".'FROM '.$db->nameQuote('#__apoth_ppl_profile_categories');
		$db->setQuery( $query );
		
		$this->_catMap = $db->loadAssocList('id');
	}
	
	/**
	 * Save the profile form data
	 * 
	 * @param array $data  Array of form data to be saved
	 * @param array $partials  Array of prtial properties
	 * @return array  An array containing success indicator and err msgs for the db operations
	 */
	function saveProfile( $data, $partials )
	{
		// find the id for the Ids category
		foreach( $this->_catMap as $catId=>$catInfo ) {
			if( $catInfo['name'] == 'ids' ) {
				$idsCat = $catId;
				break;
			}
		}
		
		// update the incoming ARC property value to be the Arc ID
		// if we are dealing with profiles and not templates
		foreach( $data as $id=>$idArray ) {
			// find and delete the existing ARC property, if present
			foreach( $idArray as $k2=>$v2 ) {
				if( ($v2['property'] == 'ARC') && ($v2['category_id'] == $idsCat) && array_key_exists('person_id', $v2) ) {
					unset( $data[$id][$k2] );
					break;
				}
			}
			// set a new ARC property with correct Arc ID and add it
			$newEntry = array( 'person_id'=>$id, 'category_id'=>$idsCat, 'property'=>'ARC', 'value'=>$id );
			array_unshift( $data[$id], $newEntry );
		}
		
		// clone the current profile
		$curData = $this->_profiles;
		
		// remove common items between old and new (unchanged stuff)
		// and any entries we otherwise don't want to consider for deleteing or saving
		foreach( $curData as $id=>$idArray ) {
			foreach( $idArray as $k=>$v ) {
				$match = false;
				
				// see if the incoming property exactly matches an existing property
				$k2 = array_search( $v, $data[$id] );
				if( ($k2 !== false) && ($k2 !== null) ) {
					$match = true;
				}
				// if not then see if we have the incoming property
				else {
					foreach( $data[$id] as $k2=>$v2 ) {
						
						
						// ############### Categories 1 and 2 ###############
						if( ($v2['category_id'] == 1) || ($v2['category_id'] == 2) ) {
							
							// if new category and property matches an exisiting one...
							if( ($v['category_id'] == $v2['category_id']) && ($v['property'] == $v2['property']) ) {
								
								// ...but value is locked then treat as a match
								if( $v2['value'] == '*** Locked ***' ) {
									$match = true;
								}
								// ...but value is other than locked
								// then set this as a partial to keep for this person
								else {
									$propsToKeep[$id][$v['category_id']][] = $v['property'];
								}
								break;
							}
						}
						// ##################################################
						
						
						// ############### Category 3 #######################
						if( $v2['category_id'] == 3 ) {
							
							// if new categories match...
							if( ($v['category_id'] == $v2['category_id']) ) {
								
								// get usable info from the "old" panel properties
								$oldPanelProps = str_replace( array("\r\n", "\r"), "\n", $v['value'] );
								$oldPanelData = explode( "\n", $oldPanelProps );
								foreach( $oldPanelData as $datumString ) {
									$datumArray = explode( '=', $datumString, 2 );
									$oldDatum[$datumArray[0]] = $datumArray[1]; 
								}
								
								// get usable info from the "new" panel properties
								$newPanelData = explode( "\n", $v2['value'] );
								foreach( $newPanelData as $datumString ) {
									$datumArray = explode( '=', $datumString, 2 );
									$newDatum[$datumArray[0]] = $datumArray[1]; 
								}
								
								$panelMatch = $oldDatum['id'] == $newDatum['id'];
								$colMatch = $oldDatum['col'] == $newDatum['col'];
								
								// ...and panels and columns match...
								if( $panelMatch && $colMatch ) {
									$propertyMatch = $v['property'] == $v2['property'];
									
									// ...but shown is 2 (mixed state)
									// and property is the same then treat as a match
									// (basically everything is the same at this point except shown is mixed)
									if( ($newDatum['shown'] == '2') && $propertyMatch ) {
										$match = true;
									}
									// ...but shown is other than 2 (definite state)
									// or the property may have changed
									// then set this as a partial panel to keep for this person
									else {
										$panelsToKeep[$id][] = $oldDatum['id'];
									}
									break;
								}
							}
						}
						// ##################################################
						
						
						// ############### Category 4 #######################
						if( $v2['category_id'] == 4 ) {
							
							// if new categories and links match...
							if( ($v['category_id'] == $v2['category_id']) && ($v['value'] == $v2['value']) ) {
								
								// then set this as a partial to keep for this person
								$linksToKeep[$id][] = $v['value'];
								break;
							}
						}
						// ##################################################
						
						
					}
				}
				
				// if we found a match or the value was locked then don't consider this property for deletion or insertion
				if( $match ) {
					unset( $curData[$id][$k] );
					unset( $data[$id][$k2] );
				}
			}
			
			// if either array for this ID is now empty then drop as required
			if( empty($curData[$id]) ) {
				unset( $curData[$id] );
			}
			if( empty($data[$id]) ) {
				unset( $data[$id] );
			}
		}
		
		// clean up any residual data having only partial properties
		foreach( $data as $id=>$idArray ) {
			foreach( $idArray as $k=>$v ) {
				
				
				// ############### Categories 1 and 2 ###############
				if( ($v['category_id'] == 1) || ($v['category_id'] == 2) ) {
					
					// if the property is in the partials array then remove it
					// as long as we don't already have that property
					if( is_array($partials[$v['category_id']]) ) {
						$partialKey = array_search( $v['property'], $partials[$v['category_id']] );
					}
					else {
						$partialKey = false;
					}
					if( isset($propsToKeep[$id][$v['category_id']]) ) {
						$keepPartial = array_search( $v['property'], $propsToKeep[$id][$v['category_id']] );
					}
					else {
						$keepPartial = false;
					}
					if( ($partialKey !== false) && ($partialKey !== null) && (($keepPartial === false) || ($keepPartial === null)) ) {
						unset( $data[$id][$k] );
					}
					
					// any data now must be valid as either not matched to existing or set to universal
					// however, it could still be marked as locked
					// if so change this to an empty string for storage
					if( isset($data[$id][$k]) && ($v['value'] == '*** Locked ***') ) {
						$data[$id][$k]['value'] = '';
					}
				}
				// ##################################################
				
				
				// ############### Category 3 #######################
				if( $v['category_id'] == 3 ) {
					
					// get usable info from the "new" panel properties
					$newPanelData = explode( "\n", $v['value'] );
					foreach( $newPanelData as $datumString ) {
						$datumArray = explode( '=', $datumString, 2 );
						$newDatum[$datumArray[0]] = $datumArray[1]; 
					}
					
					// if the panel is in the panel partials array then remove it
					// as long as we don't already have that panel
					if( is_array($partials[$v['category_id']]) ) {
						$partialKey = array_search( $newDatum['id'], $partials[$v['category_id']] );
					}
					else {
						$partialKey = false;
					}
					if( isset($panelsToKeep[$id]) ) {
						$keepPanel = array_search( $newDatum['id'], $panelsToKeep[$id] );
					}
					else {
						$keepPanel = false;
					}
					if( ($partialKey !== false) && ($partialKey !== null) && (($keepPanel === false) || ($keepPanel === null)) ) {
						unset( $data[$id][$k] );
					}
					
					// any data now must be valid as having been set to universal and we didn't have it before
					// however, it could still be marked as shown is mixed
					// if so change shown to 1 (active) by default
					// in any case reformat value for correct line endings
					if( isset($data[$id][$k]) ) {
						if( $newDatum['shown'] == '2' ) {
							$newDatum['shown'] = '1';
						}
						$data[$id][$k]['value'] = 'id='.$newDatum['id']."\n".'col='.$newDatum['col']."\n".'shown='.$newDatum['shown'];
					}
				}
				// ##################################################
				
				
				// ############### Category 4 #######################
				if( $v['category_id'] == 4 ) {
					
					// if the link is in the link partials array then remove it
					// as long as we don't already have that link
					if( is_array($partials[$v['category_id']]) ) {
						$partialKey = array_search( $v['value'], $partials[$v['category_id']] );
					}
					else {
						$partialKey = false;
					}
					if( isset($linksToKeep[$id]) ) {
						$keepLink = array_search( $v['value'], $linksToKeep[$id] );
					}
					else {
						$keepLink = false;
					}
					if( ($partialKey !== false) && ($partialKey !== null) && (($keepLink === false) || ($keepLink === null)) ) {
						unset( $data[$id][$k] );
					}
				}
				// ##################################################
				
				
				// if the array for this ID is now empty then drop that too
				if( empty($data[$id]) ) {
					unset( $data[$id] );
				}
			}
		}
		
		$db = JFactory::getDBO();
		
		// prepare the delete where clause
		$deleting = false;
		if( !empty($curData) ) {
			$deleting = true;
			foreach( $curData as $id=>$idArray ) {
				foreach( $idArray as $delRow ) {
					$delTmp = array();
					foreach( $delRow as $delField=>$delValue ) {
						$delTmp[] = '('.$db->nameQuote($delField).' = '.$db->Quote($delValue).')';
					}
					$delWhere[] = '('.implode( ' && ', $delTmp ).')';
				}
			}
			$delWheres = '( '.implode( "\n".' || ', $delWhere ).' )';
		}
		
		// prepare the insert where clause
		$inserting = false;
		if( !empty($data) ) {
			$inserting = true;
			foreach( $data as $id=>$idArray ) {
				foreach( $idArray as $insRow ) {
					$insTmp = array();
					foreach( $insRow as $insField=>$insValue ) {
						$insTmp[] = $db->Quote($insValue);
					}
					$insVal[] = '('.implode( ', ', $insTmp ).')';
				}
			}
			$insVals = implode( "\n".', ', $insVal );
		}
		
		// prepare the queries
		switch( $this->_curType ) {
		case( 'template' ):
			if( $deleting ) {
				$deleteQuery = 'DELETE FROM '.$db->nameQuote('#__apoth_ppl_profile_templates')
					."\n".'WHERE '.$delWheres;
			}
			
			if( $inserting ) {
				$insertQuery = 'INSERT INTO '.$db->nameQuote('#__apoth_ppl_profile_templates')
					."\n".'VALUES '.$insVals;
			}
			break;
		
		case( 'profile' ):
			if( $deleting ) {
				$deleteQuery = 'DELETE FROM '.$db->nameQuote('#__apoth_ppl_profiles')
					."\n".'WHERE '.$delWheres;
			}
			
			if( $inserting ) {
				$insertQuery = 'INSERT INTO '.$db->nameQuote('#__apoth_ppl_profiles')
					."\n".'VALUES '.$insVals;
			}
			break;
		}
		
		// run the queries
		$delErrMsg = '';
		$insErrMsg = '';
		if( $deleting ) {
			$db->setQuery( $deleteQuery );
			$db->Query();
			$delErrMsg = $db->getErrorMsg();
		}
		if( $inserting ) {
			$db->setQuery( $insertQuery );
			$db->Query();
			$insErrMsg = $db->getErrorMsg();
		}
		
		// prepare save outcomes
		$success = ( ($delErrMsg == '') && ($insErrMsg == '') );
		$errMsgs = array();
		if( !$success ) {
			$errMsgs = array( $delErrMsg, $insErrMsg );
		}
		
		return array( $success, $errMsgs );
	}
	
	/**
	 * Save the template types form data
	 * 
	 * @param array $data  Array of form data to be saved
	 * @return array  An array containing success indicator and err msgs for the db operations
	 */
	function saveTemplateTypes( $data )
	{
		// get the exisiting list of template types
		$curIds = $this->_templateIds;
		
		// remove common items between old and new (unchanged stuff)
		foreach( $curIds as $k=>$v ) {
			$k2 = array_search( $v, $data );
			if( ($k2 !== false) && ($k2 !== null) ) {
				unset( $curIds[$k] );
				unset( $data[$k2] );
			}
		}
		
		$db = JFactory::getDBO();
		
		// prepare the delete where clause
		$deleting = false;
		if( !empty($curIds) ) {
			$deleting = true;
			foreach( $curIds as $k=>$curId ) {
				$curIds[$k] = $db->Quote( $curId );
			}
			$curIdsQuoted = implode( ', ', $curIds );
		}
		
		// prepare the insert where clause
		$inserting = false;
		if( !empty($data) ) {
			$inserting = true;
			foreach( $data as $newId ) {
				$insVal[] = '( '.$db->Quote($newId).', '.$db->Quote('1').', '.$db->Quote('ARC').', '.$db->Quote('').' )';
			}
			$insVals = implode( ', ', $insVal );
		}
		
		// prepare the queries
		$deleteQuery = 'DELETE FROM '.$db->nameQuote('#__apoth_ppl_profile_templates')
			."\n".'WHERE '.$db->nameQuote('person_type').' IN ('.$curIdsQuoted.')';
		
		$insertQuery = 'INSERT INTO '.$db->nameQuote('#__apoth_ppl_profile_templates')
			."\n".'VALUES '.$insVals;
		
		// run the queries
		$delErrMsg = '';
		$insErrMsg = '';
		if( $deleting ) {
			$db->setQuery( $deleteQuery );
			$db->Query();
			$delErrMsg = $db->getErrorMsg();
		}
		if( $inserting ) {
			$db->setQuery( $insertQuery );
			$db->Query();
			$insErrMsg = $db->getErrorMsg();
		}
		
		// prepare save outcomes
		$success = ( ($delErrMsg == '') && ($insErrMsg == '') );
		$errMsgs = array();
		if( !$success ) {
			$errMsgs = array( $delErrMsg, $insErrMsg );
		}
		
		return array( $success, $errMsgs );
	}
	
	/**
	 * Update profile year groups by matching to tutor group
	 * 
	 * @return array  An array containing success indicator and err msgs for the db operations
	 */
	function matchYearToTutor()
	{
		$db = &JFactory::getDBO();
		
		$studentRole = ApotheosisLibAcl::getRoleId( 'group_participant_student' );
		$tutorRole = ApotheosisLibAcl::getRoleId( 'group_supervisor_teacher' );
		$quotedRoles = $db->Quote( $studentRole ).', '.$db->Quote( $tutorRole );
		
		$query = 'UPDATE '.$db->nameQuote('#__apoth_ppl_profiles').' AS '.$db->nameQuote('pro')
			."\n".'INNER JOIN '.$db->nameQuote('#__apoth_tt_group_members').' AS '.$db->nameQuote('gm')
			."\n".'   ON '.$db->nameQuote('gm').'.'.$db->nameQuote('person_id').' = '.$db->nameQuote('pro').'.'.$db->nameQuote('person_id')
			."\n".'  AND '.$db->nameQuote('gm').'.'.$db->nameQuote('valid_from').' < NOW()'
			."\n".'  AND ('.$db->nameQuote('gm').'.'.$db->nameQuote('valid_to').' > NOW() OR '.$db->nameQuote('gm').'.'.$db->nameQuote('valid_to').' IS NULL)'
			."\n".'  AND '.$db->nameQuote('gm').'.'.$db->nameQuote('role').' IN ('.$quotedRoles.')'
			."\n".'INNER JOIN '.$db->nameQuote('#__apoth_cm_courses').' AS '.$db->nameQuote('c')
			."\n".'   ON '.$db->nameQuote('c').'.'.$db->nameQuote('id').' = '.$db->nameQuote('gm').'.'.$db->nameQuote('group_id')
			."\n".'  AND '.$db->nameQuote('c').'.'.$db->nameQuote('type').' = '.$db->Quote('pastoral')
			."\n".'SET '.$db->nameQuote('pro').'.'.$db->nameQuote('value').' = '.$db->nameQuote('c').'.'.$db->nameQuote('year')
			."\n".'WHERE '.$db->nameQuote('pro').'.'.$db->nameQuote('property').' = '.$db->Quote('year')
			."\n".'  AND '.$db->nameQuote('pro').'.'.('value').' != '.$db->nameQuote('c').'.'.$db->nameQuote('year');
		$db->setQuery( $query );
		
		$db->Query();
		$errMsg = $db->getErrorMsg();
		
		// prepare save outcomes
		$success = ( $errMsg == '' );
		$errMsgs = array();
		if( !$success ) {
			$errMsgs = array($errMsg );
		}
		
		return array( $success, $errMsgs );
	}
}
?>