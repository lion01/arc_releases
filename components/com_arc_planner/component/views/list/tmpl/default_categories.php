<?php
/**
 * @package     Arc
 * @subpackage  Planner
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<style type="text/css">

.list_table_wrapper {
	overflow: auto;
	margin-bottom: 1em;
}

.list_category_title {
	font-weight: bold;
	padding: 0px 2px;
}

.list_category_table {
	padding: 2px 1px;
}

.list_category_table table {
	border-collapse: collapse;
}

.list_category_table th {
	padding: 1px 3px;
	border: solid 1px grey;
	vertical-align: middle;
	color: black;
}

.list_category_table td {
	padding: 1px 3px;
	border: solid 1px grey;
	vertical-align: middle;
	min-width: 6em;
	white-space: nowrap;
}

.list_category_table ul {
	list-style: inside;
	margin: 0px;
	padding: 0px;
}

.list_category_table img {
	vertical-align: middle;
	margin: 0px;
	padding: 0px;
}

<?php JHTML::_('behavior.modal'); ?>
</style>
<div class="list_table_wrapper">
	<div class="list_category_title">
		<?php echo $this->_curCatTitle; ?> (<?php echo $this->_curCatId; ?>)
	</div>
	<div class="list_category_table">
		<table>
			<tr>
				<?php
				// table heading row
					foreach( $this->_curColDefs as $colId=>$colInfo ) {
						echo '<th>'.$colInfo['col_title'].'</th>';
					}
				?>
			</tr>
			<?php
				// loop through each available row in _grid for this category and retrieve
				// an array of column IDs (index) and which task to show in it (value)
				while( $cur = $this->model->getRow($this->_curCatId) ) {
					
					// find matching column in _tableInfo for this category
					foreach( $this->_curColDefs as $colId=>$colInfo ) {
						// retrieve task depth for this column
						$taskDepth = $colInfo['task_depth'];
						// by default don't track this column for row span updating
						$setColumnSpanInc[$colId] = false;
						// clean out last value before rebuilding
						unset( $tmp['data'][$colId] );
						
						// if we have data from _grid
						if( isset($cur[$colId]) ) {
							// retrieve this cells data
							$data = $cur[$colId];
							
							// set last entry for this task depth if not already set
							if( !isset($last[$taskDepth]) ) {
								$last[$taskDepth] = $data;
							}
							// if value has changed from that stored in $last for this task depth
							// then empty all values for deeper task depths and set $last to new value
							// also unset bullet adding for deeper subtasks
							elseif( $last[$taskDepth] != $data ) {
								// unset this and all child depths
								$key = $taskDepth;
								while( isset($last[$key]) ) {
									unset( $last[$key++] );
								}
								// reset this task depth
								$last[$taskDepth] = $data;
								
								// stop adding bullets for all deeper task depths
								$key2 = ($taskDepth + 1);
								for( $i = $key2; $i <= $this->_maxDepth; $i++ ) {
									unset( $setBulleting[$i] );
								}
							}
							
							// if this column is set to span rows
							if( $colInfo['type'] == 'span' ) {
								// set data
								$tmp['data'][$colId] = $data;
								// start counting rows to span
								$tmp['rows'][$colId] = 1;
								// set this column as one to track the row spans for
								$setColumnSpanInc[$colId] = true;
							}
							// or if this column is set to bullet rows
							elseif( $colInfo['type'] == 'bullet' ) {
								// if we are already collecting bullets for this task depth
								if( $setBulleting[$taskDepth][$colId] ) {
									// add data to the cell in the collecting row
									$tableData[$currentBulletAddRow[$colId]]['data'][$colId][] = $data;
									// set null for the cell in this row
									$tmp['data'][$colId] = null;
									// and then rowspan over this cell
									$tableData[$currentBulletAddRow[$colId]]['rows'][$colId]++;
								}
								// if we are not already collecting bullets start here
								else {
									// set data for this cell
									$tmp['data'][$colId][] = $data;
									// start counting rows to span
									$tmp['rows'][$colId] = 1;
									// set this column as one to add subsequent bullets to at this task depth
									$setBulleting[$taskDepth][$colId] = true;
								}
							}
							// or if this column is set to duplicate rows
							elseif( $colInfo['type'] == 'duplicate' ) {
								// set data
								$tmp['data'][$colId] = $data;
							}
						}
						// no data from _grid so proceed depending on column type:
						else {
							
							// if we have a previous value for this task depth
							if( isset($last[$taskDepth]) ) {
								
								// if we are row spanning
								if( $colInfo['type'] == 'span' ) {
									// set no output
									$tmp['data'][$colId] = null;
									// increment row span count for this repeating data
									$tableData[$currentSpanIncRow[$colId]]['rows'][$colId]++;
								}
								// or if we are bulleting but have run out of data
								elseif( $colInfo['type'] == 'bullet' ) {
									// set no output
									$tmp['data'][$colId] = null;
									// and then rowspan over this cell
									$tableData[$currentBulletAddRow[$colId]]['rows'][$colId]++;
								}
								// otherwise we are duplicating
								elseif( $colInfo['type'] == 'duplicate' ) {
									// repeat last value for this task depth
									$tmp['data'][$colId] = $last[$taskDepth];
								}
							}
							// if no previous value for this task depth
							else {
								// no previous data and nothing from _grid so set a blank
								$tmp['data'][$colId] = '--';
								
								// if we are row spanning
								if( $colInfo['type'] == 'span' || $colInfo['type'] == 'bullet' ) {
									// start counting rows to span
									$tmp['rows'][$colId] = 1;
									// set this column as one to track the row spans for
									$setColumnSpanInc[$colId] = true;
								}
							}
						}
					}
					// add this row of data to table output array
					$tableData[] = $tmp;
					
					// check if we have set any columns to start counting row span data
					foreach( $setColumnSpanInc as $colId=>$increment ) {
						if( $increment ) {
							// if so keep a track of the row we need to count from for this column
							end( $tableData );
							$currentSpanIncRow[$colId] = key( $tableData );
						}
					}
					
					// check if we are starting to collect bullets at this task depth
					foreach( $setBulleting as $tDepth=>$colArray ) {
						foreach( $colArray as $colId=>$bulleting ) {
							if( $bulleting ) {
								// if so keep track of the row we need to add further bullets to
								end( $tableData );
								$currentBulletAddRow[$colId] = key( $tableData );
							}
						}
					}
				}
//				ini_set('xdebug.var_display_max_depth', 10 );var_dump_pre( $tableData , '$tableData:' ); // *** remove
				// loop through final table array and output each row
				foreach( $tableData as $row=>$rowArray ) {
					echo '<tr>';
					
					// loop through each column of data for each row and output it
					foreach( $rowArray['data'] as $colId=>$colData ) {
						// clean last value set
						unset( $value );
						
						// if we have data for this cell output it
						if( isset($colData) ) {
							
							// if $colData is not a blank
							if( is_int($colData) || is_array($colData) ) {
								// get the task property we need to retrieve
								$property = $this->_curColDefs[$colId]['property'];
								// generate the getFunction
								$propFunc = 'get'.ucfirst( $property );
								// set a blank suffix by default
								$suffix = '';
								
								switch( $property ) {
								case( 'task_min' ):
								case( 'task_max' ):
								case( 'person_min' ):
								case( 'person_max' ):
									$task = &$this->model->getTask( $colData );
									$d = $task->getDueDates();
									if( !is_null($d[$property]) ) {
										$value = ApotheosisLibParent::arcDateTime( $d[$property] );
									}
									else {
										$value = '--';
									}
									break;
								
								case( 'title' ):
									// get the title of the single task
									if( !is_array($colData) ) {
										$value = $this->_getTaskLink( $colData );
									}
									// or get the title of the array of tasks
									else {
										$value = '<ul>';
										foreach( $colData as $taskId ) {
											$value .= '<li>'.$this->_getTaskLink( $taskId ).'</li>';
										}
										$value .= '</ul>';
									}
									break;
								
								case( 'progress' ):
									$task = &$this->model->getTask( $colData );
									$value = $task->$propFunc();
									$suffix = '%';
									break;
								
								case( 'duration' ):
									$task = &$this->model->getTask( $colData );
									$value = $task->$propFunc();
									$suffix = ' hours';
									break;
								
								case( 'assignees' ):
									$task = &$this->model->getTask( $colData );
									$groups = &$task->getGroups();
									$value = '<ul>';
									foreach( $groups as $groupId=>$group ) {
										$assignees = $group->getPeopleInRole( 'assignee' );
										foreach( $assignees as $arcId=>$personObj ) {
											$value = $value.'<li>'.$personObj->displayname.'</li>';
										}
									}
									$value = $value.'</ul>';
									if( empty($value) ) {
										$value = '--';
									}
									break;
								
								case( 'evidence' ):
									$task = &$this->model->getTask( $colData );
									$groups = &$task->getGroups();
									$noData = true;
									$value = '<ul>';
									foreach( $groups as $groupId=>$group ) {
										$updates = &$groups[$groupId]->getUpdates();
										foreach( $updates as $update ) {
											$evidenceArray = $update->getEvidence();
											foreach( $evidenceArray as $evId=>$evidence ) {
												$value = $value.'<li>'.$evidence.'</li>';
												$noData = false;
											}
										}
									}
									$value = $value.'</ul>';
									if( $noData ) {
										$value = '--';
									}
									break;
								
								default:
									// get the value of the single task property
									if( !is_array($colData) ) {
										$task = &$this->model->getTask( $colData );
										if( method_exists($task, $propFunc) ) {
											$value = $task->$propFunc();
										}
										else {
											$value = '! badFunc !';
										}
									}
									// or get the value of the array of tasks properties
									else {
										$value = '<ul>';
										foreach( $colData as $taskId ) {
											if( method_exists($task, $propFunc) ) {
												$task = &$this->model->getTask( $taskId );
												$value = $value.'<li>'.$task->$propFunc().'</li>';
											}
											else {
												$value = '! badArrayFunc !';
											}
										}
										$value = $value.'</ul>';
									}
									break;
								}
							}
							// if $colData is a blank just use it
							else {
							$value = $colData;
							}
							// display the html, rowspanning and centering where necessary
							$centre = ( ($value === '--') ? 'align="center"' : '' );
							echo ( ($rowArray['rows'][$colId] > 1) ? '<td rowspan="'.$rowArray['rows'][$colId].'" '.$centre.'>' : '<td '.$centre.'>' );
							echo $value.$suffix;
							echo '</td>';
						}
					}
					echo '</tr>';
				}
			?>
		</table>
	</div>
</div>