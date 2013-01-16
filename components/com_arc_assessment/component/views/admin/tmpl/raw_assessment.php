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

echo '"~Assessment~"'."\n"
	.'"title","'.         str_replace( '"', '""', $this->assessment->getProperty( 'title'          ) )."\"\n"
	.'"short","'.         str_replace( '"', '""', $this->assessment->getProperty( 'short'          ) )."\"\n"
	.'"description","'.   str_replace( '"', '""', $this->assessment->getProperty( 'description'    ) )."\"\n"
	.'"color","'.         str_replace( '"', '""', $this->assessment->getProperty( 'color'          ) )."\"\n"
	.'"always_show","'.   str_replace( '"', '""', $this->assessment->getProperty( 'always_show'    ) )."\"\n"
	.'"group_specific","'.str_replace( '"', '""', $this->assessment->getProperty( 'group_specific' ) )."\"\n"
	.'"valid_from","'.    str_replace( '"', '""', $this->assessment->getProperty( 'valid_from'     ) )."\"\n"
	.'"valid_to","'.      str_replace( '"', '""', $this->assessment->getProperty( 'valid_to'       ) )."\"\n"
	.'"access","'.        str_replace( '"', '""', serialize($this->get('AssAccess')) )."\"\n"
	.'"admin_groups","'.        str_replace( '"', '""', serialize($this->get('AssGroupIds')) )."\"\n"
	."\n";

echo $this->loadTemplate( 'aspects' );
?>