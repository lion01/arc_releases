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
 * People Admin Josuser Model
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage People
 * @since      1.6
 */
class PeopleAdminModelJosuser extends ArcAdminModel
{
	/**
	 * Constructs the people admin josuser model
	 */
	function __construct( $config = array() )
	{
		$config['component'] = 'com_arc_people';
		parent::__construct( $config );
		
		// make the people factory available to the model
		$this->fPeo = ApothFactory::_( 'people.person', $this->fPeo );
	}
	
	/**
	 * Get the number of potential jos user accounts we could create
	 * 
	 * @return int  The number of potential jos users accounts
	 */
	function getPotentialJosUsers()
	{
		return count( $this->_potentialJosUsers );
	}
	
	/**
	 * Set the Arc ID's of potential jos user accounts we could create
	 */
	function setPotentialJosUsers()
	{
		$this->_potentialJosUsers = $this->fPeo->getInstances( array('juserid'=>null), false );
	}
	
	/**
	 * Get the current website domain
	 * 
	 * @return string  The current website domain
	 */
	function getDomain()
	{
		return $this->_domain;
	}
	
	/**
	 * Set the current website domain
	 */
	function setDomain()
	{
		$u = &JFactory::getURI();
		$this->_domain = $u->getHost();
	}
	
	/**
	 * Create the new Joomla acounts
	 * 
	 * @return array  An array containing success indicator and err msgs for the db operations
	 */
	function createJosUser()
	{
		$this->setPotentialJosUsers();
		$this->setDomain();
		
		// get the relevant format params
		$this->_loadParams();
		$pplParams = $this->_params->toArray();
		foreach( $pplParams as $k=>$v ) {
			preg_match( '~(fullname|username|email)([0-9]+)~', $k, $matches );
			if( !empty($matches) ) {
				$formats[$matches[2]][$matches[1]] = $v;
			}
		}
		
		$errMsgs = array();
		foreach( $this->_potentialJosUsers as $arcId ) {
			// get a person object
			$person = &$this->fPeo->getInstance( $arcId );
			
			// loop through the format options
			$blankKeyword = false;
			$josSave = false;
			foreach( $formats as $formatArray ) {
				// get the required strings and check validity
				$name = $this->makeString( $person, $formatArray['fullname'] );
				if( $name === false ) {
					$blankKeyword = true;
					continue;
				}
				elseif( $name == '' ) {
					continue;
				}
				
				$username = $this->makeString( $person, $formatArray['username'] );
				if( $username === false ) {
					$blankKeyword = true;
					continue;
				}
				elseif( $username == '' ) {
					continue;
				}
				
				$email = $this->makeString( $person, $formatArray['email'] );
				if( $email === false ) {
					$blankKeyword = true;
					continue;
				}
				elseif( $email == '' ) {
					continue;
				}
				
				// get a new jos user object and update with needed data
				$newJosUser = new JUser();
				$newJosUser->name = $name;
				$newJosUser->username = $username;
				$newJosUser->set( 'password', ' ' ); // must have something here so it can be manually set later.
				$newJosUser->email = $email;
				$newJosUser->usertype = 'Registered';
				$newJosUser->gid = '18';
				if( ($josSave = $newJosUser->save()) == true ) {
					break;
				}
			}
			
			// deal with outcome of trying to create the new jos user
			if( $josSave ) {
				$person->setDatum( 'juserid', $newJosUser->id );
				if( !$person->commit() ) {
					$errMsgs['arc'][] = $person->getDatum('firstname').' '.$person->getDatum('surname');
					
					// remove the newly created jos user to avoid duplication errors on a retry
					$newJosUser->delete();
				}
			}
			elseif( $blankKeyword ) {
				$errMsgs['blank'][] = $person->getDatum('firstname').' '.$person->getDatum('surname');
			}
			else {
				$errMsgs['jos'][] = $person->getDatum('firstname').' '.$person->getDatum('surname');
			}
			
			// clear this instance from memory
			$this->fPeo->freeInstance( $arcId );
		}
		$this->fPeo->clearCache();
		$success = empty( $errMsgs );
		
		return array( $success, $errMsgs );
	}
	
	/**
	 * Make the required jos user string by replacing keywords with user data
	 * 
	 * @param object $person  Person object
	 * @param string $format  The format string taken from the params
	 * @return string  The requested string
	 */
	function makeString( $person, $format )
	{
		// chars safe for joomla email creation from JMailHelper::isEmailAddress() )
		$joomlaSafe = '~[^A-Za-z0-9!#&*+=?_-]~';
		
		// get the required data from the person object
		$firstname = preg_replace( $joomlaSafe, '', $person->getDatum('firstname') );
		$middlename = preg_replace( $joomlaSafe, '', $person->getDatum('middlenames') );
		$surname = preg_replace( $joomlaSafe, '', $person->getDatum('surname') );
		$email = $person->getDatum( 'email' );
		$domain = $this->_domain;
		
		// generate the search patterns 
		$pattern[] = '[[uc_firstname]]';
		$pattern[] = '[[lc_firstname]]';
		$pattern[] = '[[as_firstname]]';
		$pattern[] = '[[uc_firstinit]]';
		$pattern[] = '[[lc_firstinit]]';
		$pattern[] = '[[uc_middlename]]';
		$pattern[] = '[[lc_middlename]]';
		$pattern[] = '[[as_middlename]]';
		$pattern[] = '[[uc_middleinit]]';
		$pattern[] = '[[lc_middleinit]]';
		$pattern[] = '[[uc_surname]]';
		$pattern[] = '[[lc_surname]]';
		$pattern[] = '[[as_surname]]';
		$pattern[] = '[[uc_surinit]]';
		$pattern[] = '[[lc_surinit]]';
		$pattern[] = '[[email]]';
		$pattern[] = '[[domain]]';
		
		// generate the search replacements
		$replace[] = ucfirst( strtolower($firstname) );
		$replace[] = strtolower( $firstname );
		$replace[] = $firstname;
		$replace[] = strtoupper( substr($firstname, 0, 1) );
		$replace[] = strtolower( substr($firstname, 0, 1) );
		$replace[] = ucfirst( strtolower($middlename) );
		$replace[] = strtolower( $middlename );
		$replace[] = $middlename;
		$replace[] = strtoupper( substr($middlename, 0, 1) );
		$replace[] = strtolower( substr($middlename, 0, 1) );
		$replace[] = ucfirst( strtolower($surname) );
		$replace[] = strtolower( $surname );
		$replace[] = $surname;
		$replace[] = strtoupper( substr($surname, 0, 1) );
		$replace[] = strtolower( substr($surname, 0, 1) );
		$replace[] = $email;
		$replace[] = $domain;
		
		$retVal = '';
		foreach( $pattern as $k=>$patternStr ) {
			if( strpos($format, $patternStr) === false ) {
				continue;
			}
			elseif( $replace[$k] != '' ) {
				$retVal = $format = str_replace( $patternStr, $replace[$k], $format );
			}
			else {
				$retVal = false;
				break;
			}
		}
		
		return $retVal;
	}
	
	/**
	 * Set Joomla! account passwords from an uploaded list
	 * 
	 * @param array $pwords  Array of arc ID's and corresponding clear text passwords
	 * @return array  An array containing success indicator and err msgs for the db operations
	 */
	function setJosPasswords( $pwords )
	{
		// bring in the JUserHelper
		jimport( 'joomla.user.helper' );
		
		// remove header rows and gets Arc IDs
		array_shift( $pwords );
		$allArcIds = array_keys( $pwords );
		$arcIdReqs = array( 'id'=>$allArcIds );
		$arcIds = $this->fPeo->getInstances( $arcIdReqs, false );
		
		// set and save password for each user
		$errMsgs = array();
		foreach( $arcIds as $arcId ) {
			$noJUserId = false;
			$noJUser = false;
			$josSave = false;
			
			$arcUser = &$this->fPeo->getInstance( $arcId );
			$jUserId = $arcUser->getDatum('juserid');
			
			if( !is_null($jUserId) ) {
				$josUser = &JFactory::getUser( $arcUser->getDatum('juserid') );
				if( $josUser->get('id') == $jUserId ) {
					// generate password
					$salt = JUserHelper::genRandomPassword( 32 );
					$crypted = JUserHelper::getCryptedPassword( $pwords[$arcId], $salt );
					$password = $crypted.':'.$salt;
					
					// set password and save
					$josUser->set( 'password', $password );
					$josSave = !$josUser->save( true );
				}
				else {
					$noJUser = true;
				}
			}
			else {
				$noJUserId = true;
			}
			
			// deal with outcome of trying to set the jos user password
			if( !$josSave ) {
				if( $noJUserId ) {
					$errMsgs['noJUserId'][] = $arcId;
				}
				elseif( $noJUser ) {
					$errMsgs['noJUser'][] = $arcId;
				}
				else {
					$errMsgs['noSave'][] = $arcId;
				}
			}
			
			// clear this instance from memory
			$this->fPeo->freeInstance( $arcId );
			
			unset( $pwords[$arcId] );
		}
		$this->fPeo->clearCache();
		
		// determine any invalid Arc IDs
		$unusedArcIds = array_diff( $allArcIds, $arcIds );
		if( !empty($unusedArcIds) ) {
			$errMsgs['noArcId'] = $unusedArcIds;
		}
		
		$success = empty( $errMsgs );
		
		return array( $success, $errMsgs );
	}
}
?>