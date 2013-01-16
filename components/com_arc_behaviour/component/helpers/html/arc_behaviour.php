<?php
/**
 * @package     Arc
 * @subpackage  Behaviour
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Utility class for generating arc behaviour specific HTML entities
 *
 * @static
 * @package    Arc
 * @subpackage Behaviour
 * @since      1.5
 */
class JHTMLArc_Behaviour
{
	/**
	 * Generate HTML to display a link to the "new message" interstitial
	 *
	 * @param array $data  Any data which the caller can provide for us to use on the form
	 * @return string  The HTML to display the required input
	 */
	function addIncident( $data, $text = '<b>&nbsp;!&nbsp;</b>' )
	{
		$data['new'] = true;
		$l = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_msg_hub_inter', array('message.scopes'=>'form', 'message.forms'=>'behaviour.start.edit', 'message.data'=>urlencode(json_encode($data))) );
		if( $l ) {
			$r = '<a href="'.$l.'" class="modal" rel="{handler: \'iframe\', size: {x: 500, y: 400}}">'.$text.'</a>';
		}
		else {
			$r = '';
		}
		return $r;
	}
	
	/**
	 * Shows a list of all the incident types
	 * 
	 * @param string $name  The name to use for the input
	 * @param mixed $default  The default value to have selected on form display / reset
	 * @param boolean $multiple  Allow multiple selections?
	 * @return string  The HTML to display the required input
	 */
	function typeList( $name, $default = null, $multiple = false )
	{
		$default  = ( is_null($default)  ? ''   : $default );
		$oldVal   = JRequest::getVar($name, $default);
		$options[0] = new stdClass();
		$options[0]->id = '';
		$options[0]->label = '';
		
		$fInc = ApothFactory::_( 'behaviour.IncidentType' );
		$r = $fInc->getInstances( array( 'deleted'=>false ), false, true ); 
		$r = $fInc->getStructure( array( 'deleted'=>false ) );
		
		foreach( $r as $info ) {
			$tmp = $fInc->getInstance( $info['id'] );
			$opt = new stdClass();
			$opt->id = $info['id'];
			$opt->label = str_repeat('- ', $info['level']).$tmp->getLabel();
			$options[] = $opt;
		}
		
		$attribs = ($multiple ? 'multiple="multiple" class="multi_medium"' : '');
		$name = ($multiple ? $name.'[]' : $name);
		$retVal = JHTML::_('select.genericList', $options, $name, $attribs , 'id', 'label', $oldVal);
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class="search_default"' );
		return $retVal;
	}
	
	/**
	 * Generate HTML to display a set of inputs to allow selection of an incident type
	 * Includes color, incident, "other" text area
	 * Sets up js to dynamically update these inputs appropriately
	 * 
	 * @param string $name  Input name for incident type
	 * @param string $name2  Input name for incident
	 * @param string $name3  Input name for incident text (where appropriate)
	 * @param boolean $active  Should the resultant inputs be enhanced with js?
	 * @param boolean $typeEnabled  Is the incident type allowed to change?
	 * @param string $oldVal  Existing value to use for incident type
	 * @param string $oldVal2  Existing value to use for incident
	 * @param string $oldVal3  Existing value to use for incident text (where appropriate
	 */
	function type( $name, $name2, $name3, $active, $typeEnabled, $oldVal = null, $oldVal2 = null, $oldVal3 = null )
	{
		// get values to carry through
		$id2 = str_replace( array('[', ']'), '', $name2 );
		$oldVal = (int)JRequest::getVar($name, $oldVal);
		$oldVal2 = JRequest::getVar($name2, $oldVal2);
		// set up output strings
		$main = '';
		$secondary = '';
		$other = '';
		
		// discover all the incident types and set up lists of any / all children we may need
		$fInc = ApothFactory::_( 'behaviour.IncidentType' );
		
		$parents = $fInc->getInstances( array('root'=>true) );
		$blank = new stdClass();
		$blank->id = 0;
		$blank->label = '';
		$children = array();
		$list = array();
		$list[0] = $blank;
		foreach( $parents as $id ) {
			$tmp = $fInc->getInstance($id);
			$list[$id] = new stdClass();
			$list[$id]->id = $tmp->getId();
			$list[$id]->label = $tmp->getLabel();
			
			// if the input bexng made is to be "active", all the children must be listed in separate lists
			// otherwise only the children of the current incident type are needed
			if( ( $active && $typeEnabled ) || $id == $oldVal ) {
				$children[$id] = $fInc->getInstances( array('parent'=>$id) );
			}
		}
		
		// Set up the html to either show or allow selection of the incident type
		if( $typeEnabled ) {
			$main = JHTML::_('select.genericList', $list, $name, '', 'id', 'label', $oldVal);
		}
		else {
			$main = $list[$oldVal]->label.'<input type="hidden" name="'.$name.'" value="'.$oldVal.'" />';
		}
		
		// Set up the html to either show or allow selection of the incident
		// This my create several lists to allow an "active" input to pick and choose
		foreach( $children as $parent=>$c ) {
			$list = array();
			$list[0] = $blank;
			foreach( $c as $id ) {
				$tmp = $fInc->getInstance($id);
				$list[$id] = new stdClass();
				$list[$id]->id = $tmp->getId();
				$list[$id]->label = $tmp->getLabel();
				
				if( $tmp->getHasText() ) {
					$texty[] = $id;
				}
			}
			$inputId = 'list_'.$parent.'_'.$id2;
			$inputName = ( $parent == $oldVal ? '' : 'list_'.$parent.'_' ).$name2;
			$secondaryIds[] = $inputId;
			
			$secondary .= '<select name="'.$inputName.'" id="'.$inputId.'" '.($parent == $oldVal ? '' : 'style="display:none" ').'>'
				.JHTML::_( 'select.Options', $list, 'id', 'label', $oldVal2, false )
				.'</select>';
		}
		
		// If any of the incidents may have extra text set up some html to allow entry of it
		if( !empty($texty) ) {
			$other = '<div id="text_for_'.$name3.'">please specify: <input type="text" name="'.$name3.'" value="'.$oldVal3.'"/></div>';
		}
		
		// Set up javascript for active inputs and add to page
		if( $active ) {
			ob_start();
			JHTML::_('behavior.mootools');
			if( !empty($texty) ) {
				// Add js to enable text input box on selection of texty incident
				?>
				textyChange = function() {
					var textyIncs = [<?php echo implode( ', ', $texty ); ?>];
					var textyIncsDiv = $('<?php echo 'text_for_'.$name3; ?>');
					if( textyIncs.contains( this.value.toInt() ) ) {
						textyIncsDiv.setStyle( 'display', 'block' );
					}
					else {
						textyIncsDiv.setStyle( 'display', 'none' );
					}
				}
						
				window.addEvent( 'domready', function() {
					var textyIncs = [<?php echo implode( ', ', $texty ); ?>];
					var textyIncsDiv = $('<?php echo 'text_for_'.$name3; ?>');
					
					<?php foreach( $secondaryIds as $id ) :?>
					$('<?php echo $id; ?>').addEvent( 'change', textyChange );
					<?php endforeach; ?>
					if( $('<?php echo 'list_'.$oldVal.'_'.$id2; ?>') != null ) {
						$('<?php echo 'list_'.$oldVal.'_'.$id2; ?>').fireEvent( 'change' );
					}
					else {
						textyIncsDiv.setStyle( 'display', 'none' );
					}
				});
				<?php
			}
			if( $typeEnabled ) {
				// Add js to change incident list when incident type changes
				?>
				window.addEvent( 'domready', function() {
					var oldVal = <?php echo $oldVal; ?>;
					var msg_old = $('<?php echo 'list_'.$oldVal.'_'.$id2; ?>');
					if( msg_old != null ) {
						msg_old_orig_name = '<?php echo 'list_'.$oldVal.'_'?>'+msg_old.name;
					}
					else {
						msg_old_orig_name = null;
					}
					$('<?php echo $name; ?>').addEvent( 'change', function( a ) {
						var msg_new = $('list_'+this.value+'_<?php echo $id2; ?>'); 
						var msg_old = $('list_'+oldVal+'_<?php echo $id2; ?>');
						
						if( msg_old != null ) {
							msg_old.name = msg_old_orig_name;
							msg_old.style.display = "none";
						}
						if( msg_new != null ) {
							msg_old_orig_name = msg_new.name;
							msg_new.name = msg_new.name.replace( /^list_[^_]+_/, '' );
							msg_new.style.display = "block";
							msg_new.fireEvent( 'change' );
						}
						oldVal = this.value;
					});
				});
				<?php
			}
			$script = ob_get_clean();
			$doc = &JFactory::getDocument();
			$doc->addScriptDeclaration( $script );
		}
		
		return $main."\n<br />".$secondary."\n<br />".$other;
	}
	
	function action( $name, $name2, $incType, $oldVal = null, $oldVal2 = null )
	{
		static $count = 0;
		$unique = 'action_'.$count++;
		$other = '';
		
		if( is_null($oldVal) ) {
			$oldVal  = JRequest::getVar($name, '');
		}
		
		$fAct = ApothFactory::_( 'behaviour.Action' );
		
		$actions = $fAct->getInstances( array('incident'=>$incType) );
		
		$hasNum = false;
		$blank = new stdClass();
		$blank->id = 0;
		$blank->label = '';
		$list = array();
		$list[0] = $blank;
		foreach( $actions as $a ) {
			$tmp = $fAct->getInstance($a);
			$list[$a] = new stdClass();
			$list[$a]->id = $tmp->getId();
			$list[$a]->label = $tmp->getLabel();
			if( (stristr($tmp->getScore(), 'n') !== false) && !$hasNum) {
				$hasNum = true;
				// inject "_number" into the input name, inside the first square brackets if any
				$nameNum = preg_replace( '~(.*\\[)?([^\\]]*)(\\])?~', '$1$2_number$3', $name, 1 ); 
				$oldValNum = JRequest::getVar( $nameNum, 0 );
			}
			
			if( $tmp->getHasText() ) {
				$texty[] = $a;
			}
		}
		
		if( !empty($texty) ) {
			// generate the script for dynamic behaviour
			ob_start();
			?>
			<script>
			window.addEvent( 'arcload', function() {
				var textyActs = [<?php echo implode( ', ', $texty ); ?>];
				var textyActsSel = $('<?php echo $unique; ?>').getElements('select');
				var textyActsDiv = $('<?php echo $unique; ?>').getElement('div.text_for_action');
				textyActsDiv.setStyle( 'display', 'none' );
				
				textyActsSel.addEvent( 'change', function() {
					if( textyActs.contains( this.value.toInt() ) ) {
						textyActsDiv.setStyle( 'display', 'block' );
					}
					else {
						textyActsDiv.setStyle( 'display', 'none' );
					}
				});
			});
			</script>
			<?php
			$script = ob_get_clean();
			
			// now generate the script that can go in the document head if this isn't being loaded dynamically (eg via AJAX)
			ob_start();
			?>
			window.addEvent( 'domready', function() {
				window.fireEvent( 'arcload' );
				window.removeEvents( 'arcload' );
			});
			<?php
			$scriptHead = ob_get_clean();
			$doc = &JFactory::getDocument();
			$doc->addScriptDeclaration( $scriptHead );
			$other = '<div class="text_for_action"><h4>Action Details (optional)</h4>'
				."\n".'<textarea name="'.$name2.'" placeholder="Detail things">'.$oldVal2.'</textarea>';
		}
		
		$num = ($hasNum ? '<input class="rnd" type="text" name="'.$nameNum.'" value="'.$oldValNum.'" style="width: 2em">&nbsp;*&nbsp;' : '');
		$main = JHTML::_('select.genericList', $list, $name, '', 'id', 'label', $oldVal);
		return $script.'<div id="'.$unique.'">'.$num.$main."\n<br />".$other.'</div>';
	}
	
	
	// ###  Search form inputs  ###
	
	/**
	 * The different ways we can group results into series
	 * 
	 */
	
	
	/**
	 * Shows a list of all the actions
	 * 
	 * @param string $name  The name to use for the input
	 * @param mixed $default  The default value to have selected on form display / reset
	 * @param boolean $multiple  Allow multiple selections?
	 * @return string  The HTML to display the required input
	 */
	function actions( $name, $default = null, $multiple = false )
	{
		$default  = ( is_null($default)  ? ''   : $default );
		$oldVal   = JRequest::getVar($name, $default);
		$options[0] = new stdClass();
		$options[0]->id = '';
		$options[0]->label = '';
		
		$fAct = ApothFactory::_( 'behaviour.Action' );
		
		$actions = $fAct->getInstances( array() );
		foreach( $actions as $aId ) {
			$action = $fAct->getInstance( $aId );
			$tmp = new stdClass();
			$tmp->id = $action->getId();
			$tmp->label = $action->getLabel();
			$options[] = $tmp;
		}
		
		$attribs = ($multiple ? 'multiple="multiple" class="multi_medium"' : '');
		$name = ($multiple ? $name.'[]' : $name);
		$retVal = JHTML::_('select.genericList', $options, $name, $attribs , 'id', 'label', $oldVal);
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class="search_default"' );
		return $retVal;
	}
	
	/**
	 * Generate HTML to display a series type select box with the given name
	 *
	 * @param string $name  The name to use for the input
	 * @param mixed $default  The default value to have selected on form display / reset
	 * @param boolean $multiple  Allow multiple selections?
	 * @return string  The HTML to display the required input
	 */
	function seriesTypes($name, $default = null, $multiple = false)
	{
		$default = ( is_null($default) ? 'person_id' : $default );
		$oldVal = JRequest::getVar($name, $default);
		$options[0] = new stdClass();
		$options[0]->id = 'person_id';
		$options[0]->label = 'Student';
		$options[1] = new stdClass();
		$options[1]->id = 'groups';
		$options[1]->label = 'Class';
		$options[2] = new stdClass();
		$options[2]->id = 'tutor';
		$options[2]->label = 'Tutor';
		$options[3] = new stdClass();
		$options[3]->id = 'author';
		$options[3]->label = 'Author';
		
		$attribs = ($multiple ? 'multiple="multiple" class="multi_medium"' : '');
		$name = ($multiple ? $name.'[]' : $name);
		$retVal = JHTML::_('select.genericList', $options, $name, $attribs , 'id', 'label', $oldVal);
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class="search_default"' );
		return $retVal;
	}
	
	function limits( $name, $default = null, $default2 = null )
	{
		$name2 = $name.'_val';
		$default  = ( is_null($default)  ? ''   : $default );
		$default2 = ( is_null($default2) ? '10' : $default2 );
		$oldVal   = JRequest::getVar($name, $default);
		$oldVal2  = JRequest::getVar($name2, $default2);
		$options[0] = new stdClass();
		$options[0]->id = '';
		$options[0]->label = '';
		$options[1] = new stdClass();
		$options[1]->id = 'top';
		$options[1]->label = 'Top';
		$options[2] = new stdClass();
		$options[2]->id = 'climb';
		$options[2]->label = 'Most improved';
		$options[3] = new stdClass();
		$options[3]->id = 'steady';
		$options[3]->label = 'Most stable';
		$options[4] = new stdClass();
		$options[4]->id = 'decline';
		$options[4]->label = 'Most deteriorated';
		$options[5] = new stdClass();
		$options[5]->id = 'bottom';
		$options[5]->label = 'Bottom';
		
		$attribs = '';
		$retVal = JHTML::_('select.genericList', $options, $name, $attribs, 'id', 'label', $oldVal)
			.'<input type="text" name="'.$name2.'" value="'.$oldVal2.'" size="3" />';
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class="search_default"' );
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name2, $default2, 'class="search_default"' );
		return $retVal;
	}
	
	function colors( $name, $default = null, $multiple = false )
	{
		$default = ( is_null($default) ? '' : $default );
		$oldVal = JRequest::getVar($name, $default);
		$options = array();
		$options[0] = new stdClass();
		$options[0]->id = '';
		$options[0]->label = '';
		
		// discover all the incident types and set up lists of any / all children we may need
		$fInc = ApothFactory::_( 'behaviour.IncidentType' );
		$parents = $fInc->getInstances( array('root'=>true) );
		if( !empty($parents) ) {
			foreach( $parents as $id ) {
				$tmp = $fInc->getInstance( $id );
				$o = new stdClass();
				$o->id = $tmp->getId();
				$o->label = $tmp->getLabel();
				$options[] = $o;
			}
		}
		
		$attribs = ($multiple ? 'multiple="multiple" class="multi_medium"' : '');
		$name = ($multiple ? $name.'[]' : $name);
		$retVal = JHTML::_('select.genericList', $options, $name, $attribs , 'id', 'label', $oldVal);
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class="search_default"' );
		return $retVal;
	}
	
	/**
	 * Generate HTML to display an admin page profile template category management section
	 * *** placeholder for now
	 * 
	 * @param array $catInfo  Info about the category
	 * @param array $catData  The data for display
	 * @return string $html  The HTML
	 */
	function profilePanel( $catInfo, $catData )
	{
		return '';
	}
}
?>