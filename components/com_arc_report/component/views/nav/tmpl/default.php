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

$this->view = 'writecheck';
?>

<style>
#arc_nav {
	padding-right: 5px;
	text-align: center;
}

#arc_nav ul li {
	display: block;
	height: 95px;
	width: 100%;
	border: 1px solid grey;
}

#arc_main_narrow {
	margin-left: 130px;
}

#breadcrumbs_wrapper {
	overflow: auto;
	padding: 0px 0px 10px 10px;
}

#breadcrumbs {
	float: left;
	-moz-box-shadow:inset 0px 1px 0px 0px #ffffff;
	-webkit-box-shadow:inset 0px 1px 0px 0px #ffffff;
	box-shadow:inset 0px 1px 0px 0px #ffffff;
	background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #ededed), color-stop(1, #dfdfdf) );
	background:-moz-linear-gradient( center top, #ededed 5%, #dfdfdf 100% );
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ededed', endColorstr='#dfdfdf');
	background-color:#ededed;
	-moz-border-radius:5px;
	-webkit-border-radius:5px;
	border-radius:5px;
	border:1px solid #bbb;
	display:inline-block;
	font-family:arial;
	font-size:13px;
	padding:1px 10px;
	text-decoration:none;
	text-shadow:1px 1px 0px rgba(255, 255, 255, 0.67);
}

div.breadcrumb,
div.breadcrumb_divider {
	float: left;
	height: 1.3em;
	margin: 5px 0px;
	
}

div.breadcrumb.first {
	margin-left: 2px;
}

div.breadcrumb.last {
	margin-right: 2px;
}

div.breadcrumb {
}

div.breadcrumb_divider {
	width: 18px;
}

#arc_nav li {
	overflow: hidden;
}
#arc_nav a {
	color: #4b4b4b;
}
#arc_nav .current a {
	color: #00b81c;
}
#arc_nav a:hover {
	text-decoration: none;
}
.inline_icon {
	line-height: 20px;
}
.inline_icon:before {
	font-size: 50px;
	line-height: 15px;
}
.menu_icon {
	line-height: 80px;
}
.menu_icon:before {
	font-size: 150px;
	line-height: 45px;
}
.menu_icon:before,
.inline_icon:before {
	font-family: "EntypoRegular";
}
#nav_wc:before    { content: "& "; }
#nav_over:before  { content: ", "; }
#nav_print:before { content: "< "; }
#nav_admin:before { content: "@ "; }
</style>

<div id="arc_nav">
	<ul>
<?php if( $link = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_writecheck' ) ) : ?>
		<li <?php if( $this->view == 'writecheck' ) { echo 'class="current"'; } ?>><a href="<?php echo $link; ?>"><span id="nav_wc"    class="menu_icon">Write / Check</span></a></li>
<?php endif; ?>
<?php if( $link = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_overview' ) ) : ?>
		<li <?php if( $this->view == 'overview'   ) { echo 'class="current"'; } ?>><a href="<?php echo $link; ?>"><span id="nav_over"  class="menu_icon">Overview</span></a></li>
<?php endif; ?>
<?php if( $link = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_printshare' ) ) : ?>
		<li <?php if( $this->view == 'printshare' ) { echo 'class="current"'; } ?>><a href="<?php echo $link; ?>"><span id="nav_print" class="menu_icon">Print / Share</span></a></li>
<?php endif; ?>
<?php if( $link = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_admin' ) ) : ?>
		<li <?php if( $this->view == 'admin'      ) { echo 'class="current"'; } ?>><a href="<?php echo $link; ?>"><span id="nav_admin" class="menu_icon">Admin</span></a></li>
<?php endif; ?>
	</ul>
</div>
