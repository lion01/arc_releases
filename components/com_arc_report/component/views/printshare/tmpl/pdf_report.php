<?php
/**
 * @package     Arc
 * @subpackage  Report
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

ob_start();

dump( $this->subreports, 'subreports' );

$name = ApotheosisData::_( 'people.displayname', $this->reportee );
?>
<h1><?php echo $name; ?></h1>
<p>header data and other prior sections</p>

<?php foreach( $this->subreports as $this->subreport ): ?>
	subreport <?php echo $this->subreport->getId(); ?><br />
<?php endforeach; ?>

<p>footer data and other after sections</p>

<?php
$body = ob_get_clean();
$this->pdf->writeHTML( $body );
?>