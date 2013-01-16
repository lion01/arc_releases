<?php
/**
 * @package     Arc
 * @subpackage  Report
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<h3><?php echo $this->groupName.': '.($this->enabled ? 'Adjust field styles' : 'Pre-set field styles'); ?></h3>

<form method="post" action="<?php echo $this->link; ?>">
<table>
<tr>
	<th>Field name</th>
	<th>Title</th>
	<th>Lookup type</th>
	<th>Lookup ids</th>
	<th>Lookup start</th>
	<th>Lookup end</th>
</tr>
<?php foreach( $this->fields as $f ) :
	if( strtolower(get_class($f)) != 'apothfieldhidden' ) :
		$fName = $f->getName();
		$isLookup = method_exists( $f, 'getLookupType' );
		if( $isLookup ) {
			$dates = $f->getLookupDates();
			$startDate = $dates['start_date'];
			$endDate   = $dates['end_date'];
		}
		else {
			$startDate = '--';
			$endDate   = '--';
		}
		if( $this->enabled ) :
			$id = htmlspecialchars($f->getName());
			$inputs['name'] = $f->getName();
			$inputs['title'] = '<input type="text" id="'.$id.'[title]" name="'.$id.'[title]" value="'.$f->getTitle().'" />';
			if( $isLookup ) {
				$types = $f->getLookupTypes();
				$inputs['lookupTypes'] = JHTML::_( 'select.genericlist', $types, $id.'[lookup_type]', '', 'type', 'title', $f->getLookupType() );
				$inputs['lookupIds']   = '<input type="text" id="'.$id.'[lookup_id]" name="'.$id.'[lookup_id]" value="'.htmlspecialchars( implode(';', $f->getLookupIds()) ).'" />';
				$inputs['startDate']   = JHTML::_( 'calendar', $startDate, $id.'[start_date]', $id.'[start_date]' );
				$inputs['endDate']     = JHTML::_( 'calendar', $endDate,   $id.'[end_date]',   $id.'[end_date]' );
			}
			else {
				$inputs['lookupTypes'] = '--';
				$inputs['lookupIds']   = '--';
				$inputs['startDate']   = $startDate;
				$inputs['endDate']     = $endDate;
			}
			
			?>
			<tr>
				<td><?php echo $inputs['name']; ?></td>
				<td><?php echo $inputs['title']; ?></td>
				<td><?php echo $inputs['lookupTypes']; ?></td>
				<td><?php echo $inputs['lookupIds']; ?></td>
				<td><?php echo $inputs['startDate']; ?></td>
				<td><?php echo $inputs['endDate']; ?></td>
			<tr>
		<?php else : ?>
			<tr>
				<td><?php echo $f->getName(); ?></td>
				<td><?php echo $f->getTitle(); ?></td>
				<td><?php echo ( $isLookup ? $f->getLookupType() : '--' ); ?></td>
				<td><?php echo ( $isLookup ? htmlspecialchars( implode(', ', $f->getLookupIds()) ) : '--' ); ?></td>
				<td><?php echo htmlspecialchars( $startDate ); ?>
				<td><?php echo htmlspecialchars( $endDate ); ?>
			<tr>
		<?php endif; ?>
	<?php endif; ?>
<?php endforeach; ?>
</table>
<?php if( $this->enabled ) : ?>
	<input type="hidden" name="task" value="SetFieldStyle" />
	
	Apply within this group on:<br />
	<input type="radio" name="_tmpl" id="_tmpl_1" value="general" checked="checked" />
	<label for="_tmpl_1">Any templates</label><br />
	<input type="radio" name="_tmpl" id="_tmpl_2" value="specific"  />
	<label for="_tmpl_2">Current template only</label>
	
	<input type="submit" value="Save" />
<? endif; ?>
</form>