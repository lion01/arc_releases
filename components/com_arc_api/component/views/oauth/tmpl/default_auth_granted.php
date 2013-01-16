<?php
/**
 * @package     Arc
 * @subpackage  API
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<h3>Authorisation Given</h3>
<p>You need to go to your application and enter the following authorisation verification code:</p>
<p><span style="margin: 1em 5em; font-weight: bold;"><?php echo $this->verifier; ?></span></p>
<h3>Why am I being asked to do this?</h3>
<p>Most applications specify a "callback url".
   This is a webpage that the data provider can call to notify the application that you have authorised it.
   The application you have just authorised has not specified a callback url so you need to let it know what's happened by entering the code above.</p>
