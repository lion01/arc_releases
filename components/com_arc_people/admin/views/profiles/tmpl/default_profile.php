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

JHTML::script( 'edit_profile.js', JURI::root().'administrator'.DS.'components'.DS.'com_arc_people'.DS.'views'.DS.'profiles'.DS.'tmpl'.DS, true );
$catMap = $this->get( 'CategoryMap' );
$cats = array();

foreach( $this->profile as $id=>$profile ) {
	foreach( $profile as $tmp ) {
		$cats[$tmp['category_id']][] = $tmp;
	}
}
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<fieldset class="adminform_thinfieldset" style="vertical-align: top;">
		<legend><?php echo JText::_( 'Apply a Template' );?></legend>
		<select id="template_to_apply" name="template_to_apply">
			<option value=""></option>
			<?php foreach( $this->templateIds as $templateId ) : ?>
				<option value="<?php echo JText::_( $templateId ); ?>"><?php echo JText::_( ucfirst($templateId) ); ?></option>
			<?php endforeach; ?>
		</select>
	</fieldset>
	<div class="clr"></div>
	<?php foreach( $catMap as $catId=>$catInfo ) {
		if( isset( $cats[$catId] ) ) {
			$com = $catInfo['component'];
			$catInfo['idCount'] = count( $this->curIds );
			echo JHTML::_( 'arc_'.$com.'.profilePanel', $catInfo, $cats[$catId] );
		}
	} ?>
	<input type="hidden" name="option" value="com_arc_people" />
	<input type="hidden" name="view" value="profiles" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="type" value="<?php echo $this->curType; ?>" />
	<?php foreach( $this->curIds as $id ) : ?>
		<input type="hidden" name="ids[]" value="<?php echo $id; ?>" />
	<?php endforeach; ?>
	<input type="hidden" name="select_task" value="<?php echo $this->curType; ?>" />
</form>