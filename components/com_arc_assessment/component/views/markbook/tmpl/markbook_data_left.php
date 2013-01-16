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
<table class="markbookTable" cellSpacing="0px">
	<thead>
		<!-- hide / show controls, and admin links -->
		<?php echo $this->loadTemplate('controls_left'); ?>
		<!-- Assessment titles -->
		<?php echo $this->loadTemplate('assessments_left'); ?>
		<!-- Aspect titles -->
		<?php echo $this->loadTemplate('aspects_left'); ?>
	</thead>
	<!-- Data -->
	<tbody>
	<?php
		$this->oddrow = false;
		$this->rowCount = -1;
		foreach($this->rows as $this->row) {
			$this->rowCount++;
			$this->colCount = 0;
			if( ($this->rowCount > 0) && (($this->rowCount % $this->rowRepeatHeaders) == 0) ) {
				echo $this->loadTemplate( 'assessments_left' );
				echo $this->loadTemplate( 'aspects_left' );
			}
			echo $this->loadTemplate( 'row_left' );
		}
		echo $this->loadTemplate( 'assessments_left' );
		echo $this->loadTemplate( 'aspects_left' );
	?>
	</tbody>
</table>
