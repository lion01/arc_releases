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

// Give us access to the joomla model class
jimport( 'joomla.application.component.model' );
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_arc_core'.DS.'helpers'.DS.'lib_sync.php' );

/**
 * Core Admin Synch Model
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Core
 * @since      1.6
 */
class CoreAdminModelSynch extends ArcAdminModel
{
	function getBatches()
	{
		if( !isset($this->_batches) ) {
			$this->_loadBatches();
		}
		return $this->_batches;
	}
	
	function _loadBatches()
	{
		$this->_batches = ApotheosisData::_( 'core.importBatches' );
	}
	
	function setBatches( $indices )
	{
		$this->_batches = ApotheosisData::_( 'core.importBatches', $indices );
	}
	
	function setBatch( $id )
	{
		$this->_batches = ApotheosisData::_( 'core.importBatches', $id );
	}
	
	function deleteBatches( $indices )
	{
		$this->getBatches();
		$db = &JFactory::getDBO();
		
		foreach( $indices as $index ) {
			$dList[] = $db->Quote( $this->_batches[$index]['id'] );
		}
		
		if( empty($dList) ) {
			$retVal = true;
		}
		else {
			// mark relevant batches and their jobs as "deleted" in the db
			$dList = implode( ', ', $dList );
			$query = 'UPDATE '.$db->nameQuote( '#__apoth_sys_import_batches' )
				."\n".'SET '.$db->nameQuote( 'done' ).' = '.$db->Quote( '-1' )
				."\n".'WHERE '.$db->nameQuote( 'id' ).' IN ('.$dList.')'
				."\n".'  AND '.$db->nameQuote( 'done' ).' = '.$db->Quote( 0 ).';'
				."\n"
				."\n".'UPDATE '.$db->nameQuote( '#__apoth_sys_import_queue' )
				."\n".'SET '.$db->nameQuote( 'taken' ).' = '.$db->Quote( '-1' )
				."\n".'WHERE '.$db->nameQuote( 'batch_id' ).' IN ('.$dList.');';
			$db->setQuery( $query );
			$db->QueryBatch();
			$dbDelete = $db->getErrorMsg() == '';
			
			// delete any files associated with the batches
			$query = 'SELECT '.$db->nameQuote('id')
				."\n".'FROM '.$db->nameQuote('#__apoth_sys_import_queue')
				."\n".'WHERE '.$db->nameQuote('batch_id').' IN ('.$dList.')';
			$db->setQuery( $query );
			$jobIds = $db->loadResultArray();
			
			$filesDelete = true;
			foreach( $jobIds as $jobId ) {
				$filesDelete = $filesDelete && ( ApotheosisData::_('core.cleanJob', array('id'=>$jobId)) !== false );
			}
			$success = $dbDelete && $filesDelete;
			
			$retVal = array( 'success'=>$success, 'db'=>$dbDelete, 'files'=>$filesDelete );
		}
		
		return $retVal;
	}
	
	function importBatches( $indices = null )
	{
		$this->getBatches();
		$db = &JFactory::getDBO();
		
		$bList = array();
		foreach( $indices as $index ) {
			if( isset( $this->_batches[$index] ) ) {
				$bList[] = $this->_batches[$index]['id'];
			}
		}
		$batches = ApotheosisData::_( 'core.importBatchesReady', $bList, true );
		$batches = array_reverse( $batches, true ); // import oldest first
		$todo = count( $batches );
		$done = 0;
		
		foreach( $batches as $bId=>$batch ) {
			// make the callback, providing the path to the data files
			$params = array();
			$pList = explode( "\r\n", $batch['params'] );
			foreach( $pList as $p ) {
				$parts = explode( '=', $p, 2 );
				$params[$parts[0]] = $parts[1];
			}
			
			$result = ArcSync::_( $batch['component'].'.'.$batch['callback'], $params, $batch['jobs'] );
			
			if( $result ) {
				// With everything done, mark this job batch as done...
				ApotheosisData::_( 'core.setBatchDone', $bId );
				foreach( $batch['jobs'] as $jId=>$job ) {
					// ... and remove the data files for the jobs
					ApotheosisData::_( 'core.cleanJob', $job );
				}
				$done++;
			}
		}
		
		return array( 'todo'=>$todo, 'done'=>$done);
	}
	
	function getQueue()
	{
		if( !isset($this->_queue) ) {
			$this->_loadQueue();
		}
		return $this->_queue;
	}
	
	function _loadQueue()
	{
		$this->getBatches();
		$this->_queue = ApotheosisData::_( 'core.importQueue', null, null, array_keys($this->_batches) );
	}
	
	/**
	 * Fetch the CSV column definitions for a given report format
	 * @param string $com  The component responsible for the import
	 * @param string $report  The SIMS report name
	 * @return array  Array of column names as keys, descriptions as values
	 */
	function getCSVcolumns( $com, $report )
	{
		return ArcSync::_( $com.'.CSVcolumns', $report );
	}
	
	function convertCSV( $batchIds, $jobFiles )
	{
		$this->setBatches( array_unique($batchIds) );
		$this->_loadQueue();
		$errors = array();
		
		// convert the files
		foreach( $jobFiles as $jobId=>$filePath ) {
			$intended = dirname( $filePath ).DS.'data.xml';
			
			// if the uploaded file is *.xml then rename (if needed) to data.xml and bypass conversion
			if( substr($filePath, -4) == '.xml' ) {
				
				if( !file_exists($intended) && !rename($filePath, $intended) ) {
					ApotheosisData::_( 'core.setJobUnready', $jobId );
					ApotheosisData::_( 'core.setJobUntaken', $jobId );
					ApotheosisData::_( 'core.emptyDir', ApotheosisData::_('core.dataPath', 'core', $jobId), true );
					$errors['rename'][] = $jobId;
				}
				else {
					ApotheosisData::_( 'core.setJobReady', $jobId, 'csv' );
				}
				
				continue;
			}
			
			// check we have the correct column headers
			$jobCom = $this->_batches[$this->_queue[$jobId]['batch_id']]['component'];
			$report = $this->_queue[$jobId]['call'];
			$formatArray = $this->getCSVcolumns( $jobCom, $report );
			$reqHeaders = array_keys( $formatArray );
			$newKey = false;
			$parts = array();
			
			// get the header row from the CSV
			$file = fopen( $filePath, 'r' );
			$this->headers = fgetcsv( $file, 2048 );
			
			// check we have the correct headers
			$headerCheck = array_diff( $reqHeaders, $this->headers );
			if( !empty($headerCheck) ) {
				ApotheosisData::_( 'core.setJobUnready', $jobId );
				ApotheosisData::_( 'core.setJobUntaken', $jobId );
				ApotheosisData::_( 'core.emptyDir', ApotheosisData::_('core.dataPath', 'core', $jobId), true );
				$errors['headers'][] = $jobId;
				continue;
			}
			
			// rename headers to match SIMS report requirements
			switch( $report ) {
				case( 'arc_people_contacts' ):
				case( 'arc_people_staff' ):
				case( 'arc_people_pupils' ):
				case( 'arc_timetable_patterns' ):
				case( 'arc_timetable_instances' ):
					foreach( $this->headers as $k=>$header ) {
						if( $header == 'Unique ID' ) { $this->headers[$k] = 'primary_id'; }
					}
					break;
					
				case( 'arc_people_relationships' ):
				case( 'arc_course_pastoral' ):
				case( 'arc_timetable_classes' ):
				case( 'arc_student_attendance' ):
					foreach( $this->headers as $k=>$header ) {
						if( $header == 'Unique ID' ) { $this->headers[$k] = 'multiple_id'; }
					}
					break;
					
				case( 'arc_course_curriculum' ):
					$newKey = array_push( $this->headers, 'multiple_id' ) - 1;
					$parts = array( array_search('Unique Subject ID', $this->headers), array_search('Unique Class ID', $this->headers) );
					break;
					
				case( 'arc_timetable_members' ):
					$newKey = array_push( $this->headers, 'multiple_id' ) - 1;
					$parts = array( array_search('Unique Person ID', $this->headers), array_search('Unique Class ID', $this->headers) );
					break;
			}
			
			// xml file start
			$xmlFile = fopen( $intended, 'w' );
			if( $xmlFile ) {
				fwrite( $xmlFile, '<SuperStarReport>' );
				
				// loop through non-header rows and construct xml as required
				$writeGood = true;
				while( ($data = fgetcsv($file, 2048)) !== false ) {
					if( (!is_null($data)) && (implode('', $data) != '') ) {
						$recordStr = '<Record>';
						
						foreach( $data as $k=>$info ) {
							// only attempt to add element if it is not part of a composite we will create later
							if( array_search($k, $parts) === false ) {
								$recordStr .= $this->_addXmlElement( $k, $info );
							}
						}
						
						// process and add any composites
						if( $newKey ) {
							$info = '';
							$partsOk = true;
							
							// loop through just the columns we need to make the composite
							foreach( $parts as $k=>$compKey ) {
								
								// check the incoming data is valid
								if( ($data[$compKey] != '') && ($data[$compKey] != ' ') ) {
									
									// extra check for horrible composite key requirement
									if( ($report == 'arc_timetable_members') && ($k == 1) ) {
										$partStrings[$k] = $data[$parts[0]].$data[$compKey];
									}
									else {
										$partStrings[$k] = $data[$compKey];
									}
								}
								else {
									$partsOk = false;
									break;
								}
							}
							
							// if composite is good then add it as an xml element
							if( $partsOk ) {
								$info = implode( ',', $partStrings );
								$recordStr .= $this->_addXmlElement( $newKey, $info );
							}
						}
						$recordStr .= '</Record>';
						
						// write record string to xml file resource and check it worked
						$recordLen = strlen( $recordStr );
						$recordWritten = fwrite( $xmlFile, $recordStr );
						if( $recordWritten != $recordLen ) {
							$writeGood = false;
							break;
						}
					}
				}
				
				// finished looping through non-header rows, check if we should continue
				if( $writeGood ) {
					fwrite( $xmlFile, '</SuperStarReport>' );
					fclose( $xmlFile );
					ApotheosisData::_( 'core.setJobReady', $jobId, 'csv' );
				}
				else {
					$errors['write'][] = $jobId;
					ApotheosisData::_( 'core.setJobUnready', $jobId );
					ApotheosisData::_( 'core.setJobUntaken', $jobId );
					fclose( $xmlFile );
					unlink( $intended );
				}
			}
			else {
				$errors['open'][] = $jobId;
				ApotheosisData::_( 'core.setJobUnready', $jobId );
				ApotheosisData::_( 'core.setJobUntaken', $jobId );
			}
			
			// close source file handle then delete it
			fclose( $file );
			unlink( $filePath );
		}
		
		return $errors;
	}
	
	/**
	 * Helper function for adding elements to the xml file during conversion
	 * @param string $k  Numeric key of the array $this->headers
	 * @param string $info  The value to be contained within the element
	 * @return string $elementStr  The element string
	 */
	function _addXmlElement( $k, $info )
	{
		$elementStr = '';
		
		if( ($info != '') && ($info != ' ') ) {
			$element = str_replace( ' ', '_x0020_', $this->headers[$k] );
			$element = str_replace( '(', '_x0028_', $element );
			$element = str_replace( ')', '_x0029_', $element );
			$element = str_replace( '/', '_x002F_', $element );
			
			// add the required 'extra' but unused composite value part
			// just to allow our importers to split on the expected comma
			if( $this->headers[$k] == 'multiple_id' ) {
				$info .= ',1'; 
			}
			
			// make the info xml safe
			$info = htmlspecialchars( $info );
			
			$elementStr = '<'.$element.'>';
			$elementStr .= $info;
			$elementStr .= '</'.$element.'>';
		}
		
		return $elementStr;
	}
}