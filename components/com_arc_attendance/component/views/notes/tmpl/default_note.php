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

if( is_null($this->note->id) ) : ?>
	
	<?php $id = '__new__'; ?>
	
	<textarea style="height: 10em; width: 30em" name="notes[<?php echo $id; ?>][note]"></textarea>
	
<?php else: ?>
	
	<?php $id = $this->note->id; ?>
	
	<h4>To <?php echo htmlspecialchars($this->note->firstname.' '.$this->note->surname); ?><br />
		at: <?php echo htmlspecialchars($this->note->last_modified); ?>
		<?php echo (is_null($this->note->delivered_on) ? '' : '<br />Delivered at '.$this->note->delivered_on );?></h4>
	
	<?php if( is_null($this->note->delivered_on) ) : ?>
		<textarea style="height: 10em; width: 30em" name="notes[<?php echo $id; ?>][note]"><?php echo htmlspecialchars($this->note->message); ?></textarea>
	<?php else: ?>
		<textarea style="height: 10em; width: 30em" name="notes[<?php echo $id; ?>][notedis]" disabled="disabled"><?php echo htmlspecialchars($this->note->message); ?></textarea>
		<input type="hidden" name="notes[<?php echo $id; ?>][note]" value="<?php echo htmlspecialchars($this->note->message); ?>" />
	<?php endif; ?>
	
<?php endif; ?>

<?php if( $this->delivering ) : ?>
	<br />
	<input type="checkbox" name="notes[<?php echo $id; ?>][delivered]" <?php echo ( is_null($this->note->delivered_on) ? '' : 'checked="checked"'); ?>/> <label for="notes[<?php echo $id; ?>][delivered]" >Delivered?</label>
<?php else: ?>
	<input type="hidden"   name="notes[<?php echo $id; ?>][delivered_on]" value="<?php echo ( is_null($this->note->delivered_on) ? '' : $this->note->delivered_on); ?>" />
<?php endif; ?>
