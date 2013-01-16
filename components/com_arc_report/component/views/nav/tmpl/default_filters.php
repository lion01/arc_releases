<?php
JHTML::script( 'filters.js', $this->scriptPath, true );
JHTML::stylesheet( 'filters.css', $this->scriptPath );
?>
<div id="filter_wrapper">
<div id="filter">
	<input type="hidden" id="filter_url" value="<?php echo ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_nav_elements', array( 'report.navelement'=>'filterList', 'report.params'=>'~PARAMS~', 'report.format'=>'json' ) ); ?>" />
	<input type="hidden" id="set_filters_url" value="<?php echo ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_nav_setfilters', array( 'report.format'=>'json' ) ); ?>" />
	<div id="filter_toggle">
		<a href="#" class="btn closed"><span></span></a>
	</div>
	<div id="filter_terms">
		<a id="filter_reset" href="#"><span>Reset All</span></a>
		<div class="filter_heading" id="filter_head_subject"><a href="#">Subject<span></span></a></div>
		<div class="filter_body">
			<div class="filter_list" id="filter_list_subject"></div>
			<div class="filter_loading" ><div><?php echo JHTML::_( 'arc.loading', '<br />Getting filters' );?></div></div>
		</div>
		
		<div class="filter_heading" id="filter_head_group"  ><a href="#">Group<span></span></a></div>
		<div class="filter_body">
			<div class="filter_list" id="filter_list_group"  ></div>
			<div class="filter_loading" ><div><?php echo JHTML::_( 'arc.loading', '<br />Getting filters' );?></div></div>
		</div>
		
		<div class="filter_heading" id="filter_head_student"><a href="#">Student<span></span></a></div>
		<div class="filter_body">
			<div class="filter_list" id="filter_list_student"></div>
			<div class="filter_loading" ><div><?php echo JHTML::_( 'arc.loading', '<br />Getting filters' );?></div></div>
		</div>
		
		<div class="filter_heading" id="filter_head_status" ><a href="#">Status<span></span></a></div>
		<div class="filter_body">
			<div class="filter_list" id="filter_list_status" ></div>
			<div class="filter_loading" ><div><?php echo JHTML::_( 'arc.loading', '<br />Getting filters' );?></div></div>
		</div>
	</div>
</div>
</div>
