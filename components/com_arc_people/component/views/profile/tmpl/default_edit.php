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

include( 'default_edit.css' );
$link = ApotheosisLib::getActionLinkByName( 'homepage_customise_save' );
?>
<div id="homepage_customise_div">
	<h3 class="select_panels_centre">Select Panels</h3>
	<form id="edit" name="edit" enctype="multipart/form-data" method="post" action="<?php echo $link; ?>" >
		<div id="formEls">
			<?php
			foreach( $this->panelSettings as $setting ) {
				echo '<label for="id['.$setting['id'].']">'.$setting['alt'].':</label>';
				echo '<input type="checkbox" name="id['.$setting['id'].']" value="'.$setting['alt'].'"'.($setting['shown'] == 1 ? ' checked="checked"' : '').' />';
			}
			?>
		</div>
		<div id="buttons">
			<label for="submit">&nbsp;</label>
			<input type="submit" name="submit" value="Save" />
			<input type="hidden" name="pId" value="<?php echo $this->pId; ?>" />
		</div>
	</form>
</div>