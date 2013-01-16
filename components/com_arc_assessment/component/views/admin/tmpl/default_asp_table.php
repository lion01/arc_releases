<?php
/**
 * @package     Arc
 * @subpackage  Assessment
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<table id="aspects_table">
<?php if( $this->leftCol ) : ?>
	<tr>
		<td>
			<input type="checkbox" id="asp_select_all" />
		</td>
	</tr>
	<tr>
		<th>
			Title
		</th>
	</tr>
	<tr>
		<th>
			Short
		</th>
	</tr>
	<tr>
		<th>
			Mark style
		</th>
	</tr>
	<tr>
		<th>
			Display style
		</th>
	</tr>
	<tr>
		<th>
			Boundaries
		</th>
	</tr>
<?php else : ?>
<?php
$doc = &JFactory::getDocument();
$i = 0;
$checkRow = '';
$titleRow = '';
$shortRow = '';
$markStyleRow = '';
$displayStyleRow = '';
$adminRow = '';
foreach( $this->assProps['aspects'] as $id=>$aspData ) {
	$boundaryData = json_decode( $aspData['boundaries'] );
	JRequest::setVar( 'asp['.$id.'][mark_style]', $boundaryData->mark_style );
	JRequest::setVar( 'asp['.$id.'][display_style]', $boundaryData->display_style );
	$checkRow .= "\n".'<td><input type="checkbox" id="aspect_check_'.$id.'" name="aspselect['.$id.']" /></td>';
	$titleRow .= "\n".'<td><input type="text" name="asp['.$id.'][title]" value="'.$aspData['title'].'" /></td>';
	$shortRow .= "\n".'<td><input type="text" name="asp['.$id.'][short]" value="'.$aspData['short'].'" /></td>';
	$markStyleRow .= "\n".'<td>'.JHTML::_( 'arc_assessment.markstyle', 'asp['.$id.'][mark_style]' ).'</td>';
	$displayStyleRow .= "\n".'<td>'.JHTML::_( 'arc_assessment.displaystyle', 'asp['.$id.'][display_style]' ).'</td>';
	$adminRow .= "\n".'<td>'.JHTML::_( 'arc.adminLink', '', 'Edit boundaries for '.$aspData['title'], 'plain', 'id="boundary_edit_'.$id.'"' )
		.'<input type="hidden" id="asp_'.$id.'_boundary_data" name="asp['.$id.'][boundaries]" value="'.htmlspecialchars($aspData['boundaries']).'" /></td>';
	$js = 'controllers['.$i.'] = new objEditControl( '.$i.', '.$id.', '.$aspData['boundaries'].' )';
	$doc->addScriptDeclaration( $js );
	$i++;
}

echo "\n".'<tr>'.$checkRow."\n".'</tr>';
echo "\n".'<tr>'.$titleRow."\n".'</tr>';
echo "\n".'<tr>'.$shortRow."\n".'</tr>';
echo "\n".'<tr>'.$markStyleRow."\n".'</tr>';
echo "\n".'<tr>'.$displayStyleRow."\n".'</tr>';
echo "\n".'<tr>'.$adminRow."\n".'</tr>';
?>
<?php endif; ?>
</table>