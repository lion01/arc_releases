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

var isNew;

function parentGetElementById( itemId )
{
	return document.getElementById( itemId );
}

function getPStatement( selObj )
{
	i = selObj.selectedIndex;
	if (i != null && i > -1) {
		return selObj.options[i];
	} else {
		return null;
	}
}

function countSelected( selObj )
{
	var retVal = 0;
	for (var i = 0, len = selObj.options.length; i < len; i++) {
		if (selObj.options[i].selected == true) {
			retVal = retVal + 1;
		}
	}
	return retVal;
}

function delSelection(formName, fieldName) 
{
	var statementForm = document.getElementById( formName );
	var t = statementForm.task;
	var f = statementForm.field;
	t.value = 'deleteStatements';
	f.value = fieldName;
	
	var selObj = eval( 'statementForm.' + fieldName );
	var selLen = selObj.options.length;
	var params = new Array();
	var j = 0;
	for( var i = 0; i < selLen; i++ ) {
		if (selObj.options[i].selected) {
			params[j++] = selObj.options[i].value;
		}
	}
	
	var ser = new PHP_Serializer();
	var statement = statementForm.statement;
	statement.value = ser.serialize(params);
	
	statementForm.submit();
}

/**
 * Selects every option in this select element
 * @param selObj object  The select object to be acted upon
 * @return int  The number of elements in the element
 */
function selectAll(selObj, editObj, editLinkObj, delObj)
{
	for (var i = 0, len = selObj.options.length; i < len; i++) {
		selObj.options[i].selected = true;
	}
	
	// make sure things all get enabled / disabled correctly
	listChanged(selObj, editObj, editLinkObj, delObj);

	return selObj.options.length;
}

/**
 * Deselects every option in this select element
 * @param selObj Object  The select element object to be acted upon
 * @return int  The number of elements in the element
 */
function selectNone(selObj, editObj, editLinkObj, delObj)
{
	for (var i = 0, len = selObj.options.length; i < len; i++) {
		selObj.options[i].selected = false;
	}
	
	// make sure things all get enabled / disabled correctly
	listChanged(selObj, editObj, editLinkObj, delObj);
	
	return selObj.options.length;
}

function listChanged( selObj, editObj, editLinkObj, delObj )
{
	var c = countSelected( selObj );
	var opt = getPStatement( selObj );
	editObj.disabled = ( c != 1 );
	delObj.disabled = ( c == 0 );
	if( c == 1 ) {
		editLinkObj.href = editLinkObj.href.replace(/statementId=\d*/, 'statementId='+opt.value);
	}
}

function updateStatement( formName, fieldName, sStatement, retro )
{
	var statementForm = document.getElementById( formName );
	var f = statementForm.field;
	var r = statementForm.retro;
	var s = statementForm.scope;
	var t = statementForm.task;
	f.value = fieldName;
	r.value = retro;
	s.value = 'statements';
	t.value = ( isNew ? 'addStatement' : 'saveStatement' );
	
	var statement = statementForm.statement;
	statement.value = sStatement;
	
	statementForm.submit();
}
