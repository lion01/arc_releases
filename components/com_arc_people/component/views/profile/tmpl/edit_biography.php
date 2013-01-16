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
?>
<h2>Edit your biography information</h2>
<form action="<?php echo $this->link; ?>&task=saveBiography&tmpl=component" method="post" name="edit_biography">

<textarea name="biography" style="width: 400px; height: 10em;">
<?php echo $this->profile->getBiography(); ?>
</textarea><br />
<br />
<input type="submit" name="submit" value="Save" />
</form>
