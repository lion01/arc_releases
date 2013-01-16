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
 * Core Admin Synch Controller
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Core
 * @since      1.6
 */
class CoreAdminControllerSynch extends CoreAdminController
{
	/**
	 * Provides the synch controller class
	 */
	function __construct()
	{
		parent::__construct();
		$this->registerTask( 'upload_page', 'uploadPage' );
		$this->registerTask( 'upload_files', 'uploadFiles' );
	}
	
	function display()
	{
		global $mainframe;
		$model = &$this->getModel( 'synch' );
		$view = &$this->getView( 'synch', 'html' );
		$view->setModel( $model, true );
		
		switch( JRequest::getVar('scope', 'batches') ) {
		case( 'batches' ):
			$view->displayBatches();
			break;
		
		case( 'batch' ):
			$model->setBatch( JRequest::getVar( 'batchId' ) );
			$view->displayBatch();
			break;
		}
	}
	
	function remove()
	{
		global $mainframe;
		$model = &$this->getModel( 'synch' );
		$view = &$this->getView( 'synch', 'html' );
		$view->setModel( $model, true );
		
		$ids = array_keys( JRequest::getVar('eid') );
		$remove = $model->deleteBatches( $ids );
		
		if( $remove['success'] ) {
			$mainframe->enqueueMessage( 'Jobs successfully removed' );
		}
		else {
			// deal with db deletion outcome
			if( $remove['db'] ) {
				$mainframe->enqueueMessage( 'Jobs successfully removed' );
			}
			else {
				$mainframe->enqueueMessage( 'There was a problem removing the jobs, please try again', 'error' );
			}
			// deal with file deletion outcome
			if( $remove['files'] ) {
				$mainframe->enqueueMessage( 'The associated files were successfully deleted' );
			}
			else {
				$mainframe->enqueueMessage( 'Some associated files may not have been deleted', 'error' );
			}
		}
		
		$this->display();
	}
	
	function import()
	{
		global $mainframe;
		$model = &$this->getModel( 'synch' );
		$view = &$this->getView( 'synch', 'html' );
		$view->setModel( $model, true );
		
		$ids = array_keys( JRequest::getVar('eid') );
		$save = $model->importBatches( $ids );
		
		if( $save ) {
			$mainframe->enqueueMessage( 'Jobs successfully imported' );
		}
		else {
			$mainframe->enqueueMessage( 'There was a problem importing the jobs, please try again', 'error' );
		}
		
		$this->display();
	}
	
	/**
	 * Attempt to import all current batches
	 * Called directly by the arc_process_jobs.sh bash script
	 */
	function importAll()
	{
		$model = &$this->getModel( 'synch' );
		$allBatchIds = array_keys( $model->getBatches() );
		$model->importBatches( $allBatchIds );
	}
	
	/**
	 * Collect checked batches and show the file upload page for their respective jobs
	 */
	function uploadPage()
	{
		global $mainframe;
		$model = &$this->getModel( 'synch' );
		$view = &$this->getView( 'synch', 'html' );
		$view->setModel( $model, true );
		
		$ids = array_keys( JRequest::getVar('eid') );
		$model->setBatches( $ids );
		
		$view->uploadPage();
	}
	
	/**
	 * Retrieve uploaded files, store them then convert to required format
	 */
	function uploadFiles()
	{
		global $mainframe;
		$model = &$this->getModel( 'synch' );
		
		$batchIds = array();
		$dirs = array();
		$jobFiles = array();
		
		foreach( $_FILES as $fileInput=>$fileInfo ) {
			$batchJobArray = explode( '_', str_replace('filename_', '', $fileInput) );
			$batchIds[] = $batchJobArray[0];
			$jobId = $batchJobArray[1];
			$dirReady = false;
			ApotheosisData::_( 'core.setJobTaken', $jobId );
			
			// if file was found and non-zero in size then proceed
			if( ($fileInfo['error'] == 0) && ($fileInfo['size'] > 0) ) {
				
				// prepare the directory to store the cleaned file
				$dirs[$jobId] = ApotheosisData::_( 'core.dataPath', 'core', $jobId );
				
				// if we get a directory we should empty it in case we used it before
				$dirReady = $dirs[$jobId] && ApotheosisData::_( 'core.emptyDir', $dirs[$jobId] );
				
				// if the directory is ready then process and save the uploaded file
				if( $dirReady ) {
					
					// get the uploaded file and clean it
					$rawFile = $fileInfo['tmp_name'];
					$rawContents = ApotheosisLib::file_get_contents_utf8( $rawFile );
					$cleaned = str_replace( array("\r\n", "\r"), "\n", $rawContents );
					
					// write the cleaned file into its new home
					if( file_put_contents($dirs[$jobId].DS.$fileInfo['name'], $cleaned) !== false ) {
						$jobFiles[$jobId] = $dirs[$jobId].DS.$fileInfo['name'];
					}
					else {
						$mainframe->enqueueMessage( JText::_('The uploaded file').' '.$fileInfo['name'].' '.JText::_('could not be saved.'), 'error' );
					}
				}
				else {
					$mainframe->enqueueMessage( JText::_('Could not create a directory for job number:').' '.$jobId, 'error' );
				}
			}
			elseif( $fileInfo['name'] == '' ) {
				$mainframe->enqueueMessage( JText::_( 'No file provided; skipping.' ) );
			}
			elseif( $fileInfo['error'] > 0 ) {
				$mainframe->enqueueMessage( JText::_('Upload of file').' '.$fileInfo['name'].' '.JText::_('failed with error: ').$fileInfo['error'], 'error' );
			}
			elseif( $fileInfo['size'] == 0 ) {
				$mainframe->enqueueMessage( JText::_('The uploaded file').' '.$fileInfo['name'].' '.JText::_('was empty.'), 'error' );
			}
			
			// check if there is a valid directory and a file in it
			// if not mark job as untaken as delete folder
			if( !$dirReady && (ApotheosisData::_('core.getJobFiles', $jobId) === '') ) {
				ApotheosisData::_( 'core.setJobUnready', $jobId );
				ApotheosisData::_( 'core.setJobUntaken', $jobId );
				ApotheosisData::_( 'core.emptyDir', ApotheosisData::_('core.dataPath', 'core', $jobId), true );
				continue;
			}
		}
		
		// proceed with conversion if we have new files to process
		if( !empty($jobFiles) ) {
			$convert = $model->convertCSV( $batchIds, $jobFiles );
			if( !empty($convert) ) {
				foreach( $convert as $error=>$jobIdArray ) {
					if( $error == 'headers' ) {
						$errText = JText::_( 'did not contain the correct column headers' );
					}
					elseif( $error == 'open' ) {
						$errText = JText::_( 'could not be converted, could not open a new file resource' );
					}
					elseif( $error == 'write' ) {
						$errText = JText::_( 'could not be converted, an error occurred writing to the file resource' );
					}
					elseif( $error == 'rename' ) {
						$errText = JText::_( 'could not be renamed to data.xml' );
					}
					
					foreach( $jobIdArray as $jobId ) {
						$filename= substr($jobFiles[$jobId], (strrpos($jobFiles[$jobId], '/') + 1));
						$mainframe->enqueueMessage( JText::_('File').' '.$filename.' '.$errText, 'error' );
					}
				}
			}
		}
		
		$mainframe->enqueueMessage( JText::_('File upload and appropriate conversions complete') );
		JRequest::setVar( 'eid', array_flip($batchIds) );
		$this->uploadPage();
	}
}
?>