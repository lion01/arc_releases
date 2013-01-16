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
?>
<div class="micro_details_wrapper">
	<div class="micro_details_progress">
		<div class="micro_details_progress_inner">
			<div class="micro_details_progress_title">
				Assignments
			</div>
			<div class="micro_details_progress_table_outer">
				<div class="micro_details_progress_table">
					<table>
						<tr>
							<th>Add / Del</th>
							<th>All / None</th>
							<th>Progress</th>
							<th>Due</th>
							<th>Assignees</th>
							<th>Assistants</th>
							<th>Admins</th>
							<th>Updates</th>
						</tr>
						<?php
							$this->groups = &$this->task->getGroups();
							foreach( $this->groups as $groupId=>$notByRef ) {
								$this->group = &$this->groups[$groupId];
								$this->_curGroupId = $this->group->getId();
								echo $this->loadTemplate( 'task_progress' );
							}
						?>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>