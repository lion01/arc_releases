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

include( 'att_summary_buttons.css' );
?> 
<!-- html to display hover buttons -->
<div id="att_panel_buttons">
	<img src="<?php echo JURI::base().'components'.DS.'com_arc_attendance'.DS.'images'.DS.'stat_pie.png'; ?>"
		alt="<?php echo $this->statListTitle; ?>"
		title="<?php echo $this->statListTitle; ?>"
		id="stat_pie_button"
		class="att_panel_button" />
	<img src="<?php echo JURI::base().'components'.DS.'com_arc_attendance'.DS.'images'.DS.'all_histo.png'; ?>"
		alt="<?php echo $this->allListTitle; ?>"
		title="<?php echo $this->allListTitle; ?>"
		id="all_histo_button"
		class="att_panel_button" />
	<img src="<?php echo JURI::base().'components'.DS.'com_arc_attendance'.DS.'images'.DS.'all_histo_per.png'; ?>"
		alt="<?php echo $this->allPerListTitle; ?>"
		title="<?php echo $this->allPerListTitle; ?>"
		id="all_per_histo_button"
		class="att_panel_button" />
</div>