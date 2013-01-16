<?php
/**
 * @package     Arc
 * @subpackage  Homepage
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Homepage Module Panel Object
 */
class ApothModulePanel extends ApothPanel
{
	/**
	 * Constructs a module-based panel object
	 */
	function __construct( $id = false, $data = array() )
	{
		parent::__construct( $id, $data );
	}
}

/**
 * Homepage RSS Module Panel Object
 */
class ApothModulePanelRSS extends ApothModulePanel
{
	/**
	 * Module object
	 * @access protected
	 * @var object
	 */
	var $_module;
	
	/**
	 * Constructs an Arc RRS Feed Reader module-based panel object
	 */
	function __construct( $id = false, $data = array(), $u = null )
	{
		parent::__construct( $id, $data );
		
		// set the module name, the first of 2 required properties needed to build the module this way
		$this->_module->module = 'mod_arc_rss';
		
		// build the common params property
		$paramsArray[] = 'block_view=0';
		$paramsArray[] = 'rss_item_summary=1';
		$paramsArray[] = 'word_count=0';
		$paramsArray[] = 'moduleclass_sfx=';
		$paramsArray[] = 'cache=0';
		$paramsArray[] = 'cache_time=15';
		
		// build the feed specific properties
		$URLs = explode( "\n", $this->_data['url'] );
		$i = 0;
		foreach( $URLs as $url ) {
			
			// check if year group news keyword is found
			if( trim($url) == 'year_group_news' )  {
				$url = $this->_getYearURL( $u );
			}
			if( $url != false ) {
				$paramsArray[] = 'rss_url_'.$i.'='.trim( $url );
				$paramsArray[] = 'rss_items_'.$i.'=10';
				$paramsArray[] = 'rss_title_'.$i.'=1';
				$paramsArray[] = 'rss_desc_'.$i.'=1';
				$paramsArray[] = 'rss_image_'.$i.'=1';
				$paramsArray[] = 'rss_favicon_'.$i.'=1';
				$paramsArray[] = 'rss_rtl_'.$i.'=0';
				$i++;
			}
		}
		
		// create single params property string, the other required property
		$this->_module->params = implode( "\n", $paramsArray );
		$this->_module->user = 0;
	}
	
	/**
	 * Retrieves a year specific Arc blog link
	 * @return string  year specific Arc blog URL
	 */
	function _getYearURL( $uId = null)
	{
		// get user year group
		if( is_null($uId) ) {
			$u = JFactory::getUser();
		}
		else {
			$u = ApotheosisLib::getJUserId( $uId );
		}
		$year = ApotheosisLib::getUserYear( $u->id );
		if( is_numeric($year) ) {
			// fetch year specific URL
			$paramsObj = &JComponentHelper::getParams('com_arc_homepage');
			$yearUrl = $paramsObj->get( $year );
			$url = JURI::base().$yearUrl.'&format=feed&type=rss';
		}
		else {
			$url = false;
		}
		
		return $url;
	}
	
	/**
	 * Retrieves the Arc RSS Feed Reader module object
	 * @return The Arc RSS Feed Reader module object
	 */
	function getModule()
	{
		return $this->_module;
	}
}
?>