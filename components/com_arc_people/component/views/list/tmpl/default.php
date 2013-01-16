<?php
/**
 * @package     Arc
 * @subpackage  People
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<h3>People</h3>
<?php echo $this->loadTemplate( 'search' ); ?>
<hr />
<?php
	if( !empty($this->people) ) :
	if( ($l = ApotheosisLibAcl::getUserLinkAllowed('ppl_lists_csv') ) !== false ) {
		echo '<a href="'.$l.'">Get as csv</a>';
	}
?>
	<table>
		<tr>
			<th><?php echo JText::_( 'Arc ID' ); ?></th>
			<th><?php echo JText::_( 'First Name' ); ?></th>
			<th><?php echo JText::_( 'Last Name' ); ?></th>
			<th><?php echo JText::_( 'Date of Birth' ); ?></th>
			<th><?php echo JText::_( 'Sex' ); ?></th>
			<?php if( $this->rels ): ?>
				<th><?php echo JText::_( 'Relationship' ); ?></th>
				<th><?php echo JText::_( 'Full Contact Rights' ); ?></th>
			<?php endif; ?>
		</tr>
		<?php
		$oddrow = false;
		foreach( $this->people as $pObj ) : ?>
			<tr<?php echo ( ($oddrow) ? ' class="oddrow"' : '' ); $oddrow = !$oddrow; ?>>
				<td><?php echo $pObj->getDatum( 'id' ); ?></td>
				<td><?php echo $pObj->getDatum( 'firstname' ); ?></td>
				<td><?php echo $pObj->getDatum( 'surname' ); ?></td>
				<td><?php echo $pObj->getDatum( 'dob' ); ?></td>
				<td><?php echo $pObj->getDatum( 'gender' ); ?></td>
			<?php if( $this->rels ): ?>
				<?php
					if( is_null($pObj->isFullContact()) ) {
						$fullContact = '?';
					}
					elseif( !$pObj->isFullContact() ) {
						$fullContact = 'No';
					}
					else {
						$fullContact = 'Yes';
					}
				?>
				<td><?php echo $pObj->getDatum( 'description' ); ?></td>
				<td><?php echo $fullContact; ?></td>
			<?php endif; ?>
			</tr>
		<?php endforeach; ?>
	</table>
<?php else : ?>
	Please use the search form
<?php endif; ?>