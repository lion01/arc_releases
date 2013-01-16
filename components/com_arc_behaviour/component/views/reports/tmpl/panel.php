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
?>
<div id="mainGraph" class="graph">
<?php
$h1 = 200;
$h2 = (count($this->seriesIds) * 30);
$graphLink = $this->_getGraphLink( $this->seriesIds, $h1, $h2 );

$this->data = $this->report->getParsedSeries( reset($this->seriesIds) );
?>
<img src="<?php echo $graphLink; ?>" title="Graph of behaviour scores" />
</div>
<table>
<tr>
<?php foreach( $this->data['_meta']['tallyThreads'] as $color=>$count ) : ?>
	<td><?php echo JHTML::_( 'arc.dot', strtolower($color), $color ); ?></td>
<?php endforeach; ?>
<td>Score</td>
</tr>
<tr>
<?php foreach( $this->data['_meta']['tallyThreads'] as $color=>$count ) : ?>
	<td><?php echo $count; ?></td>
<?php endforeach; ?>
<td><?php echo $this->data['_meta']['end']; ?></td>
</tr>
</table>
<?php if( ($l = ApotheosisLibAcl::getUserLinkAllowed('apoth_be_reports')) !== false ) :?>
	<a href="<?php echo $l ?>">Analysis</a>
<?php endif; ?>