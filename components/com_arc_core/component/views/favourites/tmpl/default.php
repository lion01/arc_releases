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
?>
<h3>These are the actions tagged as favourites which are relevant to you</h3>
<p>Alternatively, you can use the menu at the top to view any page</p>

<ul>
<?php for ($i = 0, $n = count($this->items); $i < $n; $i++) : ?>
	<?php 
		$this->loadItem($i);
		echo $this->loadTemplate('item');
	?>
<?php endfor; ?>
</ul>