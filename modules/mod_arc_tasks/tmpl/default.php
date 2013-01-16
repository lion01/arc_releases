<?php
/**
 * @package     Arc
 * @subpackage  Module_Tasks
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */


JHTML::_( 'behavior.modal' );
?>
<div id="mod_arc_tasks">

<div class="button_list" id="tasks">
<div class="btn"><span class="label"> New </span></div>
<div class="list_wrapper">
<ul>
<?php
//var_dump_pre( $tasks );
//var_dump_pre( class_exists('ApotheosisLib') );

foreach( $tasks as $task ) {
	if( !isset( $task['target'] ) ) { $task['target'] = 'self'; }
	switch( $task['target'] ) {
		case( 'self' ):
			$tgt = '';
			break;
		
		case( 'blank' ):
			$tgt = 'target="_blank"';
			break;
		
		case( 'popup' ):
			$tgt = 'class="modal" rel="{handler: \'iframe\', size: {x: 600, y: 415}}"';
			break;
	}
	
	?>
	<li><a href="<?php echo $task['url']; ?>" <?php echo $tgt; ?>><?php echo $task['text']; ?></a></li>
<?php
}
?>
</ul>
</div>
</div>
</div>