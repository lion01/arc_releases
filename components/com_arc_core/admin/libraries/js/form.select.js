/**
 * @package     Arc
 * @subpackage  Core
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

/**
 * These functions perform functions on form select elements
 * Considered making an HTMLSelectElement class to wrap around the object
 * in IE and using the native one in Opera/Firefox, then making all this
 * object oriented, but it turns out it was too difficult to be worthwhile
 */

// constants to determine the behaviour of these functions.
// used by passing the sum or OR combination of all constants to apply as a parameter
// eg result = selectToArray(theObj, SEL_ARR_FOO + SEL_ARR_BAR)
var SEL_GET_ALL = 1; // Indicates if all select options are required (as opposed to only the selected ones) // should be const, but IE is rubbish

/*
 * Adds a new option to the end of the select box
 * @param selObj object  The select object to be acted upon
 * @param text string  The text to be displayed for this option
 * @param value string  The 
 */
function selectAppendOption(selObj, text, value)
{
	var i = selObj.options.length;
	if (isUndefined(value)|| isNull(value)) {
		value = text;
	}
	selObj.options[i] = new Option(text, value);
	return selObj.options.length;
}

/*
 * Removes all options from the select box
 * @param selObj object  The select object to be acted upon
 */
function selectClear(selObj)
{
   while (selObj.length > 0) {
       selObj.remove(0);
   }
}

/**
 * @param selObj object  The select object to be acted upon
 * @return the text of the currently selected item
 */
function selectCurTxt(selObj)
{
	for (var i = 0, len = selObj.options.length; i < len; i++) {
		if (selObj.options[i].selected == true) {
			return selObj.options[i].text;
		}
	}
	return false;
}

/**
 * @param selObj object  The select object to be acted upon
 * @return the value of the currently selected item
 */
function selectCurVal(selObj)
{
	return selObj.value;
}

/**
 * Drops a single option from the select box
 * @param selObj object  The select object to be acted upon
 * @param target mixed  The integer index, or the value of the option to be dropped
 */
function selectDropOption(selObj, target)
{
	if (isUndefined(target) || isNull(target) || (target > selObj.length)) {
		return false
	}
	if (!isNumber(target)) {
		for (var i = 0, len = selObj.options.length; i < len; i++) {
			if (selObj.options[i].value == target) {
				target = i;
				i = len;
			}
		}
	}
	selObj.options[target] = null;
	return selObj.options.length;
}

/**
 * Set the options available in a select box.
 * valArr and/or selArr can be zero length arrays to pass over their functionality
 * @param selObj object  The select object to be acted upon
 * @param textArr array  All the text to be displayed in the select options
 * @param valArr array  An index-mapped array of values to associate with the text in each option
 * @param selArr array  An index-mapped array of booleans indicating whether each option is to be selected
 */
function selectFromArray(selObj, textArr, valArr, selArr)
{
	// clear the select box and initialise any too-small arrays
	selObj.options.length = 0;
	var values = true;
	if (!isArray(textArr)) {
		textArr = new Array();
	}
	if ((!isArray(valArr)) || (valArr.length < textArr.length)) {
		valArr = textArr;
	}
	if ((!isArray(selArr)) || (selArr.length < textArr.length)) {
		selArr = new Array();
		for (i = 0; i < textArr.length; i++) {
			selArr[i] = false;
		}
	}
//	alert('that\'s:\ntext:'+textArr+'\nvals:'+valArr+'\nsels:'+selArr+'\nand... go');
	// create the new options
	for (i = 0; i < textArr.length; i++) {
		selObj.options[i] = new Option(textArr[i], valArr[i], false, selArr[i]);
	}
	return selObj.options.length;
}

/**
 * Set the options available in a select box.
 * valArr and/or selArr can be zero length arrays to pass over their functionality
 * @param selObj object  The select object to be acted upon
 * @param valText Hashtable  All the value=>text pairs to be set as the select options
 */
function selectFromHashtable(selObj, valText)
{
	// check that we actually have a hashtable and clear the select box
	if (!(isObject(valText) && (valText.constructor == Hashtable))) {
		alert('invalid parameter when creating select from Hashtable');
		return 0;
	}
	var i = 0;
	selObj.options.length = i;
	var opt = valText.reset()
	while (opt !== false) {
		selObj.options[i++] = new Option(opt, valText.key());
		opt = valText.next();
	}
	return selObj.options.length;
}

/**
 * Inserts an option at any point in the select element
 * @requires the Hashtable object from lib.hashtable.js
 * @param selObj object  The select object to be acted upon
 * @param afterValue mixed  The integer index at which to insert this item,
 *                           or the string value of the option to insert the new option after
 * @param text string  The text to be displayed for this option
 * @param value string  The value for this option
 * @param selected boolean  Indicates if the new option should be selected (true) or not (false)
 */
function selectInsertOption(selObj, afterValue, text, value, selected)
{
	if (isUndefined(afterValue) || isNull(afterValue) ||
		isUndefined(text) || isNull(text)) {
		return false
	}
	// now we know that all required parameters are present...
	// create the new option
	if (isUndefined(value) || isNull(value)) {
		value = text;
	}
	selected = ((isBoolean(selected) == true) ? selected : false);
	var opt = new Option(text, value, false, selected);
	// do the move/insert.
	numIndex = ((isNumber(afterValue) == true) ? true : false);
	for (var i = selObj.options.length; i > 0; i--) {
		var tmpopt = selObj.options[(i - 1)];
		if ( ((numIndex == true)  && ((i) == afterValue))   ||
			 ((numIndex == false) && (tmpopt.value == afterValue)) ||
			  (i == 1) ) {
			selObj.options[i] = opt;
			i = -1;
		}
		else {
			selObj.options[i] = new Option(tmpopt.text, tmpopt.value, tmpopt.defaultSelected, tmpopt.selected);
		}
	}
	return selObj.options.length;
}

/**
 * Inserts an option at the top of the select element
 * @requires the Hashtable object from lib.hashtable.js
 * @param selObj object  The select object to be acted upon
 * @param text string  The text to be displayed for this option
 * @param value string  The value for this option
 * @param selected boolean  Indicates if the new option should be selected (true) or not (false)
 */
function selectPrependOption(selObj, text, value, selected)
{
	// move all original options up one position
	for (var i = selObj.options.length; i > 1; i--) {
		var tmpopt = selObj.options[(i-1)];
		selObj.options[i] = new Option(tmpopt.text, tmpopt.value, tmpopt.defaultSelected, tmpopt.selected);
	}
	// create the new option
	if (isUndefined(value) || isNull(value)) {
		value = text;
	}
	selected = ((isBoolean(selected) == true) ? selected : false);
	var opt = new Option(text, value, false, selected);
	// then insert the new option at the start
	// (well, actually an element in if there's an empty one so as to preserve the empty option at the start)
	if (selObj.options[0].text == '') {
		selObj.options[1] = opt;
	}
	else {
		selObj.options[0] = opt;
	}
	return selObj.options.length;
}

/**
 * Highlights (selects) every option in this select element
 * @param selObj object  The select object to be acted upon
 * @return int  The number of elements in the element
 */
function selectSelectAll(selObj)
{
	for (var i = 0, len = selObj.options.length; i < len; i++) {
		selObj.options[i].selected = true;
	}
	return selObj.options.length;
}

/**
 * De-highlights (de-selects) every option in this select element
 * @param selObj Object  The select element object to be acted upon
 * @return int  The number of elements in the element
 */
function selectSelectNone(selObj)
{
	for (var i = 0, len = selObj.options.length; i < len; i++) {
		selObj.options[i].selected = false;
	}
	return selObj.options.length;
}

/**
 * Highlights (selects) every option in this select element
 * @param selObj object  The select object to be acted upon
 * @return int  The number of elements in the element
 */
function selectSelectVal(selObj, val)
{
	var retVal = 0;
	for (var i = 0, len = selObj.options.length; i < len; i++) {
		if (selObj.options[i].value == val) {
			selObj.options[i].selected = true;
			retVal = i;
		}
		else {
			selObj.options[i].selected = false;
		}
	}
	return retVal;
}


/**
 * Extract all the selected values from a multiple select input and
 * return those values as an array. If the flags include SEL_ARR_ALL, then
 * all options are used, not just the selected ones.
 * @param selObj object  The select object to be acted upon
 * @return array  An array of the text of all the required options
 */
function selectToArray(selObj, flags)
{
	if (isUndefined(flags)) { flags = 0; }
	var getAll = (((flags & SEL_GET_ALL) > 0) ? true : false);
	var retVal = new Array();
	for (var i = 0, len = selObj.options.length; i < len; i++) {
		if ((getAll) || (selObj.options[i].selected == true)) {
			var nextIndex = retVal.length;
			retVal[nextIndex] = selObj.options[i].value;
		}
	}
	return retVal;
}

/**
 * Extract all the selected values from a multiple select input and
 * return those values as a hashtable. If the flags include SEL_GET_ALL, then
 * all options are used, not just the selected ones.
 * @requires  The Hashtable object from lib.hashtable.js
 * @param selObj object  The select object to be acted upon
 * @return array  A hashtable of the values=>text of all the required options
 */
function selectToHashtable(selObj, flags)
{
	if (isUndefined(flags)) { flags = 0; }
	var getAll = (((flags & SEL_GET_ALL) > 0) ? true : false);
	var retVal = new Hashtable();
	for (var i = 0, len = selObj.options.length; i < len; i++) {
		if ((getAll) || (selObj.options[i].selected == true)) {
			retVal.push(selObj.options[i].text, selObj.options[i].value);
		}
	}
	return retVal;
}

/**
 * Creates a string representation of the given select object
 * @param selObj object  The select object to be acted upon
 * @return string  The string showing the data in the select
 */
function selectToString(selObj)
{
	var opt, outStr = '';
	for (var i = 0, len = selObj.options.length; i < len; i++) {
		opt = selObj.options[i];
		outStr += i+': '+opt.value+'=>'+opt.text+(opt.selected ? '*' : '')+',\n';
	}
	return outStr;
}

