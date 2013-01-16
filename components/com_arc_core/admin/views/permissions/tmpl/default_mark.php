<?php
// Determine which image to use
switch( $this->state ) {
case( 'allowed' ):
	$title = 'allowed';
	$image = 'images/tick.png';
	break;

case( 'restricted' ):
	$title = 'restricted';
	$image = 'components/com_arc_core/images/tick_blue.png';
	break;

case( 'denied' ):
	$title = 'denied';
	$image = 'images/publish_x.png'; 
	break;
}
?>
<td><a href="javascript:void(0);" onclick="toggle( this.getParent(), <?php echo $this->rId; ?>, <?php echo $this->aId; ?> )" title="<?php echo $title; ?>">
	<?php echo '<img src="'.$image.'" />'; ?>
</a></td>