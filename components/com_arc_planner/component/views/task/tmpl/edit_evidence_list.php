<?php
/**
 * @package     Arc
 * @subpackage  Planner
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<div class="nolabel"><?php echo $this->labels['update_evidence_intro']; ?></div>
<?php
		$numEvidence = $this->task->getEvidenceNum();
		if( is_null($numEvidence) ) {
			$numEvidence = 1;
		}
			// display the required number of evidence URL fields
			$firstPass = true;
			for( $i = 1; $i <= $numEvidence; $i++) {
				echo '
					<div>
						<label for="evidence_url_'.$i.'">'.( $firstPass ? $this->labels['update_evidence_url'] : '&nbsp;' ).'</label>
						<input id="evidence_url_'.$i.'" class="evidence" type="text" name="updates['.$this->taskId.']['.$this->groupId.']['.$this->updateId.'][evidence_url][]" /><br />
					</div>
					';
				$firstPass = false;
			}
			
			// display the required number of evidence file fields
			$firstPass = true;
			for( $i = 1; $i <= $numEvidence; $i++) {
				echo '
					<div>
						<label for="evidence_file_'.$i.'">'.( $firstPass ? $this->labels['update_evidence_file'] : '&nbsp;' ).'</label>
						<input id="evidence_file_'.$i.'" class="evidence" type="file" name="updates['.$this->taskId.']['.$this->groupId.']['.$this->updateId.'][evidence_file][]" /><br />
					</div>
					';
				$firstPass = false;
			}
?>