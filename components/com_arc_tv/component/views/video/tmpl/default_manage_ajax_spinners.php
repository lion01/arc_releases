<?php
/**
 * @package     Arc
 * @subpackage  TV
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<div id="ajax_save_div"><?php echo JHTML::_( 'arc.loading', 'Saving...' ); ?></div>
<div id="ajax_submit_div"><?php echo JHTML::_( 'arc.loading', 'Submitting for moderation...' ); ?></div>
<div id="ajax_approve_div"><?php echo JHTML::_( 'arc.loading', 'Approving...' ); ?></div>
<div id="ajax_reject_div"><?php echo JHTML::_( 'arc.loading', 'Rejecting...' ); ?></div>
<div id="ajax_delete_div"><?php echo JHTML::_( 'arc.loading', 'Deleting video files...' ); ?></div>
<div id="ajax_noswoosh_file_div">Click either the Save or Submit button to save any new data...</div>
<div id="ajax_noswoosh_mod_div">Click either the Approve or Reject button to moderate this video...</div>
<div id="ajax_message_div"></div>