<?php
/**
 * @package     Arc
 * @subpackage  Message
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
JHTML::_('behavior.tooltip');
?>
<div id="msg_head">
<div id="controls">
<form method="post" name="msg_control_form">
<input type="hidden" name="thread_ids" id="thread_ids" value="" />
<button type="submit" class="btn" id="button_refresh" name="task" title="Refresh" value="refresh"><span>&nbsp;Refresh</span></button>

<?php
if( !$this->empty ) {
	switch( strtolower($this->folder->getLabel()) ) {
	case( 'inbox' ):
		echo '<button type="submit" class="btn" id="button_archive" name="task" title="Archive" value="archive"><span>&nbsp;Archive</span></button>';
		break;
	case( 'archive' ):
		echo '<button type="submit" class="btn" id="button_revive" name="task" title="Revive" value="revive"><span>&nbsp;Revive</span></button>';
		break;
	case( 'drafts' ):
		echo '<button type="submit" class="btn" id="button_delete" name="task" title="Delete" value="delete"><span>&nbsp;Delete</span></button>';
		break;
	default:
		echo '<button type="submit" class="btn" id="button_revive" name="task" title="Revive" value="revive"><span>&nbsp;Revive</span></button>';
		echo '<button type="submit" class="btn" id="button_archive" name="task" title="Archive" value="archive"><span>&nbsp;Archive</span></button>';
		break;
	}
}
if( (($link = ApotheosisLibAcl::getUserLinkAllowed('apoth_msg_hub_pdf', array())) != false) && !$this->empty ) {
	?>
	<a id="pdf_link" name="pdf_link" href="<?php echo $link; ?>"><span>&nbsp;Get PDF</span></a>
	<?php
}
?>
</form>

<?php
$linkReq = array( 'core.page'=>0, 'message.tags'=>$this->get( 'tagIds' ), 'message.scopes'=>JRequest::getVar( 'scope' ) );
if( $this->page > 1 ) {
	$linkReq['core.page'] = 0;
	$prevPage = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_msg_hub_paged', $linkReq );
	echo '<a href="'.$prevPage.'" class="btn">&lt;&lt;</a>'."\r\n";
}
if( $this->page > 0 ) {
	$linkReq['core.page'] = $this->page-1;
	$prevPage = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_msg_hub_paged', $linkReq );
	echo '<a href="'.$prevPage.'" class="btn">&lt;</a>'."\r\n";
}
echo 'page '.($this->page + 1).' / '.$this->pageCount."\r\n";
if( $this->page+1 < $this->pageCount ) {
	$linkReq['core.page'] = $this->page+1;
	$nextPage = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_msg_hub_paged', $linkReq );
	echo '<a href="'.$nextPage.'" class="btn">&gt;</a>'."\r\n";
}
if( $this->page+2 < $this->pageCount ) {
	$linkReq['core.page'] = $this->pageCount-1;
	$nextPage = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_msg_hub_paged', $linkReq );
	echo '<a href="'.$nextPage.'" class="btn">&gt;&gt;</a>'."\r\n";
}

if( $this->pageCount > 2 ) {
	$linkReq['core.page'] = 0;
	$action = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_msg_hub_paged', $linkReq );
	
	$u = new JURI( $action );
	$parts = $u->getQuery( true );
	$action = $u->toString( array('scheme', 'host', 'port', 'path') );
	unset( $parts['page'] );
	
	$o = new stdClass();
	$o->id = -1;
	$o->label = 'Go to';
	$pages = array( $o );
	for( $i = 0; $i < $this->pageCount; $i++ ) {
		$o = new stdClass();
		$o->id = $i;
		$o->label = $i+1;
		$pages[] = $o;
	}
	echo '<form method="get" action="'.$action.'" id="gotoPageForm">';
	echo JHTML::_( 'select.genericlist', $pages, 'page', 'onChange="javascript:$(\'gotoPageForm\').submit();"', 'id', 'label' );
	foreach( $parts as $k=>$v ) {
		if( is_array( $v ) ) {
			foreach( $v as $vk=>$vv ) {
				echo JHTML::_( 'arc.hidden', $k.'['.$vk.']', $vv );
			}
		}
		else {
			echo JHTML::_( 'arc.hidden', $k, $v );
		}
	}
	echo '</form>';
}
?>
</div>

<div id="msg_search">
<form method="post" name="msg_control_form" class="form-search">
<input type="text" class="" name="msg_search"<?php $tmp = JRequest::getVar('msg_search', ''); echo ( empty($tmp) ? '' : ' value="'.$tmp.'"' ); ?> />
<button type="submit" class="btn" id="button_search" name="task" title="Search" value="Search"><span class="search_ico"></span><span class=search_text>Search</span></button>
</form>
</div>
</div>

<div id="msg_list">
<table>
<?php
if( $this->empty ) {
	echo '<tr><td style="text-align: center; padding-top: 1em;">'.$this->emptyMessage.'</td></tr>';
}
else {
	$this->thread = $this->get('Thread');
	echo JHTML::_( 'arc_message.render', 'renderThreadListHeader', 'thread', $this->thread->getId() );
	while( $this->thread ) {
		echo JHTML::_( 'arc_message.render', 'renderThreadListRow', 'thread', $this->thread->getId() );
		$this->thread = $this->get('Thread');
	}
}
?>
</table>
</div>