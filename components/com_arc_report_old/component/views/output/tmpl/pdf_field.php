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

$w = $this->v->getwidth();
$h = $this->v->getHeight();
$x = $this->v->getX();

$y = $this->v->getY() + $this->reportTop;
$nl = $this->v->titleClearance == -1;
$right = $this->width - ($x + $w);

$leftPadding = $this->v->getLeftPadding();
$rightPadding = $this->v->getRightPadding();
$topPadding = $this->v->getTopPadding();
$bottomPadding = $this->v->getBottomPadding();

$titleSize = $this->v->getTitleFontSize();
$valueSize = $this->v->getDataFontSize();

$titleHeight = $titleSize * 0.3527; // see http://en.wikipedia.org/wiki/Point_(typography) for explanation of this magic number
$cleared = ($this->v->titleClearance > $titleHeight);

$this->pdf->setLeftMargin ( $x + $leftPadding );
$this->pdf->setRightMargin( $right + $rightPadding );
$this->pdf->setTopMargin( $y + $topPadding );

// make a new cell if the field requires it
if( $this->v->ownBox ) {
	if( $this->v->hasBorder ) {
		$this->pdf->Rect( $x, $y, $w, $h, 'D' );
	}
	$this->pdf->setLastH( 0 );
	$y = $y + $topPadding;
	$this->pdf->setY( $y + $this->v->titleMarginTop );
}
$this->pdf->setX( $x + $leftPadding );

// prepare string length info if we are center aligning
$tLen = 0;
if( ($this->v->titleAlign == 'C') || ($this->v->dataAlign == 'C') ) {
	// get length of title text
	$this->pdf->setFont($this->font, 'B', $titleSize);
	// The extra character (.'A') here and a few lines down is so that the text actually appears centrally. It's a hack, but hey ho.
	$tLen = $this->pdf->getStringWidth( ($this->v->showTitle ? $this->v->titlePdf().'A' : '') );
	// get length of data text
	if( !$this->v->valueAsTitle ) {
		$this->pdf->setFont($this->font, '', $valueSize);
	}
	$dLen = $this->pdf->getStringWidth($this->v->dataPdf().'A');
}

// title
$this->pdf->setFont($this->font, 'B', $titleSize);
if( (($txt = $this->v->titlePdf()) != '') && $this->v->showTitle ) {
	if($this->v->titleAlign == 'C') {
		echo 'tLen: '.$tLen.'<br />';
		echo 'dLen: '.$dLen.'<br />';
		$len = $tLen + ($nl ? 0 : $dLen);
		$gap = ($w - $len) / 2;
		echo 'gap: '.$gap.'<br />';
		$this->pdf->setX($x + $gap);
	}
	
	$this->pdf->writeHTML( $txt, $nl, 0, true );
}

if( $nl || $cleared ) {
	$this->pdf->setX( $x + $leftPadding );
}

if( !$nl ) {
	$this->pdf->setXY( $this->pdf->getX(), $y + $this->v->titleClearance );
}

// data
if( !$this->v->valueAsTitle ) {
	$this->pdf->setFont($this->font, '', $valueSize);
}

if( ($txt = $this->v->dataPdf()) != '') {
	if( ($this->v->dataAlign == 'C') && (($this->v->showTitle && ($nl || $cleared)) || !$this->v->showTitle ) ) {
		$len = (($nl || $cleared) ? 0 : $tLen) + $dLen;
		$gap = ($w - $len) / 2;
		$this->pdf->setX($x + $gap);
	}
	else {
	}
	
	// break up lines based on newlines
	$parts = preg_split('/\\n/', $txt, -1, PREG_SPLIT_NO_EMPTY );
	
	if(count($parts) > 1) {
		foreach( $parts as $txt ) {
		$this->pdf->writeHTML( rtrim($txt), true, 0, true );
		}
	}
	else{
		$this->pdf->writeHTML( rtrim($txt), true, 0, true );
	}
}
?>