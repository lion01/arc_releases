<?php
/**
 * @package     Arc
 * @subpackage  Report
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

while($row = $this->report->getRow()) {
	foreach ($row as $k=>$this->v) {
		if(is_object($this->v) && $this->v->showInPdf()) {
			echo $this->loadTemplate( 'field' );
//			timer( 'apothpdf - field '.$this->v->_name );
		}
	}
}
?>

