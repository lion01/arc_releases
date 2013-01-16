<?php
/**
 * @package     Arc
 * @subpackage  Assessment
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

echo '"~Aspect~"'."\n"
	.'"title","'.     str_replace( '"', '""', $this->aspect->getProperty( 'title' )      )."\"\n"
	.'"short","'.     str_replace( '"', '""', $this->aspect->getProperty( 'short' )      )."\"\n"
	.'"boundaries","'.str_replace( '"', '""', $this->aspect->getProperty( 'boundaries' ) )."\"\n"
	.'"shown","'.     str_replace( '"', '""', $this->aspect->getProperty( 'shown' )      )."\"\n";
?>