<?php
/**
 * @package     Arc
 * @subpackage  Staff
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

class staffmanager_html {
 
	function show_users_html($users) {
		ApotheosisLib::display_table($users);
	}

	function view_user_html($user) {
		echo $user->name;
	}
}


?>
