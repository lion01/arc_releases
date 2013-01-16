<?php
/**
 * @package     Arc
 * @subpackage  People
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

foreach( $this->awards as $award ) {
	echo '<div class="award">
		<div class="award_image"><img src="'.JURI::base().'components'.DS.'com_arc_people'.DS.'images'.DS.$award['image'].'" alt="'.$award['name'].'" title="'.$award['name'].'"></div>
		'.$award['caption'].'
	</div>';
}
?>