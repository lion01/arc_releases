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
<h3>Feedback on report for <?php echo $this->report->getStudentFirstname().' '.$this->report->getStudentSurname(); ?></h3>

<form method="post" action="index.php?Itemid=<?php global $Itemid; echo $Itemid; ?>&option=com_arc_report&view=report" id="reportForm" name="reportForm"/>
<textarea name="feedback" id="feedback" style="width: 100%; height: 10em;"></textarea>
<input type="submit" name="task" value="Send Feedback" />

<p>This form must be completed in order to mark the report as rejected.&nbsp;
   If you leave this page without pressing "Send Feedback", the peer-check status will <b>NOT</b> be changed</p>