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

/**
 * API OAuth Data Access
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage API
 * @since      1.6.1
 */
class ApotheosisData_Api extends ApotheosisData
{
	function info()
	{
		return 'API component installed';
	}
}
?>