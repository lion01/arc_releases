<?php
/**
 * @package     Arc
 * @subpackage  API
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
$link = ApotheosisLibAcl::getUserLinkAllowed( 'arc_api_apps_list' );
?>
<h3>You shouldn't be here</h3>
<p>This page is part of the API which allows applications to retrieve data from Arc.
   Only those applications should be loading this page.
   <?php if( $link !== false ): ?>
   Did you mean to go to the <a href="<?php echo $link; ?>">list</a> of applications you have authorised?
   <?php endif; ?>
   </p>