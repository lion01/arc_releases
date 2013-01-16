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

$id = $this->category->getId();
$title = $this->category->getTitle();
$progress = $this->category->getProgress();
$complete = $this->category->getComplete();
$incomplete = $this->category->getIncomplete();
$overdue = $this->category->getOverdue();
$due = $this->category->getDue();
?>
<tr>
	<td>
		<?php echo 'Cat: '.$id; /**** dev code */ ?>
	</td>
	<td>
		<input type="checkbox">
	</td>
	<td>
		<?php echo $title; ?>
	</td>
	<td>
		<?php echo $progress.'%'; ?>
	</td>
	<td>
		<?php echo $complete; ?>
	</td>
	<td>
		<?php echo $incomplete; ?>
	</td>
	<td>
		<?php echo $overdue; ?>
	</td>
	<td>
		<?php echo $due; ?>
	</td>
</tr>