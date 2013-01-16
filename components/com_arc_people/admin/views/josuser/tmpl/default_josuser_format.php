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

JHTML::script( 'josuser_format.js', JURI::root().'administrator'.DS.'components'.DS.'com_arc_people'.DS.'views'.DS.'josuser'.DS.'tmpl'.DS, true );
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<ul>
		<li>The format of each required entry for the Joomla! user record is comprised of keywords taken from a user's Arc record.</li>
		<li>Sample information can be entered into the legend to preview its final format.
		<li>The [[domain]] keyword will use the current website domain as the email domain unless otherwise specified.</li>
		<li>Conflict resolution is handled by applying the formats in order.</li>
		<li>If a keyword comes up blank for a given user, no middlename for instance, then any formats requiring that keyword will be skipped.</li>
	</ul>
	<?php echo $this->loadTemplate( 'keywords_table' ); ?>
	<fieldset class="adminform_thinfieldset" style="vertical-align: top;">
		<legend><?php echo JText::_( 'Joomla! User Variable Formats...' );?></legend>
		<?php echo $this->params->render( 'params', 'jos_user_format' ); ?>
	</fieldset>
	<?php echo $this->loadTemplate( 'preview_table' ); ?>
	<input type="hidden" name="option" value="com_arc_people" />
	<input type="hidden" name="view" value="josuser" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="select_task" value="format" />
</form>