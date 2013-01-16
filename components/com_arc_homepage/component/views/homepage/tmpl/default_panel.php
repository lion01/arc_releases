<?php
/**
 * @package     Arc
 * @subpackage  Homepage
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<!-- start of panel -->
<?php
$panel = &$this->panel;
$alt = htmlspecialchars( $panel->getAltText() );

$s = array();
$w = $panel->getParam( 'width' );
$h = $panel->getParam( 'height' );
if( !is_null($w) ) {
	$s[] = 'width: '.$w;
}
if( !is_null($h) ) {
	$s[] = 'height: '.$h;
}

if( !empty($s) ) {
	$s = ' style="'.implode( ';', $s ).'"';
}
else {
	$s = '';
}

echo '<div class="panel"'.$s.'>'."\n";
echo '<div class="panel_inner">'."\n";
echo '<div class="panel_header"><h3>'.$panel->getAltText().'</h3></div>'."\n";
switch( $panel->getType() ) {
case( 'external' ):
	$url = $panel->getUrl();
	echo '<!--[if IE]><iframe class="panel_body" src="'.$url.'">'.$alt.'</iframe><![endif]-->'
		."\n".'<!--[if !IE]> --><object class="panel_body" data="'.$url.'">'.$alt.'</object><!-- <![endif]-->';
	break;

case( 'internal' ):
	$url = $panel->getUrl();
//	curl_setopt( $this->c, CURLOPT_URL, JURI::base().$url );
//	echo '<div class="panel_body">'.curl_exec( $this->c ).'</div>';

	$id = md5( $url );
	$cssArray = $panel->getCSS();
	$jscriptArray = $panel->getJscript();
	$onComplete = '';
	
	if( !is_null($cssArray) || !is_null($jscriptArray) ) {
		$onComplete .= ',onComplete: function() {'."\n";
	}
	else {
		$onComplete .= '';
	}
	
	if( !is_null($cssArray) ) {
		foreach( $cssArray as $css ) {
			$onComplete .= 'new Asset.css(\''.$css.'\');'."\n";
		}
	}
		
	if( !is_null($jscriptArray) ) {
		foreach( $jscriptArray as $jscript ) {
			$onComplete .= 'new Asset.javascript(\''.$jscript.'\');'."\n";
		}
	}
		
	if( isset($css) || isset($jscript) ) {
		$onComplete .= '}';
	}
	else {
		$onComplete .= '';
	}
	
	echo '<div class="panel_body" id="'.$id.'">
	<script>
	new Ajax( "'.$url.'", { 
		method: \'get\',
		update: $(\''.$id.'\')'
		.(!empty( $onComplete ) ? $onComplete : '')
	.'}).request();
	</script>'.JHTML::_( 'arc.loading' ).'</div>';
	break;

case( 'faked' ):
	echo '<div class="panel_body">'.$panel->getText().'</div>';
	break;

case( 'module' ):
	echo '<div class="panel_body">'.JModuleHelper::renderModule( $panel->getModule() ).'</div>';
	break;
}
?>
</div>
</div>
<!-- end of panel -->