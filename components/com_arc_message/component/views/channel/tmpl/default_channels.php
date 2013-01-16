<fieldset class="col">
<label>Subscribed</label><br />
<?php echo JHTML::_( 'select.genericlist', $this->mySubs, 'subscribed', 'multiple="multiple"', 'id', 'name', JRequest::getVar( 'subscribed' ) ); ?>
<input type="hidden" id="derived" name="derived" value="<?php echo $this->derived; ?>" />
</fieldset>

<fieldset class="col">
<br />
<input type="button" class="btn" id="addSub" value="&lt;&lt;" /><br />
<input type="button" class="btn" id="delSub" value="&gt;&gt;" />
</fieldset>

<fieldset class="col">
<label>Global channels</label><br />
<?php echo JHTML::_( 'select.genericlist', $this->global, 'global', 'multiple="multiple"', 'id', 'name', JRequest::getVar( 'global' ) ); ?>
</fieldset>

<fieldset class="col">
<label>Public channels</label><br />
<?php echo JHTML::_( 'select.genericlist', $this->public, 'public', 'multiple="multiple"', 'id', 'name', JRequest::getVar( 'public' ) ); ?>
</fieldset>

<fieldset class="col">
<label>Private channels</label><br />
<?php echo JHTML::_( 'select.genericlist', $this->private, 'private', 'multiple="multiple"', 'id', 'name', JRequest::getVar( 'private' ) ); ?>
</fieldset>