<form method="post">
<!-- first row (channel details) inputs -->
<fieldset>

<fieldset>
<?php if( ApotheosisLibAcl::checkDependancy( 'message.channels', $this->channel->getId(), false, ApotheosisLib::getActionIdByName('apoth_msg_channel_restricted') ) ) :?>
	<input type="submit" class="btn" name="task" value="Update">
	<input type="submit" class="btn" name="task" value="Save As New">
	<input name="make_new" class="btn" id="make_new" type="button" value="Create new" />
	<input name="task" class="btn" id="task" type="submit" value="Delete" />
<?php else : ?>
	<input type="submit" class="btn" name="task" value="Save As New">
	<input name="make_new" class="btn" id="make_new" type="button" value="Create new" />
<?php endif; ?>
</fieldset>

</fieldset>


<fieldset>

<fieldset>
	<input name="id" type="hidden" value="<?php echo $this->channel->getId(); ?>" />
	<input name="name" value="<?php echo $this->channel->getName(); ?>" />
	<input name="description" value="<?php echo $this->channel->getDescription(); ?>" />
	<label for="exclusive">Exclusive?</label> <input type="checkbox" id="exclusive" name="exclusive"<?php if( $this->channel->getExclusive() ) { echo ' checked="checked"'; } ?>>
	<?php echo JHTML::_( 'arc_message.privacyLevels', 'privacy', $this->channel->getPrivacy() ); ?>
	<?php echo JHTML::_( 'arc_message.folders', 'default_folder', $this->channel->getFolder() ); ?>
</fieldset>


</fieldset>

<!-- second row (rule definition) inputs -->
<fieldset>
<fieldset class="col">
<label for="isFirst">Is First</label>
(<label for="isFirst_neg">negate</label> <input type="checkbox" id="isFirst_neg" name="isFirst_neg"<?php if( JRequest::getVar('isFirst_neg') ) { echo ' checked="checked"'; } ?>>)
<br />
<?php echo JHTML::_( 'arc_message.isfirst',  'isFirst' ); ?>
</fieldset>

<fieldset class="col">
<label for="methods">Method</label>
(<label for="methods_neg">negate</label> <input type="checkbox" id="methods_neg" name="methods_neg"<?php if( JRequest::getVar('methods_neg') ) { echo ' checked="checked"'; } ?>>)
<br />
<?php echo JHTML::_( 'arc_message.methods',  'methods',   null, true ); ?>
</fieldset>

<fieldset class="col">
<label for="times">Times</label>
(<label for="times_neg">negate</label> <input type="checkbox" id="times_neg" name="times_neg"<?php if( JRequest::getVar('times_neg') ) { echo ' checked="checked"'; } ?>>)
<br />
<?php echo JHTML::_( 'arc_message.times',    'times',     null, true ); ?>
</fieldset>

<fieldset class="col">
<label for="colors">Colors</label>
(<label for="colors_neg">negate</label> <input type="checkbox" id="colors_neg" name="colors_neg"<?php if( JRequest::getVar('colors_neg') ) { echo ' checked="checked"'; } ?>>)
<br />
<?php echo JHTML::_( 'arc_behaviour.colors', 'colors',    null, true ); ?>
</fieldset>

<fieldset class="col">
<label for="actions">Actions</label>
(<label for="actions_neg">negate</label> <input type="checkbox" id="actions_neg" name="actions_neg"<?php if( JRequest::getVar('actions_neg') ) { echo ' checked="checked"'; } ?>>)
<br />
<?php echo JHTML::_( 'arc_behaviour.actions', 'actions',  null, true ); ?>
</fieldset>

<fieldset class="col">
<label for="tutorGroup">Student Tutor</label>
(<label for="tutorGroup_neg">negate</label> <input type="checkbox" id="tutorGroup_neg" name="tutorGroup_neg"<?php if( JRequest::getVar('tutorGroup_neg') ) { echo ' checked="checked"'; } ?>>)
<br />
<label for="tutorGroup_var">Is user's</label> <input type="checkbox" id="tutorGroup_var" name="tutorGroup_var"<?php if( JRequest::getVar('tutorGroup_var') ) { echo ' checked="checked"'; } ?>>
<br />
<?php echo JHTML::_( 'arc_timetable.tutorgroups', 'tutorGroup', null, true ); ?>
</fieldset>

<fieldset class="col">
<label for="yearGroup">Student Year</label>
(<label for="yearGroup_neg">negate</label> <input type="checkbox" id="yearGroup_neg" name="yearGroup_neg"<?php if( JRequest::getVar('yearGroup_neg') ) { echo ' checked="checked"'; } ?>>)
<br />
<label for="yearGroup_var">Is user's</label> <input type="checkbox" id="yearGroup_var" name="yearGroup_var"<?php if( JRequest::getVar('yearGroup_var') ) { echo ' checked="checked"'; } ?>>
<br />
<?php echo JHTML::_( 'arc.yearGroup',        'yearGroup', null, true ); ?>
</fieldset>

<fieldset class="col">
<label for="group">Group</label>
(<label for="group_neg">negate</label> <input type="checkbox" id="group_neg" name="group_neg"<?php if( JRequest::getVar('group_neg') ) { echo ' checked="checked"'; } ?>>)
<br />
<label for="group_var">Is user-taught</label> <input type="checkbox" id="group_var" name="group_var"<?php if( JRequest::getVar('group_var') ) { echo ' checked="checked"'; } ?>>
<br />
<?php /** The following uses the 'apoth_att' action as we want users' group list to be based on the classes they teach */ ?>
<?php echo JHTML::_( 'groups.grouptree',     'group', ApotheosisLib::getActionIdByName( 'apoth_att' ), true ); ?>
</fieldset>

<fieldset class="col">
<label for="student">Student</label>
(<label for="student_neg">negate</label> <input type="checkbox" id="student_neg" name="student_neg"<?php if( JRequest::getVar('student_neg') ) { echo ' checked="checked"'; } ?>>)
<br />
<label for="student_var_t">Is user-taught</label>     <input type="checkbox" id="student_var_t" name="student_var_t"<?php if( JRequest::getVar('student_var_t') ) { echo ' checked="checked"'; } ?>><br />
<label for="student_var_u">Is user</label>            <input type="checkbox" id="student_var_u" name="student_var_u"<?php if( JRequest::getVar('student_var_u') ) { echo ' checked="checked"'; } ?>><br />
<label for="student_var_r">Is user's relation</label> <input type="checkbox" id="student_var_r" name="student_var_r"<?php if( JRequest::getVar('student_var_r') ) { echo ' checked="checked"'; } ?>><br />

<div class="combo_container">
<?php echo JHTML::_( 'arc_people.people',    'student', null, 'pupil', true, array('multiple'=>'multiple') ); ?>
</div>
</fieldset>
</fieldset>

<!-- third row inputs -->
<fieldset>
</fieldset>


</form>
