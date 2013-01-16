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

echo JHTML::_( 'arc.searchStart' ); ?>
<div id="search_container">
	<form action="<?php echo ApotheosisLib::getActionLinkByName( 'apoth_ppl_list' ); ?>" name="arc_search" id="arc_search" method="post" >
		<div class="search_row">
			<div class="search_field">
				<label for="firstname">First name:</label><br />
				<input type="text" value="<?php echo JRequest::getVar( 'firstname' ); ?>" size="30" id="firstname" name="firstname" />
				<input class="search_default" type="hidden" value="" name="search_default_firstname">
			</div>
			<div class="search_field">
				<label for="surname">Surname:</label><br />
				<input type="text" value="<?php echo JRequest::getVar( 'surname' ); ?>" size="30" id="surname" name="surname" />
				<input class="search_default" type="hidden" value="" name="search_default_surname">
			</div>
		</div>
		<div class="search_row">
			<div class="search_field">
				<label for="rel_of">Relative of:</label><br />
				<?php echo JHTML::_( 'arc_people.people', 'rel_of', '', 'pupil', true, array(), true ); ?>
			</div>
		</div>
		<div class="search_row">
			<div class="search_field">
				<?php echo JHTML::_( 'arc.hidden', 'passthrough', 'general' ); ?>
				<?php echo JHTML::_( 'arc.submit' ); ?>
				<?php echo JHTML::_( 'arc.reset' ); ?>
			</div>
		</div>
	</form>
</div>