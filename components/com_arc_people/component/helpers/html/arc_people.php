<?php
/**
 * @package     Arc
 * @subpackage  People
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Utility class for creating different select lists
 *
 * @static
 * @package 	Arc
 * @subpackage	People
 * @since		1.0
 */
class JHTMLArc_People
{
	function plannerPeople( $name, $type, $pid = null, $default = null, $multiple = false )
	{
		$u = ApotheosisLib::getUser();
		$default = ( !is_null($default) ? $default : $u->person_id );
		$oldVal = JRequest::getVar( $name, $default );
		
		$person = new stdClass();
		$person->id = $person->title = $person->firstname = $person->middlenames = $person->surname = '';
		$people[''] = $person;
		$peopleDiv['divider'] = $person;
		
		if( is_null($pid) ) {
			$pid = $u->person_id;
		}
		
		$db = &JFactory::getDBO();
		
		$pid = $db->Quote( $pid );
		
		switch( $type ) {
		case( 'teachingStaff' ) :
			$query = 'SELECT DISTINCT p.id, p.title, COALESCE( p.preferred_firstname, p.firstname ) AS firstname, p.middlenames, COALESCE( p.preferred_surname, p.surname ) AS surname'
				."\n".' FROM #__apoth_ppl_people AS p'
				."\n".' INNER JOIN #__apoth_tt_group_members AS c ON c.person_id = p.id'
				."\n".' WHERE c.is_teacher = 1' // *** titikaka
				."\n".' AND '.JHTML::_( 'arc._dateCheck', 'c.valid_from', 'c.valid_to' )
				."\n".' ORDER BY COALESCE( p.preferred_surname, p.surname ), COALESCE( p.preferred_firstname, p.firstname )';
			$db->setQuery($query);
			$p1 = $db->loadObjectList('id');
			
			$query = 'SELECT DISTINCT p.id, p.title, COALESCE( p.preferred_firstname, p.firstname ) AS firstname, p.middlenames, COALESCE( p.preferred_surname, p.surname ) AS surname'
				."\n".' FROM #__apoth_ppl_people AS p'
				."\n".' INNER JOIN #__apoth_sys_com_roles AS cr ON cr.person_id = p.id'
				."\n".' AND cr.role = "'.ApotheosisLibAcl::getRoleId( 'group-supervisor-teacher' ).'" '
				."\n".' ORDER BY COALESCE( p.preferred_surname, p.surname ), COALESCE( p.preferred_firstname, p.firstname )';
			$db->setQuery($query);
			$p2 = $db->loadObjectList('id');
			
			$people = array_merge($people, $p1, $peopleDiv, $p2);
			
			foreach( $people as $key=>$row ) {
				$people[$key]->displayname = ApotheosisLib::nameCase('teacher', $row->title, $row->firstname, $row->middlenames, $row->surname);
			}
		break;
		
		case( 'pupils' ) :
			
			$query = 'SELECT DISTINCT p.id, p.title, COALESCE( p.preferred_firstname, p.firstname ) AS firstname, p.middlenames, COALESCE( p.preferred_surname, p.surname ) AS surname'
				."\n".' FROM #__apoth_ppl_people AS p'
				."\n".' INNER JOIN #__apoth_tt_group_members AS c ON c.person_id = p.id'
				."\n".' WHERE c.is_student = 1' // *** titikaka
				."\n".' AND '.JHTML::_( 'arc._dateCheck', 'c.valid_from', 'c.valid_to' )
				."\n".' ORDER BY COALESCE( p.preferred_surname, p.surname ), COALESCE( p.preferred_firstname, p.firstname )';
			$db->setQuery($query);
			$people = array_merge($people, $db->loadObjectList('id'));
			
			foreach( $people as $key=>$row ) {
				$people[$key]->displayname = ApotheosisLib::nameCase('pupil', $row->title, $row->firstname, $row->middlenames, $row->surname);
			}
		break;
		
		case( 'assignedMentees' ) :
			$query = 'SELECT DISTINCT p.id, p.title, COALESCE( p.preferred_firstname, p.firstname ) AS firstname, p.middlenames, COALESCE( p.preferred_surname, p.surname ) AS surname'
				."\n".' FROM #__apoth_ppl_people AS p'
				."\n".' INNER JOIN #__apoth_tt_group_members AS c ON c.person_id = p.id'
				."\n".' INNER JOIN #__apoth_plan_group_members AS gm ON gm.person_id = p.id'
				."\n".'   AND gm.role IN ( "assignee", "leader" ) '
				."\n".'   AND gm.valid_to IS NULL '
				."\n".' INNER JOIN #__apoth_plan_group_members AS gm2 ON gm2.group_id = gm.group_id'
				."\n".'   AND gm2.role = "admin" '
				."\n".'   AND gm2.person_id = '.$pid
				."\n".'   AND gm2.valid_to IS NULL '
				."\n".' WHERE c.is_student = 1' // *** titikaka
				."\n".' AND '.JHTML::_( 'arc._dateCheck', 'c.valid_from', 'c.valid_to' )
				."\n".' ORDER BY COALESCE( p.preferred_surname, p.surname ), COALESCE( p.preferred_firstname, p.firstname )';
			$db->setQuery($query);
			$p1 = $db->loadObjectList('id');
			//query??
			$query = 'SELECT DISTINCT p.id, p.title, COALESCE( p.preferred_firstname, p.firstname ) AS firstname, p.middlenames, COALESCE( p.preferred_surname, p.surname ) AS surname'
				."\n".' FROM #__apoth_ppl_people AS p'
				."\n".' INNER JOIN #__apoth_sys_com_roles AS cr ON cr.person_id = p.id'
				."\n".' AND cr.role = "'.ApotheosisLibAcl::getRoleId( 'group-supervisor-teacher' ).'" '
				."\n".' INNER JOIN #__apoth_plan_group_members AS gm ON gm.person_id = p.id'
				."\n".'   AND gm.role IN ( "assignee", "leader" ) '
				."\n".'   AND gm.valid_to IS NULL '
				."\n".' INNER JOIN #__apoth_plan_group_members AS gm2 ON gm2.group_id = gm.group_id'
				."\n".'   AND gm2.role = "admin" '
				."\n".'   AND gm2.valid_to IS NULL '
				."\n".'   AND gm2.person_id = '.$pid
				."\n".' ORDER BY COALESCE( p.preferred_surname, p.surname ), COALESCE( p.preferred_firstname, p.firstname )';
			$db->setQuery($query);
			$p2 = $db->loadObjectList('id');
			
			$people = array_merge($people, $p1, $peopleDiv, $p2);
			
			foreach( $people as $key=>$row ) {
				$people[$key]->displayname = ApotheosisLib::nameCase('pupil', $row->title, $row->firstname, $row->middlenames, $row->surname);
			}
		break;
		
		case( 'assignedMenteesStaff' ) :
			$query = 'SELECT DISTINCT p.id, p.title, COALESCE( p.preferred_firstname, p.firstname ) AS firstname, p.middlenames, COALESCE( p.preferred_surname, p.surname ) AS surname'
				."\n".' FROM #__apoth_ppl_people AS p'
				."\n".' INNER JOIN #__apoth_tt_group_members AS c ON c.person_id = p.id'
				."\n".' INNER JOIN #__apoth_plan_group_members AS gm ON gm.person_id = p.id'
				."\n".'   AND gm.role = "assignee" '
				."\n".' INNER JOIN #__apoth_plan_group_members AS gm2 ON gm2.group_id = gm.group_id'
				."\n".'   AND gm2.role = "admin" '
				."\n".'   AND gm2.valid_to IS NULL '
				."\n".'   AND gm2.person_id = '.$pid
				."\n".' WHERE c.is_teacher = 1' // *** titikaka
				."\n".' AND '.JHTML::_( 'arc._dateCheck', 'c.valid_from', 'c.valid_to' )
				."\n".' ORDER BY COALESCE( p.preferred_surname, p.surname ), COALESCE( p.preferred_firstname, p.firstname )';
			$db->setQuery($query);
			$p1 = $db->loadObjectList('id');
			//query??
			$query = 'SELECT DISTINCT p.id, p.title, COALESCE( p.preferred_firstname, p.firstname ) AS firstname, p.middlenames, COALESCE( p.preferred_surname, p.surname ) AS surname'
				."\n".' FROM #__apoth_ppl_people AS p'
				."\n".' INNER JOIN #__apoth_sys_com_roles AS cr ON cr.person_id = p.id'
				."\n".' AND cr.role = "'.ApotheosisLibAcl::getRoleId( 'group-supervisor-teacher' ).'" '
				."\n".' INNER JOIN #__apoth_plan_group_members AS gm ON gm.person_id = p.id'
				."\n".'   AND gm.role = "assignee" '
				."\n".' INNER JOIN #__apoth_plan_group_members AS gm2 ON gm2.group_id = gm.group_id'
				."\n".'   AND gm2.role = "admin" '
				."\n".'   AND gm2.valid_to IS NULL '
				."\n".'   AND gm2.person_id = '.$pid
				."\n".' ORDER BY COALESCE( p.preferred_surname, p.surname ), COALESCE( p.preferred_firstname, p.firstname )';
			$db->setQuery($query);
			$p2 = $db->loadObjectList('id');
			
			$people = array_merge($people, $p1, $peopleDiv, $p2);
			
			foreach( $people as $key=>$row ) {
				$people[$key]->displayname = ApotheosisLib::nameCase('pupil', $row->title, $row->firstname, $row->middlenames, $row->surname);
			}
		break;
		
		case( 'assignedMentors' ) :
			$query = 'SELECT DISTINCT p.id, p.title, COALESCE( p.preferred_firstname, p.firstname ) AS firstname, p.middlenames, COALESCE( p.preferred_surname, p.surname ) AS surname'
				."\n".' FROM #__apoth_ppl_people AS p'
				."\n".' INNER JOIN #__apoth_tt_group_members AS c ON c.person_id = p.id'
				."\n".' INNER JOIN #__apoth_plan_group_members AS gm ON gm.person_id = p.id'
				."\n".'   AND gm.role = "admin" '
				."\n".'   AND gm.valid_to IS NULL '
				."\n".' INNER JOIN #__apoth_plan_group_members AS gm2 ON gm2.group_id = gm.group_id'
				."\n".'   AND gm2.role = "assignee" '
				."\n".'   AND gm2.person_id = '.$pid
				."\n".' WHERE c.is_teacher = 1' // *** titikaka
				."\n".' AND '.JHTML::_( 'arc._dateCheck', 'c.valid_from', 'c.valid_to' )
				."\n".' ORDER BY COALESCE( p.preferred_surname, p.surname ), COALESCE( p.preferred_firstname, p.firstname )';
			$db->setQuery($query);
			$p1 = $db->loadObjectList('id');
			
			$query = 'SELECT DISTINCT p.id, p.title, COALESCE( p.preferred_firstname, p.firstname ) AS firstname, p.middlenames, COALESCE( p.preferred_surname, p.surname ) AS surname'
				."\n".' FROM #__apoth_ppl_people AS p'
				."\n".' INNER JOIN #__apoth_sys_com_roles AS cr ON cr.person_id = p.id'
				."\n".' AND cr.role = "'.ApotheosisLibAcl::getRoleId( 'group-supervisor-teacher' ).'" '
				."\n".' INNER JOIN #__apoth_plan_group_members AS gm ON gm.person_id = p.id'
				."\n".'   AND gm.role = "admin" '
				."\n".'   AND gm.valid_to IS NULL '
				."\n".' INNER JOIN #__apoth_plan_group_members AS gm2 ON gm2.group_id = gm.group_id'
				."\n".'   AND gm2.role = "assignee" '
				."\n".'   AND gm2.person_id = '.$pid
				."\n".' ORDER BY COALESCE( p.preferred_surname, p.surname ), COALESCE( p.preferred_firstname, p.firstname )';
			$db->setQuery($query);
			$p2 = $db->loadObjectList('id');
			
			$people = array_merge($people, $p1, $peopleDiv, $p2);
			
			foreach( $people as $key=>$row ) {
				$people[$key]->displayname = ApotheosisLib::nameCase('pupil', $row->title, $row->firstname, $row->middlenames, $row->surname);
			}
			
		break;
		}
		
		if ($multiple) {
			$retVal = JHTML::_( 'select.genericList', $people, $name.'[]', 'multiple="multiple" class="multi_medium"', 'id', 'displayname', $oldVal );
		}
		else {
			$retVal = JHTML::_( 'select.genericList', $people, $name, '', 'id', 'displayname', $oldVal );
		}
		return $retVal;
	}
	
	/**
	 * Generate HTML to display a person selector with the given name
	 *
	 * @param string $name  The name to use for the input
	 * @param string $default  Default input value (used for form reset)
	 * @param string $listName  The type of person to list (null [all], 'pupil', 'teacher')
	 * @param boolean $combo  Use combo box? If not, select list will be used
	 * @param boolean $params  Additional properties for the input
	 * @param array $restrict  Do we limit limit query etc? // *** temp solution until ACL can be used on back end
	 * @return string $retVal  The HTML to display the required input
	 */
	function people( $name, $default = null, $listName = null, $combo = true, $params = array(), $restrict = null )
	{
		$default = ( !is_null($default) ? $default : '' );
		$oldVal = JRequest::getVar( $name, $default );
		
		$list = ApotheosisData::_( 'people.people', $listName, $restrict, true );
		$blank = new stdClass();
		$blank->id = '';
		$blank->displayName = '';
		array_unshift( $list, $blank );
		
		if( $combo ) {
			$retVal = JHTML::_( 'arc.combo', $name, $params, $list, 'id', 'displayName', $oldVal );
		}
		else {
			if( isset($params['multiple']) ) {
				$retVal = JHTML::_('select.genericList', $list, $name.'[]', 'multiple="multiple" class="multi_medium"', 'id', 'displayName', $oldVal);
			}
			else {
				$retVal = JHTML::_('select.genericList', $list, $name, '', 'id', 'displayName', $oldVal);
			}
		}
		
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class="search_default"' );
		
		return $retVal;
	}


	/**
	 * Generate HTML to display an admin page profile template category management section
	 * 
	 * @param array $catInfo  Info about the category
	 * @param array $catData  The data for display
	 * @return string $html  The HTML
	 */
	function profilePanel( $catInfo, $catData )
	{
		$catId = $catInfo['id'];
		$catName = $catInfo['name'];
		$catIdCount = $catInfo['idCount'];
		
		// prepare the data to account for multiple profiles
		$data = array();
		if( is_array($catData) ) {
			foreach( $catData as $k=>$props ) {
				if( !array_key_exists($props['property'], $data) ) {
					$data[$props['property']] = array( 'value'=>$props['value'], 'count'=>1 );
				}
				else {
					if( array_key_exists('value', $data[$props['property']]) && ($props['value'] != $data[$props['property']]['value']) ) {
						unset( $data[$props['property']]['value'] );
					}
					$data[$props['property']]['count']++;
				}
			}
		}
		
		ob_start();
		JHTML::script( 'admin_profile_'.$catName.'.js', JURI::root().'components'.DS.'com_arc_people'.DS.'helpers'.DS.'html'.DS, true ); ?>
		<fieldset class="adminform_thinfieldset" style="vertical-align: top;">
			<legend><?php echo JText::_( ucfirst($catName) ); ?></legend>
			<table class="adminlist">
				<thead>
					<tr>
						<th><?php echo JText::_( 'Property' ); ?></th>
						<th><?php echo JText::_( 'Value' ); ?></th>
						<th><?php echo JText::_( 'Remove' ); ?></th>
					</tr>
				</thead>
				<tbody id="<?php echo $catName; ?>_tbody">
				<?php if( !empty($data) ): ?>
					<?php foreach( $data as $prop=>$propInfo ): ?>
						<?php
							$commonProp = ( $propInfo['count'] == $catIdCount );
							$commonValue = array_key_exists( 'value', $propInfo );
							$reservedProp = ( $prop == 'ARC' );
						?>
						<tr<?php echo !$reservedProp ? ' class="row_'.$catName.'"' : ''; ?>>
							<?php if( $commonProp ): ?>
								<td class="<?php echo $catName; ?>_property">
									<?php echo JText::_( ucfirst($prop) ); ?>
								</td>
							<?php else: ?>
								<td class="<?php echo $catName; ?>_property <?php echo $catName; ?>_partial_property" style="cursor: pointer;" >
									<span style="color: red;"><?php echo JText::_( ucfirst($prop) ); ?></span>
									<input type="hidden" name="partials[<?php echo $catId; ?>][]" value="<?php echo $prop; ?>" />
								</td>
							<?php endif; ?>
							<?php if( $commonValue ): ?>
								<td>
									<div style="float: left;">
										<input class="<?php echo $catName; ?>_value" type="text" name="cats<?php echo '['.$catId.']['.$prop.']'; ?>" value="<?php echo $propInfo['value']; ?>" size="50" <?php echo $reservedProp ? 'readonly="readonly"' : ''; ?> />
									</div>
									<?php if( !$commonProp && !$reservedProp ): ?>
										<div class="<?php echo $catName; ?>_value_lock" style="float: left; background-image: url(<?php echo JHTML::_( 'arc.image', 'padlock_16', '', true ); ?>); background-repeat: no-repeat; height: 16px; width: 19px; cursor: pointer; display: none;"></div>
									<?php endif; ?>
								</td>
							<?php else: ?>
								<td>
									<div style="float: left;">
										<input type="text" name="cats<?php echo '['.$catId.']['.$prop.']'; ?>" value="*** Locked ***" size="50" style="background: #FFFFDD; text-align: center;" readonly="readonly" />
									</div>
									<?php if( !$reservedProp ): ?>
										<div class="<?php echo $catName; ?>_value_lock" style="float: left; background-image: url(<?php echo JHTML::_( 'arc.image', 'padlock_16', '', true ); ?>); background-repeat: no-repeat; height: 16px; width: 19px; cursor: pointer;"></div>
									<?php endif; ?>
								</td>
							<?php endif; ?>
							<td style="text-align: center;">
								<?php
									if( !$reservedProp ) {
										echo JHTML::_( 'arc.image', 'remove-16', 'border="0" alt="Remove" title="Remove" class="remove_'.$catName.'_'.$prop.'" style="cursor: pointer;"' );
									}
									else {
										echo '&nbsp;';
									}
								?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
					<tr id="<?php echo $catName; ?>_add_row">
						<td colspan="3">Add a new property: 
							<input type="text" size="30" id="new_<?php echo $catName; ?>_prop_input" value="" />
							<?php echo JHTML::_( 'arc.image', 'add-16', 'border="0" alt="Add" title="Add" id="add_'.$catName.'" style="cursor: pointer; vertical-align: middle;"' ); ?>
						</td>
					</tr>
					<tr id="arch_<?php echo $catName; ?>_row" class="row_<?php echo $catName; ?>">
						<td>_<?php echo $catName; ?>_property_</td>
						<td>
							<input class="<?php echo $catName; ?>_value" type="text" name="cats<?php echo '['.$catId.'][_'.$catName.'_property_]'; ?>" value="" size="50" />
						</td>
						<td style="text-align: center;">
							<?php echo JHTML::_( 'arc.image', 'remove-16', 'border="0" alt="Remove" title="Remove" class="remove_'.$catName.'__'.$catName.'_property_" style="cursor: pointer;"' ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php $html = ob_get_clean();
		
		return $html;
	}
	
	function lists( $name, $default = null, $includeVariable = true, $combo = true, $params = array(), $restrict = null)
	{
		$default = ( !is_null($default) ? $default : '' );
		$oldVal = JRequest::getVar( $name, $default );
		
		$lists = ApotheosisData::_( 'people.peopleListNames', $includeVariable );
		$list = array();
		$blank = new stdClass();
		$blank->id = '';
		$blank->name = '';
		array_unshift( $list, $blank );
		foreach( $lists as $item ) {
			$ob = new stdClass();
			$ob->id = '~'.$item.'~';
			$ob->name = ucfirst( $item );
			$list[] = $ob;
		}
		
		if( $combo ) {
			$retVal = JHTML::_( 'arc.combo', $name, $params, $list, 'id', 'name', $oldVal );
		}
		else {
			if( isset($params['multiple']) ) {
				$retVal = JHTML::_('select.genericList', $list, $name.'[]', 'multiple="multiple" class="multi_medium"', 'id', 'name', $oldVal);
			}
			else {
				$retVal = JHTML::_('select.genericList', $list, $name, '', 'id', 'name', $oldVal);
			}
		}
		
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class="search_default"' );
		
		return $retVal;
		
	}
}
?>