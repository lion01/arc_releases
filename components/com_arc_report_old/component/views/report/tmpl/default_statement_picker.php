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
?>
<input type="hidden" id="bullet" value="<?php echo $this->bullet; ?>" />
<script>
var p;
var box;
var list;
var picked = '';

var bulletStr = '';
var mergeStart = '<?php echo addslashes( $this->mergeStart ); ?>';
var mergeEnd   = '<?php echo addslashes( $this->mergeEnd ); ?>';
var mStart     = '<?php echo addslashes( preg_quote($this->mergeStart, '/') ); ?>';
var mEnd       = '<?php echo addslashes( preg_quote($this->mergeEnd, '/') ); ?>';

window.onload = function()
{
	p = window.parent;
	var tmp = p.document.getElementById('reportForm');
	box = tmp.elements['<?php echo $this->field->getName(); ?>'];
	list = document.getElementById( 'statementList' );
	bullet = document.getElementById( 'bullet' );
	bulletStr = '^' + bullet.value;
}

function pickSelected()
{
	for( var i = 0, len = list.options.length; i < len; i++ ) {
		if( list.options[i].selected == true ) {
			prefix = '';
			if( (picked != '')
			 && (list.options[i].innerHTML.match( bulletStr ) != null) ) {
				prefix = "\n";
			}
			picked += prefix + list.options[i].innerHTML;
		}
	}
}

function pickTyped()
{
	var i = 0;
	var inc = document.getElementById( 'incomplete' );
	picked = inc.value;
	var field = document.getElementById( 'typed_' + i );
	while( field != null ) {
		eval('picked = picked.replace( /' + mStart + '.+?' + mEnd + '/, field.value );');
		i = i + 1;
		field = document.getElementById( 'typed_' + i );
	}
}
	
function insertAndClose()
{
	var hasMatch = picked.match( mStart + '.+?' + mEnd );
	
	if( hasMatch == null ) {
		// no remaining fields to fill in, so add the text and close
		prefix = '';
		if( (box.value != '')
		 && (picked.match( bulletStr ) != null) ) {
			prefix = "\n";
		}
		box.value += prefix + picked;
		box.onchange();
		window.top.setTimeout('window.parent.document.getElementById(\'sbox-window\').close()', 700);
		p.document.getElementById('sbox-window').close();
	}
	else {
		// there's a merge field not filled in, so get user to type for it
		var inc = document.getElementById( 'incomplete' );
		inc.value = picked;
		document.statementform.submit();
	}
}

</script>
<div style="padding-left:2em;">
<h3><?php echo $this->field->titleHtml(); ?></h3>

<form id="statementform" name="statementform" method="post" action="<?php echo ApotheosisLib::getActionLinkByName('apoth_report_clarify', array('report.fields'=>$this->field->getName())) ?>" />

<?php
switch( $this->layout ) {
case( 'pick' ):
	$statements = $this->statements;
	echo '<select name="statementList" id="statementList" multiple="multiple" style="width: 720px; height: 25em">';
	if( is_array($statements) ) {
		foreach( $statements as $k=>$v ) {
			echo '<option value="'.$v->id.'" style="background: '.htmlspecialchars($v->color).';">'.$v->text.'</option>';
		}
	}
	echo '</select><br />'; ?>
	<br />
	<input type="hidden" id="incomplete" name="incomplete" value="" />
	<input type="button" name="insert" onclick="pickSelected();insertAndClose()" value="Insert" />
	<?php break;

case( 'finish' ):
	echo 'Please fill in the custom text fields<br /><br />';
	$text2 = ( $text = JRequest::getVar( 'incomplete', '' ) );
	$start = preg_quote($this->mergeStart, '/');
	$end   = preg_quote($this->mergeEnd, '/');
	$matches = array();
	$off = 0;
	$count = 0;
	while( preg_match( '/(?<='.$start.').+?(?='.$end.')/', $text2, $matches, PREG_OFFSET_CAPTURE, $off ) ) {
		$match = reset( $matches );
		$strLen = strlen($this->mergeStart.$match[0].$this->mergeEnd);
		$strStart = $match[1] - strlen($this->mergeStart);
		$search = $this->mergeStart.$match[0].$this->mergeEnd;
		$replace = '<input type="text" id="typed_'.$count++.'" value="'.$match[0].'" />';
		$off = $start + strlen($replace);
		
		$text2 = substr_replace( $text2, $replace, $strStart, $strLen );
	}
	echo nl2br( $text2 ); ?>
	<br /><br />
	<input type="hidden" id="incomplete" name="incomplete" value="<?php echo $text; ?>" />
	<input type="button" name="insert" onclick="pickTyped();insertAndClose()" value="Insert" />
	<?php break;
} ?>
</form>
</div>