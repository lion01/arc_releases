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

JHTML::_('behavior.mootools')."\n";

$doc = &JFactory::getDocument();
$doc->addScript( 'administrator'.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'js'.DS.'mooRainbow.js');
$doc->addScript( 'administrator'.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'js'.DS.'colorNameHash.js');
$doc->addScript( 'administrator'.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'js'.DS.'apothMooRainbow.js');
?>
<?php $name = $this->field->getName(); ?>

<script>
var box;
var p;
var statement;

/**
 * Function to be used as an object prototype so we can create an object that php
 * will recognise once we have serialized and unserialized it.
 */
function stdClass() {
}

/**
 * Set global vars
 */
window.onload = function()
{
	box = document.getElementById( "text" );
	p = window.parent;
	var statementForm = p.document.getElementById( '<?php echo $this->formName; ?>');
	var statementField = statementForm.<?php echo $name; ?>;
	
	<?php if( $this->new ) : ?>
		statement = new Object();
		statement.text = '';
		statement.value = null;
		p.isNew = true;
	<?php else: ?>
		statement = p.getPStatement( statementField );
		p.isNew = false;
	<?php endif; ?>
}

/**
 * Insert text into the text box
 * @param mergeWord string  The text to add to the box
 */
function addText( mergeWord )
{
	box.value += mergeWord.innerHTML;
	box.focus();
//	textSetCaretToPos(box, box.value.length);
}

function saveAndClose()
{
	var retro     = document.getElementById('retro');
	var range_min = document.getElementById('range_min');
	var range_max = document.getElementById('range_max');
	var range_of  = document.getElementById('range_of');
	var color     = document.getElementById('color');
	var keyword   = document.getElementById('keyword');
	var order     = document.getElementById('order');
	
	statement.innerHTML = box.value; // update text in parent window
	statementObj = new stdClass();
	statementObj.id   = statement.value;
	statementObj.text = statement.innerHTML;
	statementObj.range_min = range_min.value;
	statementObj.range_max = range_max.value;
	statementObj.range_of  = range_of.value;
	statementObj.color     = color.value;
	statementObj.keyword   = keyword.value;
	statementObj.field     = '<?php echo $name; ?>';
	statementObj.order     = order.value;
	
	var ser = new PHP_Serializer();
	var sStatement = ser.serialize(statementObj);
	
	p.updateStatement( '<?php echo $this->formName; ?>', '<?php echo $name; ?>', sStatement, retro.checked );
	window.top.setTimeout('window.parent.document.getElementById(\'sbox-window\').close()', 700);
	p.document.getElementById('sbox-window').close();
}

</script>

<h3><?php echo $this->groupName; ?>: <?php echo( $this->new ? JText::_('Add a statement') : JText::_('Edit a statement') ); ?></h3>
editting: <?php echo $name; ?><br />

<table style="width: 100%">
<?php $element = $this->statFields['text']; ?>
<tr><td colspan="2" style="width: <?php echo $element->getHtmlWidth(); ?>">
	<?php echo $element->titleHtml().sprintf($element->dataHtml(), '100%', $element->getHtmlHeight()); ?>
</td></tr>
<tr><td colspan="2">
	<?php foreach( $this->mergeWords as $k=>$v ) : ?>
	<span
		class="merge-field"
		onmouseover="this.className='merge-field-over'"
		onmouseout="this.className='merge-field'"
		onclick="addText(this)"><?php echo $this->mergeStart.$v->word.$this->mergeEnd; ?></span>
	<?php endforeach; ?>
</td></tr>
<tr>
<?php $element = $this->statFields['range_min']; ?>
<td style="width: <?php echo $element->getHtmlWidth(); ?>">
	<?php echo $element->titleHtml().sprintf($element->dataHtml(), '100%', $element->getHtmlHeight()); ?>
</td>
<?php $element = $this->statFields['range_max']; ?>
<td style="width: <?php echo $element->getHtmlWidth(); ?>">
	<?php echo $element->titleHtml().sprintf($element->dataHtml(), '100%', $element->getHtmlHeight()); ?>
</td>
<td>&nbsp;</td></tr>

<tr>
<?php $element = $this->statFields['range_of']; ?>
<td style="width: <?php echo $element->getHtmlWidth(); ?>">
	<?php echo $element->titleHtml().sprintf($element->dataHtml(), '100%', $element->getHtmlHeight()); ?>
</td></tr>

<tr>
<?php $element = $this->statFields['color']; ?>
<td style="width: <?php echo $element->getHtmlWidth(); ?>">
	<?php echo $element->titleHtml().sprintf($element->dataHtml(), '100%', $element->getHtmlHeight()); ?>
	<img src="components/com_arc_core/images/rainbow.png" id="myRainbow" alt="[r]" />
<script type="text/javascript">
	window.addEvent('domready', function() {
		$('color').setStyle( 'background-color', $('color').value);
		$('color').onchange = function() { $('color').setStyle( 'background-color', $('color').value); }
		apothMooRainbow($('color').value , 'myRainbow', 'color');
	});
</script>
</td>

<?php $element = $this->statFields['keyword']; ?>
<td style="width: <?php echo $element->getHtmlWidth(); ?>">
	<?php echo $element->titleHtml().sprintf($element->dataHtml(), '100%', $element->getHtmlHeight()); ?>
</td>
</tr>

<tr>
<td>
	<?php $element = $this->statFields['order']; ?>
	<?php if( $this->new ) : ?>
		<?php $element->setValue( $this->bank->getNextOrder() ); ?>
		<input type="hidden" id="retro" name="retro" value="0" checked="false" />
	<?php else : ?>
		<input type="checkbox" id="retro" name="retro" /> Apply retrospectively?<br />
	<?php endif; ?>
	<?php echo $element->dataHtml(); ?>
</td>
<td>
	<input type="button" name="save" value="Save" onclick="javascript:saveAndClose();" /><br />
</td>
</tr></table>
