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
<script type="text/javascript" language="javascript">
bullet = document.getElementById( 'bullet' );
var bulletStr = bullet.value;
var widths = new Hash();

function lineChecker()
{
	<?php foreach( $this->counted as $v ) : ?>
		<?php $n = $v->getName(); ?>
		<?php $fn = $v->getFontName(); ?>
		// set up all new font data
		<?php if( array_search($fn, $this->doneFonts) === false ) : ?>
			<?php $this->doneFonts[] = $fn; ?>
			font_<?php echo $fn; ?> = new Hash();
			<?php
			$cw = $v->getFontWidths();
			foreach($cw as $k=>$val) {
				echo 'font_'.$fn.'.set(\''.$k.'\', \''.$val.'\');';
			} ?>
			widths.set('<?php echo $fn; ?>', font_<?php echo $fn; ?>);
		<?php endif; ?>
		
		// set up the events for the input
		var txtObj_<?php echo $n; ?> = document.getElementById('<?php echo $n; ?>');
		txtObj_<?php echo $n; ?>.onchange = txtObj_<?php echo $n; ?>.onkeydown = txtObj_<?php echo $n; ?>.onkeyup = function() {
			var targ = document.getElementById('linecount_<?php echo $n; ?>');
			var lMax = <?php echo $v->getLineMax(); ?>;
			var lCount = countLines(txtObj_<?php echo $n; ?>, <?php echo $v->getLineLength(); ?>, '<?php echo $fn; ?>', <?php echo $v->getDataFontSize() / $v->getFontScale(); ?>);
			
			if( lCount > lMax ) {
				targ.style.color = 'red';
				txtObj_<?php echo $n; ?>.style.color = 'red';
			}
			else {
				targ.style.color = 'black';
				txtObj_<?php echo $n; ?>.style.color = 'black';
			}
			targ.innerHTML = lCount + '/' + lMax;
		}
		txtObj_<?php echo $n; ?>.onchange();

	<?php endforeach; ?>
}

<?php if( !empty($this->counted) ) : ?>
function countLines( txtObj, lineLen, txtFontName, txtFontSize )
{
	var font = widths.get(txtFontName);
	var l = 0;
	var spaces = 0;
	var w = lineLen;
	var word = 0;
	var total = 0;
	var str = txtObj.value;
	str = str.replace(/\r\n/g, "\n");
	str = str.replace(/\r/g, "\n");
	
	var chars = str.split('');
	var boundary = /\s/; // non-word characters form boundaries

	for( var i = 0; i < chars.length; i++ ) {
		
		var myChar = chars[i];
		var prev = i-1;

		if( font.hasKey(myChar) ) {
			cw = font.get(myChar);
		}
		else if( font.hasKey(myChar.charCodeAt(0)) ) {
			cw = font.get(myChar.charCodeAt(0));
		}
		else {
			cw = 500; // just a fairly good average character width
		}
		cw = cw * txtFontSize / 1000;
		
		if( myChar == "\n" ) {
			// only add a new line if we're not going to leave one empty
			// (remember we use PREG_SPLIT_NO_EMPTY in pdf_field.php which does the final pdf output)
			w = w + word;
			word = 0;
			if( w > 0 ) {
				l = l + 1;
				w = 0;
			}
		}
		
		else if( boundary.test(myChar) ) {
			
			if( spaces == 0 ) {
				w += word;
				word = 0;
			}

			if( w !== 0) {
				spaces = cw;
			}
			
			if( (w + spaces) > lineLen ) {
				spaces = 0;
			}
		}

		else {
			if( word == 0 ) {
				w += spaces;
				spaces = 0;
			}

			word = word + cw;
					
			if( (w + word) > lineLen ) {

				l = l + 1;
				w = 0;
			}
		}
	}

	return l;
}
<?php endif; ?>

</script>
