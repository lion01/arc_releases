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

jimport( 'joomla.application.component.model' );

/**
 * XML parser (light)
 *
 * @author      David Swain
 * @package     Arc
 * @subpackage  Core
 * @since       1.3
 */
class ArcXml extends JObject
{
	/**
	 * xml parser object for parsing SIMS reports (and maybe others)
	 * The JSimpleXML parser fails on large files so am writing my own lighter version
	 */
	var $_parser;
	
	/**
	 * Current object depth
	 */
	var $_stack;
	
	/**
	 * The resultant object
	 */
	var $_result;
	
	/**
	 * The current depth of the stack
	 */
	var $_max;
	
	/**
	 * The base file name to look in for data
	 */
	var $_file;
	
	function __construct()
	{
		$this->_fileCount = -1;
		$this->_file   = '';
		$this->_initParser();
	}
	
	function _initParser() {
		$this->_parser = xml_parser_create_ns( '' );
		$this->_result = new ArcXmlElement();
		$this->_stack  = array();
		$this->_max    = -1;
		xml_set_object( $this->_parser, $this );
		xml_parser_set_option( $this->_parser, XML_OPTION_CASE_FOLDING, 0 );
		xml_set_element_handler( $this->_parser, '_startElement', '_endElement' );
		xml_set_character_data_handler( $this->_parser, '_characterData' );
	}
	
	/**
	 * Parses an entire string into objects
	 */
	function loadString( $string )
	{
		$this->_parse( $string );
		xml_parse( $this->_parser, $string );
		xml_parser_free( $this->_parser );
		return $this->_result;
	}
	
	/**
	 * Parses an entire file into objects
	 */
	function loadFile( $inFile )
	{
		$handle = @fopen($inFile, 'rb');
		if( $handle === false ) {
			return false;
		}
		
		while( !feof($handle) ) {
			$contents = fread($handle, 4096);
			xml_parse( $this->_parser, $contents );
		}
		fclose( $handle );
		
		xml_parser_free( $this->_parser );
		return $this->_result;
	}
	
	/**
	 * Sets up parsing of a file to be taken in in chunks.
	 * This is essential to deal with very large files as we can't read the whole thing into memory
	 *
	 * @see ArcXml::next
	 * @param string $file  The name of the file to be read
	 * @return mixed  This parser, or false if file would not open
	 */
	function loadFileChunks( $inFile )
	{
		$this->_handle = fopen($inFile, 'rb');
		if( $this->_handle === false ) {
			return false;
		}
		
		return $this;
	}
	
	/**
	 * Get the next value of the property named
	 */
	function next( $property )
	{
		// if we've run out of properties, load the next chunk
		if( !isset($this->_result->$property) || (count($this->_result->$property) <= 1) ) {
			$this->_loadChunk( $property );
		}
		
		// check again now we've tried to load it
		if( !isset($this->_result->$property) || (count($this->_result->$property) < 1) ) {
			return false;
		}
		else {
			return array_shift($this->_result->$property);
		}
	}
	
	function _loadChunk( $property = '' )
	{
		xml_set_object( $this->_parser, $this );
		
		// if we've now run out of data or didn't get a file, quit
		if( ( $this->_handle === false ) || feof($this->_handle) ) {
			@fclose($this->_handle);
			$this->_handle = false;
			return false;
		}
		
		// otherwise read in and parse the next chunk of contents
		// goes for a small bite out of the file, but keeps going until a close
		// tag for the needed property is found
		$contents = '';
		$bite = '';
		$biteLen = 4096;
		$property = '/'.$property;
		$propLen = strlen( $property );
		$offset = -1 * ( $biteLen + $propLen );
		while( (stripos( $contents, $property, (($offset < 0) ? 0 : $offset) ) === false) && !feof( $this->_handle ) && $bite !== false ) {
			$bite = fread($this->_handle, $biteLen);
			$contents .= $bite;
			$offset += $biteLen;
		}
		
		// with the raw text loaded, parse it into the xml object
		xml_parse( $this->_parser, $contents );
	}
	
	function free()
	{
		@xml_parser_free( $this->_parser );
		unset($this->_stack);
		$this->_stack  = array();
		$this->_result = new ArcXmlElement();
	}
	
	
	// #####  Element handlers  #####
	
	/**
	 * What to do when we encounter a start tag
	 * Increases the current object stack
	 */
	function _startElement( $parser, $name, $attrs = array() )
	{
		$name = strtolower($name);
		$name = preg_replace( array('~_x0020_~', '~_x0028_~', '~_x0029_~', '~_x002f_~', '~/~'), array('_', '', '', '_', 'O.o'), $name );
		if( $this->_max == -1 ) {
			$this->_stack[] = &$this->_result;
			$this->_max = 0;
		}
		else {
			$new = new ArcXmlElement();
			$this->_stack[$this->_max]->{$name}[] = &$new;
			$this->_max++;
			$this->_stack[$this->_max] = &$new;
		}
	}
	
	/**
	 * What to do when we encounter a start tag
	 * Decreases the current object stack
	 */
	function _endElement( $parser, $name )
	{
		unset($this->_stack[$this->_max]);
		$this->_max--;
	}
	
	/**
	 * What to do when we encounter data in a tag
	 * Stores the data in the "_data" property of the element currently at the top of the stack
	 */
	function _characterData( $parser, $data )
	{
		if( trim($data) != '' ) {
			$data = html_entity_decode( $data );
			if( isset($this->_stack[$this->_max]->_data) ) {
				$this->_stack[$this->_max]->_data .= $data;
			}
			else {
				$this->_stack[$this->_max]->_data = $data;
			}
		}
	}
}

/**
 * Each single element of the xml object is an instance of this class.
 * Very light and therefore quick.
 */
class ArcXmlElement
{
	
	var $_data;
	
	function data()
	{
		return $this->_data;
	}
	
	/**
	 * Allows safe and easy access to expected child elements
	 * Returns null if the child doesn't exist
	 */
	function childData( $element, $offset = 0 )
	{
		if( isset($this->{$element}[$offset]) && is_object($this->{$element}[$offset]) ) {
			$retVal = trim( $this->{$element}[$offset]->data() );
		}
		else {
			$retVal = null;
		}
		
		return $retVal;
	}
}
?>