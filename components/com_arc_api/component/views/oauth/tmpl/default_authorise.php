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
<h3>Grant access?</h3>
<p>An application is asking for your permission to access your data which is held in Arc.
   Subject to your user permissions, the application <em>will</em> be allowed to:
   <ul>
   	<li>Access your personal information ( eg name, address, date of birth )</li>
   	<li>Access your academic and performance-related data</li>
   	<li>Make changes to all the above, eg
   	<ul>
   		<li>record behaviour incidents in your name</li>
   		<li>change your address</li>
   	</ul>
   </ul>
   The Application <em>will not</em> be allowed to:
   <ul>
   	<li>Access your password</li>
   	<li>Grant access to other applications</li>
   </ul>
   </p>

<form method="post">
<input type="hidden" id="oauth_token" name="oauth_token" value="<?php echo $this->token; ?>" />
<input type="hidden" name="decided" value="1" />
<input type="image" id="grant" name="grant" src="images/apply_f2.png"  />&nbsp;<label for="grant">Grant</label><br />
<input type="image" id="deny"  name="deny"  src="images/cancel_f2.png" />&nbsp;<label for="deny">Deny</label><br />
</form>