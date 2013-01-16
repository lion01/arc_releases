<?php
/**
 * @package     Arc
 * @subpackage  Attendance
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

$pId = reset( $this->model->getPupilList($this->sheetId) );

// Names
$legal = ApotheosisData::_( 'people.displayname', $pId, 'pupil', true );
$pref = ApotheosisData::_( 'people.displayname', $pId, 'pupil' );
$personalRows['Name'] = $legal;
$personalRows['Chosen name'] = $legal;

// Personal
$personInfo = ApotheosisData::_( 'people.person', $pId );
$dob = date( 'd/m/Y', strtotime($personInfo->dob) );
$upn = $personInfo->upn;
$personalRows['Date of Birth'] = $dob;
$personalRows['UPN'] = $upn;

// Tutor
$tutorGroup = ApotheosisData::_( 'timetable.tutorgroup', $pId );
$tutorGroupName = ApotheosisData::_( 'course.name', $tutorGroup );
$personalRows['Tutor Group'] = $tutorGroupName;
$tutors = ApotheosisData::_( 'timetable.teachers', $tutorGroup );
foreach( $tutors as $k=>$tutor ) {
	$tutors[$k] = ApotheosisData::_( 'people.displayname', $tutor, 'teacher' );
}
$tutorTitle = ( count($tutors) > 1 ) ? 'Tutors' : 'Tutor';
$personalRows[$tutorTitle] = implode( '<br />', $tutors );

// Relations
$relationInfo = ApotheosisData::_( 'people.relations', $pId );
foreach( $relationInfo as $relation ) {
	$personalRows[$relation->description] = ApotheosisData::_( 'people.displayname', $relation->relation_id, 'person' );
}

// Address
$addressInfo = reset( ApotheosisData::_('people.address', $pId) );
$address = array();
$line1 = array();
if( !is_null($addressInfo->apartment) ) {
	$line1[] = $addressInfo->apartment;
}
if( !is_null($addressInfo->name) ) {
	$line1[] = $addressInfo->name;
}
if( !empty($line1) ) {
	$address[] = implode( ' ', $line1 );
}
$number = array();
if( !is_null($addressInfo->number) ) {
	$number[] = $addressInfo->number;
}
if( !is_null($addressInfo->number_range) ) {
	$number[] = $addressInfo->number_range;
}
$numberRange = array();
if( !empty($number) ) {
	$numberRange[] = implode( '-', $number );
}
if( !is_null($addressInfo->number_suffix) ) {
	$numberRange[] = $addressInfo->number_suffix;
}
$line2 = array();
if( !empty($numberRange) ) {
	$line2[] = implode( '', $numberRange );
}
if( !empty($addressInfo->street) ) {
	$line2[] = $addressInfo->street;
}
if( !empty($line2) ) {
	$address[] = implode( ' ', $line2 );
}
if( !empty($addressInfo->district) ) {
	$address[] = $addressInfo->district;
}
if( !empty($addressInfo->town) ) {
	$address[] = $addressInfo->town;
}
if( !empty($addressInfo->county) ) {
	$address[] = $addressInfo->county;
}
if( !empty($addressInfo->postcode) ) {
	$address[] = $addressInfo->postcode;
}
$personalRows['Address'] = implode( '<br />', $address );
ob_start();
?>
<table cellpadding="2" cellspacing="0" border="1">
	<?php foreach( $personalRows as $title=>$value ) : ?>
		<?php $this->setPdfStrWidth( $title, 'personal_title' ); ?>
		<tr>
			<td width="~title~" align="right"><?php echo $title; ?></td>
			<td width="~value~"><?php echo $value; ?></td>
		</tr>
	<?php endforeach; ?>
</table>
<?php
$personalTable = ob_get_clean();
echo $this->setPersonalTableColWidths( $personalTable, 'personal_title' );
?>
