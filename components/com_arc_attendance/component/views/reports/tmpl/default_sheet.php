<?php
/**
 * @package     Arc
 * @subpackage  Attendance
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

$tabEnabled = (bool)ApotheosisLibAcl::getUserLinkAllowed('att_reports_tab', array('attendance.sheet'=>$this->sheetId, 'attendance.tab'=>null));
$drillEnabled = (bool)ApotheosisLibAcl::getUserLinkAllowed('att_reports_drilldown', array('attendance.sheet'=>$this->sheetId, 'attendance.scope'=>null));

// generate the html for the tab rows
$tabCount = $this->model->getHeadRowCount( $this->sheetId );
$tabRows = '';
if( $tabCount > 2 ) {
	$noTabs = false;
	$this->model->getHeadRow( $this->sheetId ); // discard the first tab row which is just "everything" if we have more useful info to show
	$tabCount -= 2; // only consider 2nd and subsequent remaining head rows for inclusion as tabs
}
else {
	$noTabs = true;
	$tabCount -= 1; // only consider 2nd and subsequent head rows for inclusion as tabs
}
$firstPass = true;
for( $i = $tabCount; $i > 0; $i-- ) {
	$heads = $this->model->getHeadRow( $this->sheetId );
	$labelTmp = reset( $heads );
	$rowLabel = $labelTmp['row_label'];
	$tabRows .= '<tr>';
	if( $this->edits && $firstPass ) {
		if( $this->editShown ) {
			$tabRows .= '<th class="edit_column" rowspan="'.$tabCount.'"><input type="submit" name="submit" value="Save" /></th>';
		}
		else {
			$tabRows .= '<th class="edit_column" rowspan="'.$tabCount.'"><input type="submit" name="submit" value="Edit" /></th>';
		}
	}
	$firstPass = false;
	$tabRows .= '<th>'.$rowLabel.'</th>';
	if( $noTabs ) {
		$tabRows .= '<th colspan="~colspan~"><ul class="tab_row">'."\n";
	}
	else {
		$tabRows .= '<th colspan="~colspan~"><ul class="tab_row">'."\n";
	}
	$cur = reset($heads);
	$next = next($heads);
	while( $cur !== false ) {
		$tabClass = 'tab';
		$safety = 0;
		if( $cur['enabled'] ) {
			// test to see if this tab is active
			if( $cur['active'] ) {
				$tabClass .= '_active';
			}
			$title = $cur['text_full'] ? $cur['text_full'] : $cur['text']; 
			$tabRows .= '<li class="arcTip '.$tabClass.'" title="'.$title.'">'
				.($tabEnabled ? '<a href="'.ApotheosisLib::getActionLinkByName( 'att_reports_tab', array('attendance.sheet'=>$this->sheetId, 'attendance.tab'=>$i.'_'.$cur['id']) ).'">' : '' )
				.$cur['text']
				.($tabEnabled ? '</a>' : '')
				.'</li>'."\n";
		}
		elseif( $noTabs ) {
			$tabRows .= '<li>'.$cur['text'].'</li>'."\n";
		}
		else {
			$tabClass .= '_inactive';
			$tabRows .= '<li class="'.$tabClass.'">'.$cur['text'].'</li>'."\n";
		}
		$cur = $next;
		$next = next($heads);
		$safety++;
		if( $safety > 200) { die('ouch!'); }
	}
	$tabRows .= '</ul></th></tr>'."\n";
}

// now generate the html for the section row
$this->sectionRow = '';
$sections = 0;
$heads = $this->model->getHeadRow( $this->sheetId );

switch( $this->get('MarkSheetType') ) {
case('full'):
	$heads['summary'] = array('colid'=>'summary', 'text'=>'Summary'); // To make the summary column appear
	break;
case('summary'):
default:
	break;
}

foreach( $heads as $id=>$info ) {
	$sections++;
	$this->sectionRow .= '<th>'.$info['text'].'</th>'."\n";
}

$tabRows = str_replace( '~colspan~', $sections, $tabRows );

// finally generate the html for the toggles
$this->toggles = '';
if( ApotheosisLibAcl::getUserLinkAllowed('att_reports_toggle', array()) ) {
	$toggleData = $this->model->getToggles( $this->sheetId );
	foreach( $toggleData as $k=>$v ) {
		if( is_null($v) ) {
			$this->toggles .= '<span class="toggle_disabled arcTip" title="::'.ucfirst($k).'">'
				.strtoupper(substr($k, 0, 1))
				.'</span> ';
		}
		else {
			$this->toggles .= '<a class="'.($v ? 'toggle_active arcTip' : 'toggle arcTip').'" title="::'.ucfirst($k).'" href="'.ApotheosisLib::getActionLinkByName( 'att_reports_toggle', array('attendance.sheet'=>$this->sheetId, 'attendance.toggle'=>$k) ).'">
				'.strtoupper(substr($k, 0, 1)).'
				</a>';
		}
	}
}

?>
<div id="sheet_div">
<?php
if( ($l = ApotheosisLibAcl::getUserLinkAllowed( 'att_reports_csv', array('attendance.sheet'=>$this->sheetId) ) ) !== false ) {
	$links[] = '<a href="'.$l.'">Get as csv</a>';
}
if( ($l = ApotheosisLibAcl::getUserLinkAllowed( 'att_reports_pdf', array('attendance.sheet'=>$this->sheetId) ) ) !== false ) {
	$links[] = '<a href="'.$l.'">Get as pdf</a>';
}
if( (($l = ApotheosisLibAcl::getUserLinkAllowed( 'att_reports_pdf_compact', array('attendance.sheet'=>$this->sheetId) ) ) !== false) && !($this->get('expandcompact')) ) {
	$links[] = '<a href="'.$l.'">Get as compact pdf</a>';
}
echo implode( ' | ', $links );
?>
	<table id="mark_table">
		<?php echo $tabRows; ?>
		<?php echo $this->loadTemplate( 'toggle_row' ); ?>
		<?php $i = 1; ?>
		<?php while( ($this->row = $this->model->getMarkRow($this->sheetId)) !== false ) : ?>
			<?php
			$this->rowId = $this->row['id'];
			if( ($i++ % 50) == 0 ) {
				echo $this->loadTemplate( 'toggle_row' );
			} ?>
			<tr <?php echo ( !($i%2) ? 'class="oddrow"' : '' ); ?>>
				<?php if( $this->edits && !$this->row['multi'] ) : ?>
					<?php
						$rowColTuple = array();
						foreach( $this->row as $k=>$v ) {
							if( is_array($v) && isset($v['marks']) ) {
								$mark = reset( $v['marks'] );
								$rowColTuple[] = ( array('row'=>$mark['row'], 'col'=>$mark['col']) );
							}
						}
					?>
					<td class="edit_cell">
						<input class="edit_input" type="checkbox" name="rows[<?php echo $this->rowId; ?>]" <?php echo ($this->row['edits'] ? 'checked="checked"' : '' ); ?> />
						<input type="hidden" name="rows_matrix[<?php echo $this->rowId; ?>]" value="<?php echo htmlspecialchars( serialize($rowColTuple) ); ?>" />
					</td>
				<?php elseif( $this->edits && $this->row['multi'] ) :?>
					<td>&nbsp;</td>
				<?php endif; ?>
				<td class="indent_cell">
					<?php
					echo str_repeat('<span class="indent" />', $this->row['depth'])
						.($drillEnabled ? '<a href="'.ApotheosisLib::getActionLinkByName( 'att_reports_drilldown', array('attendance.sheet'=>$this->sheetId, 'attendance.scope'=>$this->rowId) ).'">' : '')
						.( isset($this->row['info']) 
							? '<span class="arcTip" title="'.htmlspecialchars($this->row['info']).'">'.$this->row['name'].'</span>'
							: $this->row['name'] )
						.($drillEnabled ? '</a>' : '');
					unset($this->row['name']);
					?>
				</td>
				<?php
				foreach( $heads as $id=>$info ) {
					$this->colId = $info['colid'];
					$this->_mark = ( isset($this->row[$this->colId]) ? $this->row[$this->colId] : null );
					echo $this->loadTemplate( 'mark' );
				}
				?>
			</tr>
		<?php endwhile; ?>
	</table>
</div>
