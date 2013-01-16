<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Document
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework
defined('_JEXEC') or die('Restricted access');

require_once( JPATH_SITE.DS.'libraries'.DS.'joomla'.DS.'document'.DS.'apothpdf'.DS.'tcpdf_config.php' );
jimport('tcpdf.tcpdf');

/**
 * DocumentPDF class, provides an easy interface to parse and display a pdf document
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Document
 * @since		1.5
 */
class JDocumentApothPDF extends JDocument
{
	var $_engine	= null;

	var $_name		= 'joomla';

	var $_header	= null;

	var $_margin_header = 5;
	var $_margin_footer = 10;
	var $_margin_top    = 27;
	var $_margin_bottom = 25;
	var $_margin_left   = 15;
	var $_margin_right  = 15;
	var $_image_scale   = 1;

	/**
	 * Class constructore
	 *
	 * @access protected
	 * @param	array	$options Associative array of options
	 */
	function __construct($options = array())
	{
		parent::__construct($options);

		//set mime type
		$this->_mime = 'application/pdf';

		//set document type
		$this->_type = 'pdf';

		// Default settings are a portrait layout with an A4 configuration using millimeters as units
		$this->_engine = new TCPDF();

		//set margins
		$this->_engine->SetMargins($this->_margin_left, $this->_margin_top, $this->_margin_right);
		//set auto page breaks
		$this->_engine->SetAutoPageBreak(TRUE, $this->_margin_bottom);
		$this->_engine->SetHeaderMargin($this->_margin_header);
		$this->_engine->SetFooterMargin($this->_margin_footer);
		$this->_engine->setImageScale($this->_image_scale);
	}

	/**
	 * Sets the document name
	 *
	 * @param   string   $name	Document name
	 * @access  public
	 * @return  void
	 */
	function setName($name = 'joomla') {
		$this->_name = $name;
	}

	/**
	 * Returns the document name
	 *
	 * @access public
	 * @return string
	 */
	function getName() {
		return $this->_name;
	}

	 /**
	 * Sets the document header string
	 *
	 * @param   string   $text	Document header string
	 * @access  public
	 * @return  void
	 */
	function setHeader($text) {
		$this->_header = $text;
	}

	/**
	 * Returns the document header string
	 *
	 * @access public
	 * @return string
	 */
	function getHeader() {
		return $this->_header;
	}
	
	function startDoc()
	{
		// Initialize PDF Document
		$pdf = &$this->_engine;

		// Set PDF Metadata
		$pdf->SetCreator( $this->getGenerator() );
		$pdf->SetTitle( $this->getTitle() );
		$pdf->header_title = $this->getTitle();
		$pdf->SetSubject( $this->getDescription() );
		$pdf->SetKeywords( $this->getMetaData('keywords') );

		// Set PDF Header data
		$pdf->setHeaderData( '', 0, $this->getTitle(), $this->getHeader() );

		$pdf->AliasNbPages();
		$pdf->AddPage();
		if( $this->getBuffer() != '' ) {
			$pdf->WriteHTML( $this->getBuffer() );
		}
		ob_start();
	}
	
	function endDoc()
	{
		// Build the PDF Document string from the output buffer
		$pdf = &$this->_engine;
		$pdf->WriteHTML( ob_get_clean() );
	}
	
	function endSection()
	{
		$pdf = &$this->_engine;
		$pdf->WriteHTML( ob_get_clean() );
	}
	
	function startSection()
	{
		ob_start();
	}
	
	function getFont()
	{
		$lang = &JFactory::getLanguage();
		$font = $lang->getPdfFontName();
		$font = ($font) ? $font : 'vera';
		return $font;
	}
	
	function &getEngine()
	{
		return $this->_engine;
	}
	
	/**
	 * Render the document.
	 *
	 * @access public
	 * @param boolean 	$cache		If true, cache the output
	 * @param array		$params		Associative array of attributes
	 * @return 	The rendered data
	 */
	function render( $cache = false, $params = array())
	{
		$pdf = &$this->_engine;
		
		// Set document type headers
		parent::render();
		JResponse::setHeader('Content-disposition', 'inline; filename="'.$this->getName().'.pdf"', true);

		// Close and output PDF document
		$data = $pdf->Output('', 'S');
		return $data;
	}
	
	/**
	 * Wrapper for writeHtml() that adds a page break first if needed
	 * Also overcomes lack of pdf transactions in this version of TCPDF (4.5.*)
	 * 
	 * @param string $content  String of HTML to be written into pdf
	 */
	function pdfWriteHtmlWithPageBreak( $content )
	{
		// Store current object
		$tmpPdf = clone $this->_engine;
		
		// Store starting values
		$startY = $tmpPdf->GetY();
		$startPage = $tmpPdf->getPage();
		
		// Call your printing functions with your parameters
		$tmpPdf->writeHtml( $content );
		
		// Get the new Y
		$endY = $tmpPdf->GetY();
		$endPage = $tmpPdf->getPage();
		
		// Calculate height
		$height = 0;
		if( $endPage == $startPage ) {
			$height = $endY - $startY;
		}
		else {
			for( $page = $startPage; $page <= $endPage; ++$page ) {
				$tmpPdf->setPage( $page );
				if( $page == $startPage ) {
					// First page
					$height += ( $tmpPdf->h - $startY - $tmpPdf->bMargin );
				}
				elseif( $page == $endPage ) {
					// Last page
					$height += ( $endY - $tmpPdf->tMargin );
				}
				else {
					$height += ( $tmpPdf->h - $tmpPdf->tMargin - $tmpPdf->bMargin );
				}
			}
		}
		
		// Add pagebreak if needed then add content
		$this->_engine->checkPageBreak( $height );
		$this->_engine->writeHtml( $content );
	}
}
?>