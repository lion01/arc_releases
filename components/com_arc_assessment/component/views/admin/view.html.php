<?php
/**
 * @package     Arc
 * @subpackage  Assessment
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.view');

/**
 * Assessments View Admin
 */
class AssessmentsViewAdmin extends JView
{
	/**
	 * Prepares and displays assessment edit form
	 */
	function edit()
	{
		$this->assProps = $this->get( 'AssDetails' );
		$this->assAccess = $this->get( 'AssAccess' );
		$this->assGroups = $this->get( 'AssGroupIds' );
		$this->defaultBoundaries = $this->get( 'DefaultBoundaries' );
		$this->markstyleInfo = $this->get( 'MarkstyleInfo' );
		$this->markstyleInfo[''] = array( 'label'=>'-- As Marked --', 'type'=>'text' );
		parent::display();
	}
	
	function selectAssessmentFile()
	{
		$this->assProps = $this->get( 'AssDetails' );
		ob_start();
		?>
Please select a file to upload
<form enctype="multipart/form-data" action="<?php echo ApotheosisLib::getActionLink( null, array('assessment.assessments'=>$this->assProps['assessment']['id']) ); ?>" name="arc_search" id="arc_search" method="post" >
<input type="hidden" name="MAX_FILE_SIZE" value="2000000" />
<input type="file" name="filename" />
<br /><br />
<input type="submit" name="task" value="Upload Assessment" />
</form>
		<?php
		echo ob_get_clean();
	}
	
	function selectAspectFile()
	{
		$this->assProps = $this->get( 'AssDetails' );
		ob_start();
		?>
Please select a file to upload
<form enctype="multipart/form-data" action="<?php echo ApotheosisLib::getActionLink( null, array('assessment.assessments'=>$this->assProps['assessment']['id']) ); ?>" name="arc_search" id="arc_search" method="post" >
<input type="hidden" name="MAX_FILE_SIZE" value="2000000" />
<input type="file" name="filename" />
<br /><br />
<input type="submit" name="task" value="Upload Aspects" />
</form>
		<?php
		echo ob_get_clean();
	}

}
?>