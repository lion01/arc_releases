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

// id of logged in user
$u = &JFactory::getUser();
$userId = $u->person_id;

// id of profile we are viewing
$profileId = $this->profile->getId();

$link = ApotheosisLib::getActionLinkByName( 'homepage_customise_edit', (array('people.arc_people'=>$userId)) ); ?>
Hide or show your homepage panels
<div style="float: right">
	<?php if( $userId == $profileId ) : ?>
		<a class="panel_modal" target="blank" rel="{handler: 'iframe', size: {x: 640, y: 480}, onClose: function(){window.parent.location.reload();}}" href="<?php echo $link; ?>">Customise</a>
	<?php else: ?>
		<span style="color: grey;">Customise</span>
	<?php endif; ?>
</div>