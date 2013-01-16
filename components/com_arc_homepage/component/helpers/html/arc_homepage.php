<?php
/**
 * @package     Arc
 * @subpackage  Homepage
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Utility class for creating html
 *
 * @static
 * @package 	Arc
 * @subpackage	Homepage
 * @since		1.0
 */
class JHTMLArc_Homepage
{
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
		
		switch( $catName ) {
		case 'panels':
			JHTML::script( 'admin_profile_panel.js', JURI::root().'components'.DS.'com_arc_homepage'.DS.'helpers'.DS.'html'.DS, true );
			
			// proceed with processing any existing data
			if( is_array($catData) ) {
				$layout = array();
				foreach( $catData as $props ) {
					// take care of differences in line endings
					$props['value'] = str_replace( array("\r\n", "\r"), "\n", $props['value'] );
					$data = explode( "\n", $props['value'] );
					
					// split data string into usable associative array
					foreach( $data as $datumString ) {
						$datumArray = explode( '=', $datumString, 2 );
						$datum[$datumArray[0]] = $datumArray[1]; 
					}
					
					// check if we are already tracking a given column number
					if( !array_key_exists($datum['col'], $layout) ) {
						// if not then initialise it
						$layout[$datum['col']] = array();
					}
					
					$found = false;
					foreach( $layout[$datum['col']] as $prop=>$propArray ) {
						if( $propArray['id'] == $datum['id'] ) {
							$found = true;
							// as soon as we find a shown value that is different, give it a value of 2
							if( $propArray['shown'] != '2' ) {
								if( $propArray['shown'] != $datum['shown'] ) {
									$layout[$datum['col']][$prop]['shown'] = '2';
								}
							}
							
							// increment count of this panels presence
							$layout[$datum['col']][$prop]['count']++;
						}
					}
					
					// we didn't find it so add it for the first time with appropriate data values
					// checking that we don't overwrite an exisiting property
					if( !$found ) {
						while( array_key_exists($props['property'], $layout[$datum['col']]) ) {
							$props['property']++;
						}
						
						$layout[$datum['col']][$props['property']] = array( 'id'=>$datum['id'], 'shown'=>$datum['shown'], 'count'=>1 );
					}
				}
				
				// sort the display order based on property
				foreach( $layout as $col=>$panels ) {
					ksort( $panels, SORT_NUMERIC );
					$layout[$col] = $panels;
				}
			}
			
			$panelIds = array_flip( ApotheosisData::_('homepage.panelIds') );
			$panelColsCount = ApotheosisData::_( 'homepage.panelColsCount' );
			ob_start(); ?>
			<div class="clr"></div>
				<?php for( $colNum = 1; $colNum <= $panelColsCount; $colNum++ ): ?>
				<fieldset class="adminform_thinfieldset" style="vertical-align: top;">
					<legend><?php echo JText::_( 'Homepage Column '.$colNum );?></legend>
					<table class="adminlist">
						<thead>
							<tr>
								<th><?php echo JText::_( 'Panel Name' ); ?></th>
								<th><?php echo JText::_( 'Order' ); ?></th>
								<th><?php echo JText::_( 'Visibility' ); ?></th>
								<th><?php echo JText::_( 'Remove' ); ?></th>
							</tr>
						</thead>
						<tbody id="panel_row_tbody_<?php echo $colNum; ?>">
						<?php if( is_array($layout[$colNum]) ): ?>
							<?php foreach( $layout[$colNum] as $pId=>$pInfo ): ?>
								<?php if( !is_null($panelTitle = ApotheosisData::_('homepage.panelTitle', $pInfo['id'])) ) : ?>
									<?php unset( $panelIds[$pInfo['id']] ); ?>
									<tr class="row_<?php echo $colNum; ?>">
										<?php if( $pInfo['count'] == $catIdCount ): ?>
											<td>
												<?php echo $panelTitle; ?>
											</td>
										<?php else: ?>
											<td class="partial_panel" style="cursor: pointer;" >
												<span style="color: red;"><?php echo $panelTitle; ?></span>
												<input type="hidden" name="partials[<?php echo $catId; ?>][]" value="<?php echo $pInfo['id']; ?>" />
											</td>
										<?php endif; ?>
										<td class="arrows_<?php echo $colNum; ?>" style="text-align: center;"></td>
										<td style="text-align: center;">
											<div id="panel_div_<?php echo $pInfo['id']; ?>">
												<?php if( $pInfo['shown'] == '2' ) : ?>
													<span style="color: orange;">Mixed</span>
												<?php elseif( $pInfo['shown'] == '1' ) : ?>
													<span style="color: green;">Active</span>
												<?php elseif( $pInfo['shown'] == '0' ) : ?>
													<span style="color: red;">Hidden</span>
												<?php endif; ?>
											</div>
										</td>
										<td style="text-align: center;">
											<?php echo JHTML::_( 'arc.image', 'remove-16', 'border="0" alt="Remove Panel" title="Remove Panel" class="remove_panel_'.$colNum.' panel_id_'.$pInfo['id'].'" style="cursor: pointer;"' ); ?>
											<input type="hidden" id="panel_id_<?php echo $pInfo['id']; ?>" value="<?php echo $pInfo['id']; ?>" />
											<input type="hidden" id="panel_col_<?php echo $pInfo['id']; ?>" value="<?php echo $colNum; ?>" />
											<input type="hidden" id="panel_shown_<?php echo $pInfo['id']; ?>" value="<?php echo $pInfo['shown']; ?>" />
											<input type="hidden" id="panel_cats_<?php echo $pInfo['id']; ?>" name="cats<?php echo '['.$catId.'][]'; ?>" />
										</td>
									</tr>
								<?php endif; ?>
							<?php endforeach; ?>
						<?php endif; ?>
							<tr class="row_<?php echo $colNum; ?> arch_panel_row">
								<td>_panel_name_</td>
								<td class="arrows_<?php echo $colNum; ?>" style="text-align: center;"></td>
								<td style="text-align: center;">
									<div id="panel_div__panel_id_"><span style="color: green;">Active</span></div>
								</td>
								<td style="text-align: center;">
									<?php echo JHTML::_( 'arc.image', 'remove-16', 'border="0" alt="Remove Panel" title="Remove Panel" class="remove_panel_'.$colNum.' panel_id__panel_id_" style="cursor: pointer;"' ); ?>
									<input type="hidden" id="panel_id__panel_id_" value="_panel_id_" />
									<input type="hidden" id="panel_col__panel_id_" value="<?php echo $colNum; ?>" />
									<input type="hidden" id="panel_shown__panel_id_" value="1" />
									<input type="hidden" id="panel_cats__panel_id_" name="cats<?php echo '['.$catId.'][]'; ?>" />
								</td>
							</tr>
						</tbody>
					</table>
				</fieldset>
				<?php endfor; ?>
			<?php if( !empty($panelIds) ): ?>
				<?php $panelIds = array_flip( $panelIds ); ?>
				<fieldset class="adminform_thinfieldset" style="vertical-align: top;">
					<legend><?php echo JText::_( 'Unused Panels' );?></legend>
					<table class="adminlist">
						<thead>
							<tr>
								<th rowspan="2"><?php echo JText::_( 'Panel Name' ); ?></th>
								<th colspan="<?php echo $panelColsCount; ?>"><?php echo JText::_( 'Target Column' ); ?></th>
							</tr>
							<tr>
							<?php for( $cols = 1; $cols <= $panelColsCount; $cols++ ): ?>
								<th style="text-align: center;"><?php echo $cols; ?></th>
							<?php endfor; ?>
							</tr>
						</thead>
						<tbody id="add_panel_tbody">
						<?php foreach( $panelIds as $panelId ): ?>
							<tr>
								<td><?php echo JText::_( ApotheosisData::_('homepage.panelTitle', $panelId) ); ?></td>
								<?php for( $cols = 1; $cols <= $panelColsCount; $cols++ ): ?>
									<td style="text-align: center;"><?php echo JHTML::_( 'arc.image', 'add-16', 'border="0" alt="Add Panel" title="Add Panel to Column '.$cols.'" class="add_panel_'.$panelId.'_'.$cols.'" style="cursor: pointer;"' ); ?></td>
								<?php endfor; ?>
							</tr>
						<?php endforeach; ?>
							<tr id="arch_panel_add">
								<td>_panel_name_</td>
								<?php for( $cols = 1; $cols <= $panelColsCount; $cols++ ): ?>
									<td style="text-align: center;"><?php echo JHTML::_( 'arc.image', 'add-16', 'border="0" alt="Add Panel" title="Add Panel to Column '.$cols.'" class="add_panel__panel_id__'.$cols.'" style="cursor: pointer;"' ); ?></td>
								<?php endfor; ?>
							</tr>
						</tbody>
					</table>
				</fieldset>
			<?php endif; ?>
			<input type="hidden" id="panel_cols_count" name="panel_cols_count" value="<?php echo $panelColsCount; ?>" />
			<?php $html = ob_get_clean();
			break;
		
		case 'links':
			JHTML::script( 'admin_profile_links.js', JURI::root().'components'.DS.'com_arc_homepage'.DS.'helpers'.DS.'html'.DS, true );
			$document = &JFactory::getDocument();
			$document->addStyleDeclaration('.tool-tip { max-width: 400px !important; }');
			$linkInfo = ApotheosisData::_( 'homepage.linkInfo' );
			$linkPanels = ApotheosisData::_('homepage.linkPanelNames');
			$linkPanelNames = array_flip( $linkPanels );
			foreach( $linkPanelNames as $linkPanelName=>$v ) {
				$linkPanelNames[$linkPanelName] = array();
			}
			if( is_array($catData) ) {
				foreach( $catData as $linkArray ) {
					$link = $linkInfo[$linkArray['value']];
					if( !array_key_exists($linkArray['value'], $linkPanelNames[$link['panel']]) ) {
						$linkPanelNames[$link['panel']][$linkArray['value']] = array( 'text'=>$link['text'], 'url'=>$link['url'], 'count'=>1 );
					}
					else {
						$linkPanelNames[$link['panel']][$linkArray['value']]['count']++;
					}
				}
			}
			ob_start(); ?>
			<div class="clr"></div>
			<?php foreach( $linkPanelNames as $panel=>$links ) : ?>
				<fieldset class="adminform_thinfieldset" style="vertical-align: top;">
					<legend><?php echo JText::_( ucfirst($panel) );?></legend>
					<table class="adminlist">
						<thead>
							<tr>
								<th>Link</th>
								<th>Order</th>
								<th>Remove</th>
							</tr>
						</thead>
						<tbody id="link_row_tbody_<?php echo $panel; ?>">
							<?php $linkIds = array_flip( ApotheosisData::_('homepage.linkIds', $panel) ); ?>
							<?php foreach( $links as $linkId=>$linkData ): ?>
								<tr class="row_<?php echo $panel; ?>">
									<?php if( $linkData['count'] == $catIdCount ): ?>
										<td>
											<span class="hasTip" title="URL::<?php echo $linkData['url']; ?>" style="cursor: crosshair;"><?php echo JText::_( $linkData['text'] ); ?></span>
										</td>
									<?php else: ?>
										<td class="partial_link">
											<span class="hasTip" title="URL::<?php echo $linkData['url']; ?>" style="color: red; cursor: pointer;"><?php echo JText::_( $linkData['text'] ); ?></span>
											<input type="hidden" name="partials[<?php echo $catId; ?>][]" value="<?php echo $linkId; ?>" />
										</td>
									<?php endif; ?>
									<td class="link_arrows_<?php echo $panel; ?>" style="text-align: center;"></td>
									<td style="text-align: center;">
										<?php echo JHTML::_( 'arc.image', 'remove-16', 'border="0" alt="Remove Link" title="Remove Link" class="remove_link_'.$panel.' link_id_'.$linkId.'" style="cursor: pointer;"' ); ?>
										<input type="hidden" name="cats<?php echo '['.$catId.'][]'; ?>" value="<?php echo $linkId; ?>" />
									</td>
								</tr>
								<?php unset( $linkIds[$linkId] ); $usedLinks[] = $linkId; ?>
							<?php endforeach; ?>
							<tr class="row_<?php echo $panel; ?> arch_link_row">
								<td><span class="hasTip" title="_link_title_" style="cursor: pointer;">_link_text_</span></td>
								<td class="link_arrows_<?php echo $panel; ?>" style="text-align: center;"></td>
								<td style="text-align: center;">
									<?php echo JHTML::_( 'arc.image', 'remove-16', 'border="0" alt="Remove Link" title="Remove Link" class="remove_link_'.$panel.' link_id__link_id_" style="cursor: pointer;"' ); ?>
									<input type="hidden" name="cats<?php echo '['.$catId.'][]'; ?>" value="_link_id_" />
								</td>
							</tr>
						</tbody>
					</table>
					<div id="unused_select_div_<?php echo $panel; ?>"><br />Add:
						<select id="unused_select_<?php echo $panel; ?>">
						<?php foreach( $linkIds as $unusedLinkId=>$v ) : ?>
							<option id="unused_link_<?php echo $panel.'_'.$unusedLinkId; ?>" class="hasTip" title="URL::<?php echo $linkInfo[$unusedLinkId]['url']; ?>" style="cursor: pointer;"><?php echo JText::_( $linkInfo[$unusedLinkId]['text'] ); ?></option>
						<?php endforeach; ?>
						</select>
					</div>
					<div style="display: none;">
						<select id="used_select_<?php echo $panel; ?>">
						<?php $usedLinks = array_flip( $usedLinks ); ?>
						<?php foreach( $usedLinks as $usedLinkId=>$v ) : ?>
							<option id="used_link_<?php echo $panel.'_'.$usedLinkId; ?>" class="hasTip" title="URL::<?php echo $linkInfo[$usedLinkId]['url']; ?>" style="cursor: pointer;"><?php echo JText::_( $linkInfo[$usedLinkId]['text'] ); ?></option>
						<?php endforeach; ?>
						</select>
					</div>
				</fieldset>
			<?php endforeach; ?>
			<input type="hidden" id="link_panel_names" name="link_panel_names" value="<?php echo htmlspecialchars( json_encode($linkPanels) ); ?>" />
			<?php $html = ob_get_clean();
			break;
		}
		
		return $html;
	}
}
?>