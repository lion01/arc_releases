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
$h1 = 400;
$h2 = (count($this->seriesIds) * 30);
$graphLink = $this->_getGraphLink( $this->seriesIds, $h1, $h2 );
?>
<img src="<?php echo $graphLink; ?>" title="Graph of behaviour scores" />
<br /><small>NB: responses to incidents are also shown on the bar-graph as in some cases they have a score associated with them</small>
</div>