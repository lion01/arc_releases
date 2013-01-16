<?php
/**
 * @package     Arc
 * @subpackage  Behaviour
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

// Called directly by html view
if( !isset($_GET['write']) || !(bool)$_GET['write'] ) {
	ArcGraphBehaviour::main();
}

class ArcGraphBehaviour
{
	function main()
	{
		// ###  prepare the graph area and retrieve the data  ###
		ob_start();
		
		// get the data
		$series = array();
		$fileName = base64_decode( $_GET['file'] );
		$file = fopen( $fileName, 'r' );
		if( $file !== false ) {
			$dates = fgets($file);
			$p = strpos( $dates, ':' );
			$dates = unserialize( substr($dates, ($p+1)) );
			while( $line = fgets($file) ) {
				$p = strpos( $line, ':' );
				$id = substr( $line, 0, $p );
				$series[$id] = unserialize( substr($line, ($p+1)) );
			}
		}
		if( empty($series) ) {
			die('no data');
		}
		unlink( $fileName ); // don't leave potentially sensitive data around any longer than absolutely needed
		foreach( $series as $sId=>$data ) {
			$meta[$sId] = $data['_meta'];
			unset($series[$sId]['_meta']);
		}
		
		// image properties
		$height1 = (int)$_GET['h1'];
		$height2 = (int)$_GET['h2'];
		$width = (int)$_GET['w'];
		$labels = (bool)$_GET['labels'];
		$yRes = 5;
		$ySnap = 10;
		putenv('GDFONTPATH=' . realpath('.'));
		$thisDir = dirname(__FILE__);
		$font = $thisDir.'/arial.ttf'; 
		$fontBold = $thisDir.'/arialbd.ttf'; 
		
		// set up x scales
		$count = count( $dates );
		$rawPointWidth = $width / $count;
		$pointWidth = floor($width / $count);
		
		if( $pointWidth == 0 ) {
			// allow fractional point width if not doing so would lead to div-by-zero
			if( $width != 0 ) {
				$pointWidth = $width / $count;
			}
			// set a non-zero value in the worst case
			else {
				$pointWidth = 1;
			}
		}
		$width = $count * $pointWidth;
		
		// set up
		$img = imagecreatetruecolor( $width, ($height1 + $height2) );
		$colors['debug']     = imagecolorallocate( $img, 255,   0,   0 );
		$colors['bg']        = imagecolorallocate( $img, 255, 255, 255 );
		$colors['divider']   = imagecolorallocate( $img, 150, 150, 150 );
		$colors['axes']      = imagecolorallocate( $img,   0,   0,   0 );
		$colors['scales']    = imagecolorallocatealpha( $img, 100, 100, 100, 70 );
		$colors['labels']    = imagecolorallocatealpha( $img,  85,  85,  85,  0 );
		$colors['labelsBg']  = imagecolorallocatealpha( $img, 255, 255, 255,  0 );
		$colors['series'][]  = imagecolorallocatealpha( $img,   0,   0, 255, 70 );
		$colors['series'][]  = imagecolorallocatealpha( $img,   0, 180, 202, 70 );
		$colors['series'][]  = imagecolorallocatealpha( $img, 158, 111,   0, 70 );
		$colors['series'][]  = imagecolorallocatealpha( $img, 255, 167, 103, 70 );
		$colors['series'][]  = imagecolorallocatealpha( $img,  26, 167,  26, 70 );
		$colors['series'][]  = imagecolorallocatealpha( $img, 212,   0, 185, 70 );
		$colors['series'][]  = imagecolorallocatealpha( $img, 211,   0,  21, 70 );
		$colors['series'][]  = imagecolorallocatealpha( $img, 211, 247,  21, 70 );
		$colors['highlight'] = imagecolorallocatealpha( $img, 175, 238, 238, 90 );
		$colors['counts']['Purple'] = imagecolorallocate( $img, 107,  47, 154 );
		$colors['counts']['Red']    = imagecolorallocate( $img, 210,  27,  24 );
		$colors['counts']['Amber']  = imagecolorallocate( $img, 255, 164,  14 );
		$colors['counts']['Grey']   = imagecolorallocate( $img, 156, 154, 156 );
		$colors['counts']['Green']  = imagecolorallocate( $img,  84, 148,   9 );
		$colors['counts']['Gold']   = imagecolorallocate( $img, 249, 234, 151 );
		$colors['counts']['Clear']  = imagecolorallocate( $img, 211, 211, 211 );
		
		imagefilledrectangle($img, 0, 0, $width, ($height1 + $height2), $colors['bg']);
		
		
		// ###  Cumulative scores  ###
		// parse the data to set up y scales
		$tmp = reset($meta);
		$min = $max = $tmp['init'];
		foreach( $meta as $sId=>$mData ) {
			if( $mData['min'] < $min ) { $min = $mData['min']; }
			if( $mData['max'] > $max ) { $max = $mData['max']; }
		}
		if( $min == 0 && $max == 0 ) { $max = 1; }
		$min = floor($min / $ySnap) * $ySnap;
		$max = ceil( $max / $ySnap) * $ySnap;
		$range = ( $max == $min ) ? $ySnap : ($max - $min);
		$scale = $height1 / $range;
		$offset = 0;
		ArcGraphBehaviour::y( null, $offset, $max, $scale ); // setup static cache of values for y()
		//echo 'min: '.$min.' max: '.$max.' range: '.$range.' scale: '.$scale.' 0->'.y(0).', 20->'.y(20);
		
		// draw axes
		imagesetthickness( $img, 1 );
		// ... x
		if( $min <= 0 && $max > 0 ) {
			$yOfxAxis = 0;
			if( ArcGraphBehaviour::y(0) > $height1 - 75 ) {
				$labelsOver = true;
			}
			elseif( ArcGraphBehaviour::y(0) < 75 ) {
				$labelsOver = false;
			}
			else {
				$labelsOver = ( ($min + $max) < 0 );
			}
		}
		elseif( $min > 0 ) {
			$yOfxAxis = $min;
			$labelsOver = true;
		}
		else {
			$yOfxAxis = $max;
			$labelsOver = false;
		}
		if( $labelsOver ) {
			$labelOffsetX = 0;
			$labelOffsetY = 0;
		}
		else {
			$labelOffsetX = 40;
			$labelOffsetY = 75;
		}
		imageline( $img, 0, ArcGraphBehaviour::y($yOfxAxis), $width, ArcGraphBehaviour::y($yOfxAxis), $colors['axes'] );
		// ... y
		imageline( $img, 0, ArcGraphBehaviour::y($max), 0, ArcGraphBehaviour::y($min), $colors['axes'] );
		
		if( $labels ) {
			// x-axis markers
			$s = reset($series);
			$x = 0;
			$xTarget = 0;
			$yL = ArcGraphBehaviour::y($yOfxAxis) - 8;
			$yM1 = ArcGraphBehaviour::y($yOfxAxis) + 5;
			$yM2 = ArcGraphBehaviour::y($yOfxAxis) - 5;
			$yS1 = ArcGraphBehaviour::y($yOfxAxis) + 2;
			$yS2 = ArcGraphBehaviour::y($yOfxAxis) - 2;
			reset($s);
			$i = date( 'N', strtotime(key($s)));
			foreach( $s as $date=>$val ) {
				if( $i % 7 == 1 ) {
					imageline( $img, $x, $yM1, $x, $yM2, $colors['axes'] );
				}
				elseif( $rawPointWidth >= 2 ) {
					imageline( $img, $x, $yS1, $x, $yS2, $colors['axes'] );
				}
				if( ($i % 7 == 1) && ( ($i * $pointWidth) > $xTarget) ) {
					$xTarget = ($i * $pointWidth) + 30;
					imagettftext($img, 10, 45, ($x - $labelOffsetX), ($yL + $labelOffsetY), $colors['scales'], $font, $date);
				}
				$x += $pointWidth;
				$i++;
			}
			unset( $s );
		}
		
		// y-axis markers
		$yTarget = ArcGraphBehaviour::y($min) + 1;
		for( $y = $min; $y <= $max; $y += 5 ) {
			imageline( $img, 0, ArcGraphBehaviour::y($y), 2, ArcGraphBehaviour::y($y), $colors['axes'] );
			if( ArcGraphBehaviour::y($y) < $yTarget ) {
				$yTarget = ArcGraphBehaviour::y($y) - 10;
				imageline( $img, 2, ArcGraphBehaviour::y($y), 4, ArcGraphBehaviour::y($y), $colors['axes'] );
				imagettftext($img, 10, 0, 10, (ArcGraphBehaviour::y($y)+5), $colors['scales'], $font, abs($y));
			}
		}
		
		// draw cumulative lines
		imagesetthickness( $img, 2 );
		$c = reset( $colors['series'] );
		foreach( $series as $sId=>$data ) {
			$x = 0;
			$prev = array( 'total'=>$meta[$sId]['init'] );
			foreach( $dates as $date ) {
				$point = ( isset( $data[$date] ) ? $data[$date] : $prev );
				if( $date == $meta[$sId]['highlight'] ) {
					imagefilledrectangle( $img, $x + 1, 0, $x + $pointWidth - 1, ArcGraphBehaviour::y($min), $colors['highlight'] );
				}
				imageline( $img, $x, ArcGraphBehaviour::y($prev['total']), ($x += $pointWidth), ArcGraphBehaviour::y($point['total']), $c );
				$prev = $point;
			}
			$c = next( $colors['series'] );
			if( $c === false ) {
				$c = reset( $colors['series'] );
			}
		}
		
		
		// ###  Message count tallies  ###
		// parse the data to set up y scales
		$min = $max = 0;
		foreach( $meta as $sId=>$mData ) {
			if( $mData['tallyMax'] > $max ) { $max = $mData['tallyMax']; }
		}
		if( $min == 0 && $max == 0 ) { $max = 1; }
		$countHeight = $height2 / count($series);
		$range = $max - $min;
		$scale = ($countHeight - 4) / $range;
		$offset = $height1 - 1;
		ArcGraphBehaviour::y( null, $offset, $max, $scale ); // setup static cache of values for y()
		//echo 'i: '.$min.' x: '.$max.' r: '.$range.' scale:'.$scale.' 0->'.y(0).', 20->'.y(20);
		
		// draw bars
		if( $height2 != 0 ) {
			$c = reset( $colors['series'] );
			foreach( $series as $sId=>$data ) {
				imagefilledrectangle( $img, 0, ($offset + $countHeight - 2), $width, ($offset + $countHeight), $c );
				$c = next( $colors['series'] );
				if( $c === false ) {
					$c = reset( $colors['series'] );
				}
				
				$x1 = 1;
				$x2 = $pointWidth - 1;
				foreach( $dates as $date ) {
					$point = ( isset( $data[$date] ) ? $data[$date] : array( 'tallies'=>array() ) );
					$y = 0;
					$baseOffset = 2;
					foreach( $point['tallies'] as $color=>$num ) {
						imagefilledrectangle( $img, $x1, (ArcGraphBehaviour::y($y+$num) + $baseOffset), $x2, (ArcGraphBehaviour::y($y) + $baseOffset), $colors['counts'][$color] );
						$y += $num;
					}
					$x1 += $pointWidth;
					$x2 += $pointWidth;
				}
				
				if( $labels ) {
					$fontSize = min( 8, $countHeight - 4 );
					$xBase = 5;
					$y0 = ArcGraphBehaviour::y(0) - 5;
					imagettftext( $img, $fontSize, 0, ($xBase + 1), ($y0 - 1), $colors['labelsBg'], $fontBold, $meta[$sId]['label'] );
					imagettftext( $img, $fontSize, 0, ($xBase + 1), ($y0 - 3), $colors['labelsBg'], $fontBold, $meta[$sId]['label'] );
					imagettftext( $img, $fontSize, 0, ($xBase - 1), ($y0 - 1), $colors['labelsBg'], $fontBold, $meta[$sId]['label'] );
					imagettftext( $img, $fontSize, 0, ($xBase - 1), ($y0 - 3), $colors['labelsBg'], $fontBold, $meta[$sId]['label'] );
					imagettftext( $img, $fontSize, 0, ($xBase),     ($y0 - 2), $colors['labels'],   $fontBold, $meta[$sId]['label'] );
				}
				
				$offset += $countHeight;
				ArcGraphBehaviour::y( null, $offset, $max, $scale ); // setup static cache of values for y()
			}
		}
		
		$o = ob_get_clean();
		if( !empty($o) ) {
			imagestring( $img, 4, 30, 35, $o, $colors['debug']);
		}
		
		// output all that
		//	echo $o;
		if( isset($_GET['write']) && (bool)$_GET['write'] ) {
			imagepng( $img, $fileName.'.png' );
		}
		else {
			header( "Content-type: image/png" );
			imagepng( $img );
		}
		
		// tidy up
		foreach( $colors as $k=>$v ) {
			if( !is_array($v) ) {
				imagecolordeallocate( $img, $colors[$k] );
			}
		}
		imagedestroy( $img );
	}
	
	function y( $y, $offset = null, $max = null, $scale = null )
	{
		static $statOffset, $statMax, $statScale;
		if( is_null($y) ) {
			$statOffset = $offset;
			$statMax = $max;
			$statScale = $scale;
		}
		return ( ($statMax - $y) * $statScale ) + $statOffset;
	}
}
?>