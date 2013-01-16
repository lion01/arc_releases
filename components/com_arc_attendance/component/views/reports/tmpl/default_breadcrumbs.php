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
?>
<div id="breadcrumbs">
	<?php
	$b = $this->get( 'Breadcrumbs' );
	$crumbNum = count($b);
	if( $crumbNum > 1 ) : ?>
	<div id="breadcrumbs_trail_div">
		<ul>
			<li>Search history:</li>
		<?php foreach( $b as $k=>$crumb ) : ?>
			<li><a href="<?php echo ApotheosisLib::getActionLinkByName( 'att_reports_drillup', array('attendance.sheet'=>$k) ); ?>"><?php echo $crumb['label']; ?></a></li>
		<?php endforeach; ?>
		</ul>
	</div>
	<?php endif; ?>
	<?php if( ($links = $this->getExpandLinks()) != '' ) : ?>
	<div id="expand_links_div">
		Expand search:
		<?php echo $links; ?>
	</div>
	<?php endif; ?>
</div>