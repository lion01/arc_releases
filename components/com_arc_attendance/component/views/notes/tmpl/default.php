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
?>
<h3>Notes</h3>

<?php $h = ( ($this->edits) ? 'Add a note for' : 'View notes for' );
	if( $this->state->search ) : 
	echo JHTML::_( 'arc.searchStart' );
?>
	<div id="search_container">
		<form action="<?php echo $this->link; ?>" name="arc_search" id="arc_search" method="post">
		<div class="search_row">
			<div class="search_field">
				<label for="pupil">Pupil:</label><br />
				<?php echo JHTML::_( 'arc_timetable.pupil', 'pupil' );?>
			</div>
		</div>
		<div class="search_row">
			<div class="search_field">
				<?php echo JHTML::_( 'arc.hidden', 'passthrough', 'general' ); ?>
				<?php echo JHTML::_( 'arc.submit' ); ?>
				<?php echo JHTML::_( 'arc.reset' ); ?>
			</div>
		</div>
		</form>
	</div>
<?php endif; ?>

<hr />
<form action="<?php echo $this->link; ?>" method="post">
<?php if( $this->edits || $this->delivering ) :
	echo JHTML::_( 'arc.submit', 'Save' );
endif; ?>

<?php if( empty($this->notes) ) :
	echo '<br /><br /><h3>'.$h.' a pupil</h3>';
	echo JHTML::_( 'arc_timetable.pupil', 'pupil' ).'<br />';
	echo $this->loadTemplate( 'note' ).'<br /><br />';
?>
	
<?php else : ?>
	<br /><br />
	<h3><?php echo $h.' '.$this->note->firstname.' '.$this->note->surname; ?></h3>
	
	<input type="hidden" name="pupil" value="<?php echo $this->note->pupil_id; ?>" />
	<?php
	foreach( $this->notes as $this->note ) {
		if( $this->showDelivered || is_null($this->note->delivered_on) ) {
			echo $this->loadTemplate( 'note' ).'<br /><br />';
		}
	}
	?>

<?php endif; ?>

<?php if( $this->edits || $this->delivering ) :
	echo JHTML::_( 'arc.submit', 'Save' );
endif; ?>

</form>
