<?php
/**
 * @package     Arc
 * @subpackage  Message
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

// add default css
JHTML::stylesheet( 'default.css', $this->addPath );
?>
<form action="index.php" method="post" name="adminForm">
<table>
	<tr style="vertical-align: top;">
		<td>
			<fieldset class="adminform_thinfieldset">
				<legend><?php echo JText::_( 'Authorisation' ); ?></legend>
				<?php echo $this->comParams->render( 'params', 'twitter_auth' ); ?>
				<br />
				Once these options are set you will need to Authorise the app with Twitter.<br />
				This will take you to Twitter's site after which you will be returned here.
			</fieldset>
			<input type="hidden" name="component" value="Attendance Manager" />
			<input type="hidden" name="option" value="com_arc_message" />
			<input type="hidden" name="view" value="twitter" />
			<input type="hidden" name="task" value="save" />
			<div class="clr"></div>
			<fieldset class="adminform_thinfieldset">
				<legend><?php echo JText::_( 'API URLs' ); ?></legend>
				<?php echo $this->comParams->render('params', 'twitter_api'); ?>
			</fieldset>
		</td>
		<td>
			<fieldset class="adminform_thinfieldset">
				<legend><?php echo JText::_( '20 Most Recent Tweets' ); ?></legend>
				<?php if( $this->comParams->get( 'token' ) == '' ) : ?>
					<?php echo JText::_( 'Not authorised!' ); ?><br />
					<?php echo JText::_( 'You need to authorise Arc to access your Twitter account using the options on the left before tweets can be shown or sent.' ); ?>
				<?php else : ?>
					<?php try { ?>
						<?php
						$this->twit = $this->get( 'TwitterAccount' );
						$this->twit = $this->twit->screen_name;
						$this->tweets = $this->get( 'Tweets' );
						foreach( $this->tweets as $this->tweet ) {
							echo $this->loadTemplate( 'tweet' );
						}
						?>
						<?php } catch( OAuthException $E ) { ?>
						<p>There was a problem retrieving the tweets.
						   Perhaps the access token needs to be re-created</p>
						<p><?php echo $E->getMessage(); ?></p>
					<?php } ?>
				<?php endif; ?>
			</fieldset>
		</td>
	</tr>
</table>
</form>