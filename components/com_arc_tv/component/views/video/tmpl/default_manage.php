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

// add javascript
JHTML::script( 'default_manage.js', $this->addPath, true );
echo JHTML::_( 'arc.hidden', 'ajax_spinner_path', JURI::base().'media/system/images/mootree_loader.gif', 'id="ajax_spinner_path"' );
echo JHTML::_( 'arc.hidden', 'save_url'    , ApotheosisLibAcl::getUserLinkAllowed('arc_tv_manage_save',    array('tv.videoId'=>$this->curVideo->getId()                                             )), 'id="save_url"'    );
echo JHTML::_( 'arc.hidden', 'submit_url'  , ApotheosisLibAcl::getUserLinkAllowed('arc_tv_manage_submit',  array('tv.videoId'=>$this->curVideo->getId()                                             )), 'id="submit_url"'  );
echo JHTML::_( 'arc.hidden', 'approve_url' , ApotheosisLibAcl::getUserLinkAllowed('arc_tv_manage_approve', array('tv.videoId'=>$this->curVideo->getId()                                             )), 'id="approve_url"' );
echo JHTML::_( 'arc.hidden', 'reject_url'  , ApotheosisLibAcl::getUserLinkAllowed('arc_tv_manage_reject',  array('tv.videoId'=>$this->curVideo->getId()                                             )), 'id="reject_url"'  );
echo JHTML::_( 'arc.hidden', 'status_url'  , ApotheosisLibAcl::getUserLinkAllowed('arc_tv_manage_status',  array('tv.videoId'=>$this->curVideo->getId(), 'js.idcheck.replace'=>'js.idcheck.replace' )), 'id="status_url"'  );
echo JHTML::_( 'arc.hidden', 'sidebar_url' , ApotheosisLibAcl::getUserLinkAllowed('arc_tv_sidebar_ajax',   array(                                        'js.idcheck.replace'=>'js.idcheck.replace' )), 'id="sidebar_url"' );
?>
<div id="manage_div">
	<form id="manage_video_form" method="post" action="<?php echo ApotheosisLibAcl::getUserLinkAllowed( 'arc_tv', array() ); ?>">
		
		<span class="section_title"><?php echo $this->manageDivTitle; ?></span>
		<?php
			if( $this->moderate && $this->submitted ) {
				echo $this->loadTemplate( 'manage_mod' );
			}
			else {
				echo $this->loadTemplate( 'manage_file' );
			}
		?>
		<div id="manage_accordion">
			<div class="manage_pane">
				<span class="manage_pane_clicker">Current Status...</span>
				<div class="manage_slider">
					<div id="manage_status_div">
						<?php echo $this->loadTemplate( 'manage_status' ); ?>
					</div>
				</div>
			</div>
			<div class="manage_pane">
				<span class="manage_pane_clicker">Media...</span>
				<div class="manage_slider">
					<p>This preview will be limited by the progress made by the encoding server. Please check back soon to see any progress.</p>
					<?php if( $this->curVideo->getId() < 0 ): ?>
					<p>You need to set a file to upload before a preview will display here.</p>
					<?php else: ?>
					<?php echo $this->loadTemplate( 'video_player' ); ?>
					<?php endif; ?>
				</div>
			</div>
			<div class="manage_pane">
				<span class="manage_pane_clicker">Title, Description and Tags...</span>
				<div class="manage_slider">
					<input type="hidden" name="manage_meta_pane" value="1" />
					<div class="manage_form_input_row">
						<div class="manage_form_label_div">Title:</div>
						<div class="manage_form_input_div"><input id="manage_title_input" name="manage_title_input" type="text" size="100" value="<?php echo $this->curVideo->getDatum( 'title' ); ?>" /></div>
					</div>
					<div class="manage_form_input_row">
						<div class="manage_form_label_div">Description:</div>
						<div class="manage_form_input_div"><input id="manage_desc_input" name="manage_desc_input" type="text" size="100" value="<?php echo $this->curVideo->getDatum( 'desc' ); ?>" /></div>
					</div>
					<div class="manage_form_input_row">
						<div class="manage_form_label_div">Tags:</div>
						<div class="manage_form_input_div"><?php echo JHTML::_( 'arc_tv.tags', 'manage_tags_input', array_keys($this->curVideo->getTags()), array(), '(^|\W)(\w+(\'s)?)(?=$|\W)' ); ?></div>
					</div>
				</div>
			</div>
			<div class="manage_pane">
				<span class="manage_pane_clicker">Credits...</span>
				<div class="manage_slider">
					<input type="hidden" name="manage_credits_pane" value="1" />
					<div class="manage_form_input_row">
						<div class="manage_form_label_div">Role:</div>
						<div class="manage_form_input_div"><?php echo JHTML::_( 'arc_tv.roles', 'manage_roles_roles_input', null, array(), '(^|\W)([\w\040]+(\'s)?)(?=$|\W)' ); ?></div>
					</div>
					<div class="manage_form_input_row">
						<div class="manage_form_label_div">Name:</div>
						<div class="manage_form_input_div"><?php echo JHTML::_( 'arc_people.people', 'manage_roles_people_input', '', 'teacher OR pupil', true, array('multiple'=>'multiple') ); ?></div>
					</div>
					<div class="manage_form_input_row" id="manage_form_add_role_botton_div">
						<div class="manage_form_label_div">&nbsp;</div>
						<div class="manage_form_input_div"><a id="add_roles_link" class="btn" href="#">Add Roles</a></div>
					</div>
					<div class="manage_form_input_row" id="manage_roles_table_div">
						<div class="manage_form_label_div">&nbsp;</div>
						<div class="manage_form_input_div">
							<?php echo $this->loadTemplate( 'manage_role_table' ); ?>
						</div>
					</div>
				</div>
			</div>
			<div class="manage_pane">
				<span class="manage_pane_clicker">Permissions...</span>
				<div class="manage_slider">
					<input type="hidden" name="manage_filters_pane" value="1" />
					<div class="manage_form_input_row">
						<div class="manage_form_label_div">Users:</div>
						<div class="manage_form_input_div">
							<?php
							JRequest::setVar( 'manage_filter_people_input', $this->filters['people'] );
							echo JHTML::_( 'arc_people.people', 'manage_filter_people_input', null, 'teacher OR pupil', true, array('multiple'=>'multiple') );
							?>
						</div>
					</div>
					<div class="manage_form_input_row">
						<div class="manage_form_label_div">Years:</div>
						<div class="manage_form_input_div">
							<?php
							JRequest::setVar( 'manage_filter_years_input', $this->filters['years'] );
							echo JHTML::_( 'arc.yearGroup', 'manage_filter_years_input', null, true );
							?>
						</div>
					</div>
					<div class="manage_form_input_row">
						<div class="manage_form_label_div">Classes:</div>
						<div class="manage_form_input_div">
							<?php
							JRequest::setVar( 'manage_filter_groups_input', serialize($this->filters['groups']) );
							echo JHTML::_( 'groups.grouptree', 'manage_filter_groups_input' );
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<input type="hidden" id="video_id" name="video_id" value="<?php echo $this->curVideo->getId(); ?>" />
		<input type="hidden" id="video_status" name="video_status" value="<?php echo $this->curVideo->getDatum( 'status' ); ?>" />
	</form>
</div>