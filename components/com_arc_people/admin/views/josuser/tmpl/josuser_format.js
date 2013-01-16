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

window.addEvent( 'domready', function()
{
	// get an instance of the page variables class
	pageVars = new PageVarsClass();
	
	// watch the sample inputs for changes
	pageVars.firstnameIn.addEvent( 'keyup', function() {
		pageVars.updatePreview();
	});
	
	pageVars.middlenameIn.addEvent( 'keyup', function() {
		pageVars.updatePreview();
	});
	
	pageVars.surnameIn.addEvent( 'keyup', function() {
		pageVars.updatePreview();
	});
	
	pageVars.emailIn.addEvent( 'keyup', function() {
		pageVars.updatePreview();
	});
	
	// watch the format inputs for changes
	pageVars.fullnameIn1.addEvent( 'keyup', function() {
		pageVars.updatePreview();
	});
	
	pageVars.usernameIn1.addEvent( 'keyup', function() {
		pageVars.updatePreview();
	});
	
	pageVars.emailFrIn1.addEvent( 'keyup', function() {
		pageVars.updatePreview();
	});
	
	pageVars.fullnameIn2.addEvent( 'keyup', function() {
		pageVars.updatePreview();
	});
	
	pageVars.usernameIn2.addEvent( 'keyup', function() {
		pageVars.updatePreview();
	});
	
	pageVars.emailFrIn2.addEvent( 'keyup', function() {
		pageVars.updatePreview();
	});
	
	pageVars.fullnameIn3.addEvent( 'keyup', function() {
		pageVars.updatePreview();
	});
	
	pageVars.usernameIn3.addEvent( 'keyup', function() {
		pageVars.updatePreview();
	});
	
	pageVars.emailFrIn3.addEvent( 'keyup', function() {
		pageVars.updatePreview();
	});
	
	// show preview of the sample text
	pageVars.updatePreview();
});

/**
 * Class to handle manipulation of page variables
 */
var PageVarsClass = new Class({
	/**
	 * Construct the class by getting references to all the html elements we will be using
	 */
	initialize: function() {
		// get the example inputs
		this.firstnameIn =  $( 'firstname_in' );
		this.middlenameIn = $( 'middlename_in' );
		this.surnameIn =    $( 'surname_in' );
		this.emailIn =      $( 'email_in' );
		this.curDomain =    $( 'cur_domain' );
		
		// get the format inputs
		this.fullnameIn1 = $( 'paramsfullname1' );
		this.usernameIn1 = $( 'paramsusername1' );
		this.emailFrIn1 =  $( 'paramsemail1' );
		
		this.fullnameIn2 = $( 'paramsfullname2' );
		this.usernameIn2 = $( 'paramsusername2' );
		this.emailFrIn2 =  $( 'paramsemail2' );
		
		this.fullnameIn3 = $( 'paramsfullname3' );
		this.usernameIn3 = $( 'paramsusername3' );
		this.emailFrIn3 =  $( 'paramsemail3' );
		
		// get the example table cells
		this.fullnameCell1 = $( 'fullname_ex_1' );
		this.usernameCell1 = $( 'username_ex_1' );
		this.emailCell1 =    $( 'email_ex_1' );
		
		this.fullnameCell2 = $( 'fullname_ex_2' );
		this.usernameCell2 = $( 'username_ex_2' );
		this.emailCell2 =    $( 'email_ex_2' );
		
		this.fullnameCell3 = $( 'fullname_ex_3' );
		this.usernameCell3 = $( 'username_ex_3' );
		this.emailCell3 =    $( 'email_ex_3' );
	},
	
	/**
	 * Update the previews when any sample texts or formats are changed
	 */
	updatePreview: function() {
		// the Joomla email safe chars
		var joomlaSafe = /[^A-Za-z0-9!#&*+=?_-]/;
		
		// array to hold strings of examples to NOT update
		this.noReplace = new Array();
		
		// get all the info we need and their variations
		this.fullForm1 =  this.fullnameIn1.getValue();
		this.userForm1 =  this.usernameIn1.getValue();
		this.emailForm1 = this.emailFrIn1.getValue();
		
		this.fullForm2 =  this.fullnameIn2.getValue();
		this.userForm2 =  this.usernameIn2.getValue();
		this.emailForm2 = this.emailFrIn2.getValue();
		
		this.fullForm3 =  this.fullnameIn3.getValue();
		this.userForm3 =  this.usernameIn3.getValue();
		this.emailForm3 = this.emailFrIn3.getValue();
		
		this.firstStr =   this.firstnameIn.getValue().replace( joomlaSafe, '' );
		this.ucFirstStr = this.ucfirst( this.firstStr );
		this.lcFirstStr = this.firstStr.toLowerCase();
		this.ucFirstInitStr = this.upperInit( this.firstStr );
		this.lcFirstInitStr = this.lowerInit( this.firstStr );
		if( this.firstStr == '' ) {
			this.noReplace.include( 'firstname' );
		}
		
		this.midStr =   this.middlenameIn.getValue().replace( joomlaSafe, '' );
		this.ucMidStr = this.ucfirst( this.midStr );
		this.lcMidStr = this.midStr.toLowerCase();
		this.ucMidInitStr = this.upperInit( this.midStr );
		this.lcMidInitStr = this.lowerInit( this.midStr );
		if( this.midStr == '' ) {
			this.noReplace.include( 'middlename' );
		}
		
		this.surStr =   this.surnameIn.getValue().replace( joomlaSafe, '' );
		this.ucSurStr = this.ucfirst( this.surStr );
		this.lcSurStr = this.surStr.toLowerCase();
		this.ucSurInitStr = this.upperInit( this.surStr );
		this.lcSurInitStr = this.lowerInit( this.surStr );
		if( this.surStr == '' ) {
			this.noReplace.include( 'surname' );
		}
		
		this.emailStr =     this.emailIn.getValue();
		if( this.emailStr == '' ) {
			this.noReplace.include( 'email' );
		}
		
		this.curDomainStr = this.curDomain.getText().replace( joomlaSafe, '' );
		
		// now update the previews
		this.fullnameCell1.setHTML( this.replaceKeywords(this.fullForm1) );
		this.usernameCell1.setHTML( this.replaceKeywords(this.userForm1) );
		this.emailCell1.setHTML( this.replaceKeywords(this.emailForm1) );
		
		this.fullnameCell2.setHTML( this.replaceKeywords(this.fullForm2) );
		this.usernameCell2.setHTML( this.replaceKeywords(this.userForm2) );
		this.emailCell2.setHTML( this.replaceKeywords(this.emailForm2) );
		
		this.fullnameCell3.setHTML( this.replaceKeywords(this.fullForm3) );
		this.usernameCell3.setHTML( this.replaceKeywords(this.userForm3) );
		this.emailCell3.setHTML( this.replaceKeywords(this.emailForm3) );
	},
	
	/**
	 * Upper case just the first character of a given string
	 * whilst explicitly making all remaining characters lower case
	 */
	ucfirst: function( string ) {
		return string.charAt(0).toUpperCase() + string.toLowerCase().slice(1);
	},
	
	/**
	 * Upper case and return the first letter of the string
	 */
	upperInit: function( string ) {
		return string.charAt(0).toUpperCase();
	},
	
	/**
	 * Lower case and return the first letter of the string
	 */
	lowerInit: function( string ) {
		return string.charAt(0).toLowerCase();
	},
	
	/**
	 * Replace all the keywords in a given format string
	 * 
	 * @param string format  The format string whose keywords we wish to replace
	 * @return string newStr  The sample text with all keywords replaced
	 */
	replaceKeywords: function( format ) {
		// re-declare format string
		var newStr = format;
		
		// Firstname
		if( !this.noReplace.contains('firstname') ) {
			newStr = newStr.replace( /\[\[uc_firstname\]\]/g, this.ucFirstStr );
			newStr = newStr.replace( /\[\[lc_firstname\]\]/g, this.lcFirstStr );
			newStr = newStr.replace( /\[\[as_firstname\]\]/g, this.firstStr );
			newStr = newStr.replace( /\[\[uc_firstinit\]\]/g, this.ucFirstInitStr );
			newStr = newStr.replace( /\[\[lc_firstinit\]\]/g, this.lcFirstInitStr );
		}
		else {
			newStr = newStr.replace( /\[\[uc_firstname\]\]/g, '<span style="color: red;">[[uc_firstname]]</span>' );
			newStr = newStr.replace( /\[\[lc_firstname\]\]/g, '<span style="color: red;">[[lc_firstname]]</span>' );
			newStr = newStr.replace( /\[\[as_firstname\]\]/g, '<span style="color: red;">[[as_firstname]]</span>' );
			newStr = newStr.replace( /\[\[uc_firstinit\]\]/g, '<span style="color: red;">[[uc_firstinit]]</span>' );
			newStr = newStr.replace( /\[\[lc_firstinit\]\]/g, '<span style="color: red;">[[lc_firstinit]]</span>' );
		}
		
		// Middlename
		if( !this.noReplace.contains('middlename') ) {
			newStr = newStr.replace( /\[\[uc_middlename\]\]/g, this.ucMidStr );
			newStr = newStr.replace( /\[\[lc_middlename\]\]/g, this.lcMidStr );
			newStr = newStr.replace( /\[\[as_middlename\]\]/g, this.midStr );
			newStr = newStr.replace( /\[\[uc_middleinit\]\]/g, this.ucMidInitStr );
			newStr = newStr.replace( /\[\[lc_middleinit\]\]/g, this.lcMidInitStr );
		}
		else {
			newStr = newStr.replace( /\[\[uc_middlename\]\]/g, '<span style="color: red;">[[uc_middlename]]</span>' );
			newStr = newStr.replace( /\[\[lc_middlename\]\]/g, '<span style="color: red;">[[lc_middlename]]</span>' );
			newStr = newStr.replace( /\[\[as_middlename\]\]/g, '<span style="color: red;">[[as_middlename]]</span>' );
			newStr = newStr.replace( /\[\[uc_middleinit\]\]/g, '<span style="color: red;">[[uc_middleinit]]</span>' );
			newStr = newStr.replace( /\[\[lc_middleinit\]\]/g, '<span style="color: red;">[[lc_middleinit]]</span>' );
		}
		
		// Surname
		if( !this.noReplace.contains('surname') ) {
			newStr = newStr.replace( /\[\[uc_surname\]\]/g, this.ucSurStr );
			newStr = newStr.replace( /\[\[lc_surname\]\]/g, this.lcSurStr );
			newStr = newStr.replace( /\[\[as_surname\]\]/g, this.surStr );
			newStr = newStr.replace( /\[\[uc_surinit\]\]/g, this.ucSurInitStr );
			newStr = newStr.replace( /\[\[lc_surinit\]\]/g, this.lcSurInitStr );
		}
		else {
			newStr = newStr.replace( /\[\[uc_surname\]\]/g, '<span style="color: red;">[[uc_surname]]</span>' );
			newStr = newStr.replace( /\[\[lc_surname\]\]/g, '<span style="color: red;">[[lc_surname]]</span>' );
			newStr = newStr.replace( /\[\[as_surname\]\]/g, '<span style="color: red;">[[as_surname]]</span>' );
			newStr = newStr.replace( /\[\[uc_surinit\]\]/g, '<span style="color: red;">[[uc_surinit]]</span>' );
			newStr = newStr.replace( /\[\[lc_surinit\]\]/g, '<span style="color: red;">[[lc_surinit]]</span>' );
		}
		
		// Email
		if( !this.noReplace.contains('email') ) {
			newStr = newStr.replace( /\[\[email\]\]/g, this.emailStr );
		}
		else {
			newStr = newStr.replace( /\[\[email\]\]/g, '<span style="color: red;">[[email]]</span>' );
		}
		
		newStr = newStr.replace( /\[\[domain\]\]/g, this.curDomainStr );
		
		return newStr;
	}
});