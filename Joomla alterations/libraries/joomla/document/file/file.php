<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_BASE') or die();

/**
 * DocumentFile class, provides an easy interface to send complete files
 *
 * @package     Joomla.Platform
 * @subpackage  Document
 * @since       11.1
 */
class JDocumentFile extends JDocument
{
	/**
	 * Class constructor
	 *
	 * @param   array  $options  Associative array of options
	 *
	 * @since   11.1
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);
		
		// set mime type
		$this->setMimeEncoding( 'application/octet-stream' );
		
		// set document type
		$this->setType( 'file' );
		
		// set default name for delivered file
		$this->setFileName( 'download.file' );
		
		// make sure we're not buffering any output
		$l = ob_get_level();
		for( $i = 0; $i<$l; $i++ ) {
			ob_end_clean();
		}
		
		// set headers that make this behave as a file download
		JResponse::setHeader('Content-description', 'File Transfer', true);
		JResponse::setHeader('Content-type', 'application/octet-stream', true);
		JResponse::setHeader('Content-transfer-encoding', 'binary', true);
	}
	
	public function setFileName( $name )
	{
		$this->_fName = $name;
		JResponse::setHeader('Content-disposition', 'attachment; filename="'.$this->_fName.'"', true);
	}
	
	/**
	 * Set the contents of the document buffer
	 * This actually directly echos the content as buffering can
	 * exceed memory limits with large files 
	 *
	 * @param   string  $content  The content to be set in the buffer.
	 * @param   array   $options  Array of optional elements.
	 *
	 * @return  JDocument instance of $this to allow chaining
	 *
	 * @since   11.1
	 */
	public function setBuffer($content, $options = array())
	{
		echo $content;
		
		return $this;
	}
	
	/**
	 * Render the document.
	 *
	 * @param   boolean  $cache   If true, cache the output
	 * @param   array    $params  Associative array of attributes
	 *
	 * @return  The rendered data
	 *
	 * @since   11.1
	 */
	public function render($cache = false, $params = array())
	{
		return '';
	}
}
