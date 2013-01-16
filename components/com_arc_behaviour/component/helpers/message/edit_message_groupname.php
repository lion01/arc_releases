<?php
/**
 * @package     Arc
 * @subpackage  Behaviour
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

$g = $this->message->getDatum('group_id');

$tType = ApotheosisData::_( 'course.type', $g );
switch( $tType ) {
case( 'pastoral' ):
	$tType = 'Tutor';
	break;
	
case( 'normal' ):
	$tType = 'Lesson';
	break;
	
default:
	$tType = 'Untaught';
	break;
}


echo ( empty($g) ? 'No class' : ApotheosisData::_( 'course.name', $g ) );
?>
<input type="hidden" name="msg_tags[gen][]" value="<?php echo ApotheosisData::_( 'message.tagId', 'attribute', $tType ); ?>" />