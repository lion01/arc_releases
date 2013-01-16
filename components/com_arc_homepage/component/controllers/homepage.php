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
 * Homepage Task Controller
 * 
 * @author     p.walker@wildern.hants.sch.uk
 * @package    Arc
 * @subpackage Homepage
 * @since      0.1
 */
class HomepageControllerHomepage extends HomepageController
{
	/**
	 * Displays a homepage page depending on the scope specified
	 * by calling the relevant function in the view
	 */
	function display()
	{
		$scope = JRequest::getWord('scope');
		$model = &$this->getModel( 'homepage', array(), $scope );
		$view  = &$this->getView ( 'homepage', 'html' );
		$view->setModel( $model, true );
		$view->link = $this->_getLink();
		$reuse = true;
		
		switch( $scope ) {
		case( 'eportfolio' ):
			$u = &ApotheosisLib::getUser();
			$model->setProfile( $u->person_id );
			$this->profile = $model->getProfile();
			$tmp = $this->profile->getIds();
			$arcId = $tmp['ARC'];
			
			$blogAction = ApotheosisLib::getActionId( array('option'=>'com_lyftenbloggie'), array('author'=>'people.jusers') );
			$blogLink = ApotheosisLib::getActionLink( $blogAction, array('people.jusers'=>$u->id) );
			
			$p = new ApothPanel(1, array(
				'id'=>1
				,'alt'=>'My Links'
				,'type'=>'faked' ) );
			$p->setText( ApotheosisPeopleData::getAvatar( $arcId )
				."\n".'<a href="'.$blogLink.'">My Blog</a>'
				."\n".'<br /><a href="#">My Documents</a>'
//				."\n".'<br /><a href="#">My Calendar</a>'
//				."\n".'<br /><a href="#">My PAP</a>'
				);
			$model->setPanel( $p );
			
			$p = new ApothPanel(2, array(
				'id'=>2
				,'alt'=>'My Showcase'
				,'url'=>'&view=profile&Itemid=327&task=search&pId=~ARC~&scope=panel&panel=showcase&format=raw'
				,'option'=>'com_arc_people'
				,'type'=>'internal' ) );
			$model->setPanel( $p );
			
			$p = new ApothPanel(10, array(
				'id'=>10
				,'alt'=>'My Active Questions'
				,'url'=>'&view=task&Itemid=319&task=search&pId=~ARC~&scope=panel&panel=activeQuestions&format=raw'
				,'option'=>'com_arc_planner'
				,'type'=>'internal'
				,'jscript'=>'components/com_arc_homepage/views/panels/tmpl/panel_modal.js'
				,'css'=>'components/com_arc_planner/views/task/tmpl/panel.css' ) );
			$model->setPanel( $p );
			
			$hasSen = ApotheosisLibAcl::getUserPermitted( null, ApotheosisLib::getActionIdByName('eportfolio_sen_list') );
			if( $hasSen ) {
				$p = new ApothPanel(11, array(
					'id'=>11
					,'alt'=>'My SEN profile'
					,'url'=>'&view=profile&Itemid=332&task=search&pId=~ARC~&scope=panel&panel=sen&format=raw'
					,'option'=>'com_arc_people'
					,'type'=>'internal' ) );
				$model->setPanel( $p );
			}
			
			$p = new ApothPanel(20, array(
				'id'=>20
				,'alt'=>'Who I am'
				,'url'=>'&view=profile&Itemid=332&task=search&pId=~ARC~&scope=panel&panel=biography&format=raw'
				,'option'=>'com_arc_people'
				,'type'=>'internal'
				,'jscript'=>'components/com_arc_homepage/views/panels/tmpl/panel_modal.js' ) );
			$model->setPanel( $p );
			
			$p = new ApothPanel(21, array(
				'id'=>21
				,'alt'=>'My Latest Blog Entry'
				,'type'=>'faked' ) );
			$p->setText( $this->getBlogText() );
			// Blog entries for a user:
			// http://fla90/j_live_clone_prelim/index.php?option=com_lyftenbloggie&author=63&Itemid=333
			$model->setPanel( $p );
			break;
		
		case( 'home' );
		default:
			$pId = JRequest::getVar( 'pId', false );
			$reuse = ($pId === false);
			$u = &ApotheosisLib::getUser();
			if( $pId === false ) {
				$pId = $u->person_id;
			}
			elseif( $pId != $u->person_id ) {
				$model->clearPanels();
				$model->setProfile( $u->person_id );
				$model->setPanels( array(), true );
			}
			$model->setProfile( $pId );
			$model->setPanels();
			if( $model->getNumPanels() == 0 ) {
				global $mainframe;
				$mainframe->redirect( 'index.php' );
			}
		}
		$view->fullPage( $scope );
		
		if( $reuse ) {
			$this->saveModel( $scope );
		}
	}
	
	/**
	 * I know this is the wrong place to put this
	 * @return string  The text to display in the blog panel
	 */
	function getBlogText()
	{
		$db = &JFactory::getDBO();
		
		// work out the person's juserid (as that's what gets used as blog entry creator)
		$ids = $this->profile->getIds();
		$query = 'SELECT `juserid`'
			."\n".' FROM `#__apoth_ppl_people` AS p'
			."\n".' WHERE `id` = '.$db->Quote($ids['ARC']);
		$db->setQuery( $query );
		$uId = $db->loadResult();
		
		$query = 'SELECT `title`, `introtext`, `fulltext`'
			."\n".'FROM #__bloggies_entries'
			."\n".'WHERE '.$db->nameQuote('created_by').'='.$db->Quote($uId)
			."\n".'ORDER BY '.$db->nameQuote('created').' DESC';
		$db->setQuery( $query );
		$entries = $db->loadObjectList();
		
		$out ='<a id="newBlogEntry" class="modal" target="blank" rel="{handler: \'iframe\', size: {x: 640, y: 480}}" href="index.php?option=com_lyftenbloggie&view=entry&layout=form&Itemid=334&tmpl=component">New Entry</a>'
			.' <small>(You\'ll then need to refresh this page to see your post )</small>'; 
		foreach( $entries as $entry ) {
			$out .= '<h3>'.$entry->title.'</h3>'
				.$entry->introtext
				.$entry->fulltext
				.'<br />';
		}
		
		return $out;
	}
}
?>