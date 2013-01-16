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

$fieldName = $this->field->getTitle();
$field = $this->bank->getField();
echo '~Fieldname:'.$fieldName.',~field:'.$field."\n";
echo '~id,~Keyword,~Text,~range_min,~range_max,~range_of,~color'."\n";

$statements = $this->bank->getStatements();
if( is_array($statements) ) {
	foreach( $statements as $s ) {
		echo '"'.$s->id.'",'
			.'"'.$s->keyword.'",'
			.'"'.$s->text.'",'
			.'"'.$s->range_min.'",'
			.'"'.$s->range_max.'",'
			.'"'.$s->range_of.'",'
			.'"'.$s->color.'"'."\n";
	}
}
?>