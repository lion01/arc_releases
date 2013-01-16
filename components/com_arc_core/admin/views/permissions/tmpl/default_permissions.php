<?php
/**
 * @package     Arc
 * @subpackage  Core
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

// add default javascript
JHTML::script( 'default.js', $this->addPath, true );

$parents = array();
$depth = -1;
$rowCount = 0;
foreach( $this->actions as $this->aId=>$aclGroups ) :
	$action = reset( $aclGroups );
	$mId = $action->menu_id;
	
	// work out indentation. Go in a level for each sublevel of menu.
	if( !array_key_exists($action->parent, $parents) ) {
		$parents[$action->parent] = $action->parent;
		$depth++;
	}
	else {
		while( (($tmp = end($parents)) !== $action->parent) && ($tmp !== false) ) {
			array_pop( $parents );
			$depth--;
		}
	}
	
	// Add a level for tasks
	$indent = ( is_null($action->task) ? $depth : $depth + 1 );
	
	$formatStr = str_repeat( '- ', $indent );
	if( $indent == 0 ) {
		$formatStr .= '<b>%1$s</b>';
	}
	elseif( !is_null($action->task) ) {
		$formatStr .= '<i>%1$s</i>';
	}
	else {
		$formatStr .= '%1$s';
	}
	if( $rowCount++ > 20 ) {
		$rowCount = 0;
		echo $this->_headings;
	}
	?>
	<tr>
		<td><?php echo $action->id; ?></td>
		<td><?php echo $action->menu_id.'<br />('.$action->parent.')'; ?></td>
		<td><?php echo sprintf( $formatStr, $action->menu_text ); ?></td>
		<td><?php echo $action->description; ?></td>
		<?php
			foreach( $this->roles as $role ) { 
				$this->rId = $role->id;
				// Determine which image to use
				if( isset($aclGroups[$this->rId]) && (bool)$aclGroups[$this->rId]->allowed ) {
					if( is_null($aclGroups[$this->rId]->sees) ) {
						$this->state = 'allowed';
					}
					else {
						$this->state = 'restricted';
					}
				}
				else {
					$this->state = 'denied';
				}
				echo $this->loadTemplate( 'mark' );
			} ?>
	</tr>
<?php endforeach; ?>
<input type="hidden" id="groupId" name="group" value="" />