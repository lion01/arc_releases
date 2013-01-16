<?php
	/**
	 * @version		eRef Template 1.0
	 * @copyright	Copyright (C) 2011 Punnet.
	 * @license		GNU General Public License version 2 or later; see LICENSE.txt
	 */

	defined('_JEXEC') or die;

	/* The following line loads the MooTools JavaScript Library */
	JHTML::_('behavior.mootools');

	/* The following line gets the application object for things like displaying the site name */
	$app = JFactory::getApplication();
	$db =& JFactory::getDBO();
	
	$time = date("H:i:s");
	$date = date("d/m/Y");
	
	$user = JFactory::getUser();
	
	$path = 'templates/'.$this->template;
	$pie = $this->baseurl.'/templates/'.$this->template;
?>

<?php echo '<?'; ?>xml version="1.0" encoding="<?php echo $this->_charset ?>"?>

<!DOCTYPE HTML>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >

<head>
	
	<!-- This template uses Entypo http://www.entypo.com/ and Twitter Bootstap http://twitter.github.com/bootstrap/ -->
	<jdoc:include type="head" />
	<link rel="stylesheet" href="<?php echo $path; ?>/css/default.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo $path; ?>/css/arc/default.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo $path; ?>/css/bootstrap.css" type="text/css" />
	<?php	
	$opt = JRequest::getVar( 'option' );
	if( substr( $opt, 0, 8 ) == 'com_arc_' ) {
		JHTML::stylesheet( 'core.css', $path.'/css/arc/' );
		JHTML::stylesheet( substr( $opt, 8 ).'.css', $path.'/css/arc/' );
	}
	
	$palate = $this->params->get('palate');
	JHTML::stylesheet( $palate.'.css', $path.'/css/palates/' );
	?>
	
<!--[if IE 7]>
	<link rel="stylesheet" href="<?php echo $path; ?>/css/arc/ie7.css" type="text/css" />
	<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/scripts/html5.js"></script>
<![endif]-->

<!--[if IE 8]>
	<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/scripts/html5.js"></script>
	<link rel="stylesheet" href="<?php echo $path; ?>/css/arc/ie8.php?pie='.<?php echo urlencode( base64_encode ( $pie ) ); ?>.'" type="text/css" />
<![endif]--> 

	<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/scripts/login.js"></script>

	<link rel="shortcut icon" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/favicon.ico" type="image/x-icon" />

	<meta name="viewport" content="width=device-width, initial-scale=1"/>
</head>

<body>

<div id="wrapper">
	<header>
		<div id="punnet"></div>
		<jdoc:include type="modules" name="arc_menu"  style="" headerLevel="3" />
		<div id="login_form">
			<ul class="login">
				<li>
					<?php if( $user->id == 0 ) : ?>
					<?php else: ?>
						<jdoc:include type="modules" name="login_top"  style="" headerLevel="3" />
					<?php endif; ?>
				</li>
			</ul> 
		</div>
	</header>

	<div id="system-message">
		<jdoc:include type="message" />
	</div>
	<div id="allcoms">
		<jdoc:include type="modules" name="context1" />
		<jdoc:include type="modules" name="context2" />
	</div>
	<div id="content">
		<jdoc:include type="component" />
		<!-- Login -->
		<?php if( $user->id == 0 ) : ?>
		<div id="login_wrap">
			<div id="login_main">
				<div class="loginContent">
					<jdoc:include type="modules" name="login_top" headerLevel="3" />
				</div>
			</div>
		</div> 
		<?php endif; ?><!-- /login -->
	</div><!-- / content -->

	<div id="footer">
		<div id="logo">
			<a href=""></a>
		</div>
	</div>
	
</div><!-- / wrapper -->
</body>
</html>