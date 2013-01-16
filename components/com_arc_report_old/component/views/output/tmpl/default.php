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

JHTML::script( 'default.js', JURI::base().'components'.DS.'com_arc_report'.DS.'views'.DS.'output'.DS.'tmpl'.DS, true );

global $Itemid;
?>
<input id="url" type="hidden" value="index.php?option=com_arc_report&view=output&scope=data&Itemid=<?php echo $Itemid; ?>&format=raw"; />

<h3>Select which reports to generate</h3>
<p>Your reports will be generated when you press the "Generate" button.</p>

<form id="osubjselection" name="osubjselection" action="index.php?option=com_arc_report&view=output&task=generate&Itemid=<?php echo $Itemid; ?>" method="post">

Select format: <select name="format">
<option value="apothpdf">a PDF to print</option>
<option value="raw">data for a merge</option>
</select><br />
<table>
<!-- select course -> group -> pupil -->
<tr><td colspan="6">
Select courses -&gt; groups -&gt; pupils for which to retrieve data.<br />
</td></tr>
<tr>
<td><?php echo JHTML::_('select.genericList', $this->courses, 'ocourse[]', 'multiple="multiple" class="multi_medium" onchange="javascript:selectChange( this );"', 'id', 'fullname', ''); ?>
</td>
<td><input type="button" id="ocoursebtnfwd" value="&gt;&gt;" onclick="javascript:goForward( 'ocourse', 'ogroup' );" /><br />
    <input type="button" id="ogroupbtnback" value="&lt;&lt;" onclick="javascript:goBack( 'ogroup', 'ocourse' );" /><br />
</td>

<td><?php echo JHTML::_('select.genericList', array(), 'ogroup[]', 'multiple="multiple" class="multi_medium" onchange="javascript:selectChange( this );"', 'id', 'fullname'); ?>
</td>
<td><input type="button" id="ogroupbtnfwd"  value="&gt;&gt;" onclick="javascript:goForward( 'ogroup', 'opupil' );" /><br />
    <input type="button" id="opupilbtnback" value="&lt;&lt;" onclick="javascript:goBack( 'opupil', 'ogroup' );" /><br />
</td>

<td><?php echo JHTML::_('select.genericList', array(), 'opupil[]', 'multiple="multiple" class="multi_medium" onchange="javascript:selectChange( this );"', 'id', 'displayname'); ?>
</td>

<td><input type="submit" id="ocoursebtngen" name="bycourse" value="Generate" /></td>
</tr>

<!-- select tutor group -> pupil -> subject -->
<tr><td colspan="6">
Select courses -&gt; groups -&gt; pupils for which to retrieve data.<br />
</td></tr>
<tr>
<td><?php echo JHTML::_('select.genericList', $this->tutors, 'otutor[]', 'multiple="multiple" class="multi_medium" onchange="javascript:selectChange( this );"', 'id', 'fullname', ''); ?>
</td>
<td><input type="button" id="otutorbtnfwd"   value="&gt;&gt;" onclick="javascript:goForward( 'otutor', 'omember' );" /><br />
    <input type="button" id="omemberbtnback" value="&lt;&lt;" onclick="javascript:goBack( 'omember', 'otutor' );" /><br />
</td>

<td><?php echo JHTML::_('select.genericList', array(), 'omember[]', 'multiple="multiple" class="multi_medium" onchange="javascript:selectChange( this );"', 'id', 'fullname'); ?>
</td>
<td><input type="button" id="omemberbtnfwd"   value="&gt;&gt;" onclick="javascript:goForward( 'omember', 'ocourse2' );" /><br />
    <input type="button" id="ocourse2btnback" value="&lt;&lt;" onclick="javascript:goBack( 'ocourse2', 'omember' );" /><br />
</td>

<td><?php echo JHTML::_('select.genericList', array(), 'ocourse2[]', 'multiple="multiple" class="multi_medium" onchange="javascript:selectChange( this );"', 'id', 'displayname'); ?>
</td>

<!--// and a generate/submit button -->
<td><input type="submit" id="otutorbtngen" name="bytutor" value="Generate" /></td>
</tr>
</table>
<br /><br />
<?php /*
?> <div id="debug"></div> <?php // */
?>
</form>
