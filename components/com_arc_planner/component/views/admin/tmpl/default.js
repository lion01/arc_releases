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

window.addEvent('domready', function() {

	var mentors = $('mentors');
	var assignedMentees = $('assignedMentees');
	var mentees = $('mentees');
	var assignedMentors = $('assignedMentors');
	var tasksDiv = $('tasksDiv');
	var tasksDiv2 = $('tasksDiv2');
	var removeMentors = $('removeMentors');
	var removeMentees = $('removeMentees');
	var allTasks = $('allTasks');
	var assign = $('assign');
	var removeTasksL = $('removeTasksL');
	var tasks1 = $('tasks1');
	var removeTasksR = $('removeTasksR');
	var tasks2 = $('tasks2');
	var add_tasks = $('add_tasks');
	var addTasks = $('addTasks');
	var whichPM = $('whichPM');	
	var ser = new PHP_Serializer();
	var check = 0;
	var msg1;
	var msg2;
	var loc = 'confirm1';
	var reset = 0;
	var menteesDiv = $('menteesDiv');
	var assignedMentorsDiv = $('assignedMentorsDiv');
	var menteeDiv = $('menteeDiv');
	var addTasksDiv = $('addTasksDiv');
	var pos = 'a';
	var clear = 0;


	function infoLeft( pos ) {

		switch(pos)
		{
		case 't':
			if ( $('mentors').getValue().length != 0 ) {
		
				var l = mentors.options.length;
				var textArr = new Array();

				for( var i=0; i<l; i++ ) {
					if( mentors.options[i].selected ) {
						textArr.push( mentors.options[i].text );
					}
				}
				text = textArr.join( '<br />' );
				$('info1').setHTML( text );		
			}
		break;
		
		
		case 'c':
		
			if ( $('assignedMentees').getValue().length != 0 ) {

				var l = $('assignedMentees').options.length;
				
				var textArr = new Array();
	
				for( var i=0; i<l; i++ ) {
					if( $('assignedMentees').options[i].selected ) {
						textArr.push( $('assignedMentees').options[i].text );
					}
				}
				text = textArr.join( '<br />' );
				$('info2').setHTML( text );
			}
		break;
		
		case 'b':
		
			if ( $('tasks1').getValue().length != 0 ) {
				var l = $('tasks1').options.length;
				var textArr = new Array();
				
				for( var i=0; i<l; i++ ) {
					if( $('tasks1').options[i].selected ) {
						textArr.push( $('tasks1').options[i].text );
					}
				}
				text = textArr.join( '<br />' );
				$('info3').setHTML( text );
			}
		break;
		}
	}

	function infoMiddle( pos ) {

		switch(pos)
		{
		case 't':
			if ( $('mentees').getValue().length != 0 ) {
				var l = mentees.options.length;
				var textArr = new Array();

				for( var i=0; i<l; i++ ) {
					if( mentees.options[i].selected ) {
						textArr.push( mentees.options[i].text );
					}
				}
		
				text = textArr.join( '<br />' );
		
				$('info2').setHTML( text );
			}				

		break;	
		
		case 'c':
			if ( $('assignedMentors').getValue().length != 0 ) {

				var l = $('assignedMentors').options.length;
			
				var textArr = new Array();
	
				for( var i=0; i<l; i++ ) {
					if( $('assignedMentors').options[i].selected ) {
						textArr.push( $('assignedMentors').options[i].text );
					}
				}
	
				text = textArr.join( '<br />' );
			
				$('info1').setHTML( text );
			}
		break;
		
		case 'b':
			if ( $('tasks2').getValue().length != 0 ) {
				var l = $('tasks2').options.length;
				var textArr = new Array();

				for( var i=0; i<l; i++ ) {
					if( $('tasks2').options[i].selected ) {
						textArr.push( $('tasks2').options[i].text );
					}
				}
				text = textArr.join( '<br />' );
				$('info3').setHTML( text );	
			}
		break;
		}
		
	}

	function infoRight( pos ) {
		switch(pos)
		{
		case 't':		
			if( $('allTasks').getValue().length != 0 ) {
				var l = $('allTasks').options.length;
				var textArr = new Array();

				for( var i=0; i<l; i++ ) {
					if( $('allTasks').options[i].selected ) {
						textArr.push( $('allTasks').options[i].text );
					}
				}
				text = textArr.join( '<br />' );
				$('info3').setHTML( text );
			}
		break;
		
		case 'b':
			if( $('addTasks').getValue().length != 0 ) {
				var l = $('addTasks').options.length;
				var textArr = new Array();

				for( var i=0; i<l; i++ ) {
					if( $('addTasks').options[i].selected ) {
						textArr.push( $('addTasks').options[i].text );
					}
				}
				text = textArr.join( '<br />' );
				$('info3').setHTML( text );
			}
		break;
		}
	}

	whichPM.addEvent('change', function() {
		
		var threadListUrl = 'index.php?option=com_arc_planner&view=ajax&scope=mentees&format=xml&Itemid=318';
		pos = 'right';
		new Ajax( threadListUrl, {
			'method': 'post',
			'update': menteeDiv,
			'data': 'list='+whichPM.value
		}).request();
	});

	document.addEvent('click', function(e) {
		check = 0;
		$('info1').style.color = 'black';
		$('info2').style.color = 'black';
		$('info3').style.color = 'black';
		$( loc ).style.color = 'black';
		$( loc ).setHTML( '' );

	});

	function conf1( msg1, loc ) {
		
			$('info1').style.color = 'red';
			$('info2').style.color = 'red';
			$('info3').style.color = 'red';
			$( loc ).style.color = 'red';
			$( loc ).setHTML( '<strong>'+msg1+'</strong>' );
			check = 1;
	}

	function conf2( msg2, loc ) {
			check = 0;
			$('info1').style.color = 'green';
			$('info2').style.color = 'green';
			$('info3').style.color = 'green';
			$( loc ).style.color = 'green';
			$( loc ).setHTML( '<strong>'+msg2+'</strong>' );		
	}


	//**** Selects/Divs ****
	
	//mentors (select - left, top)
	mentors.addEvent('click', function() {

		infoLeft( 't' );

		var threadListUrl;
		var name;
		
		if( whichPM.value == 'plr') {
			threadListUrl = 'index.php?option=com_arc_planner&view=ajax&scope=pupils&format=xml&Itemid=318';
		}
		else {
			threadListUrl = 'index.php?option=com_arc_planner&view=ajax&scope=staff&format=xml&Itemid=318';
			name = 'assignedMenteesStaff';
		}

		new Ajax( threadListUrl, {
			'method': 'post',
			'update': menteesDiv,
			'data': 'personId='+mentors.value+'&name='+name
		}).request();

		$('tasks1').setHTML('');
		$('assignedMentors').setHTML('');
		$('tasks2').setHTML('');

		if( ($('tasks2').getValue().length == 0) && ($('allTasks').getValue().length == 0) && ($('addTasks').getValue().length == 0) ) {
			$('info3').setHTML('');
		}

		$('assign').setStyle( 'visibility', 'visible' );
		$('removeMentees').setStyle( 'visibility', 'hidden' );
		$('removeMentors').setStyle( 'visibility', 'hidden' );
		$('removeTasksL').setStyle( 'visibility', 'hidden' );
		$('removeTasksR').setStyle( 'visibility', 'hidden' );
		$('add_tasks').setStyle( 'visibility', 'hidden' );

	});

	//assigned mentees  (select - left, middle)
	menteesDiv.addEvent('click', function() {

		infoLeft( 'c' );

		var threadListUrl = 'index.php?option=com_arc_planner&view=ajax&scope=tasks&format=xml&Itemid=318';

		var ser_mentors = ser.serialize( $('mentors').getValue() );
		var ser_assignedMentees = ser.serialize( $('assignedMentees').getValue() );
		new Ajax( threadListUrl, {
			'method': 'post',
			'update': tasksDiv,
			'data': 'assigneeIds='+ser_assignedMentees+'&adminIds='+ser_mentors+'&inputName=tasks1'
		}).request();

		var taskUrl = 'index.php?option=com_arc_planner&view=ajax&scope=allTasks&format=xml&Itemid=318';

		new Ajax( taskUrl, {
			'method': 'post',
			'update': addTasksDiv,
			'data': 'assigneeIds='+ser_assignedMentees+'&inputName=addTasks'
		}).request();


		var l = $('mentees').options.length;

		for( var i=0; i<l; i++ ) {
			if( $('mentees').options[i].selected ) {
				$('mentees').options[i].selected = false;
				$('assignedMentors').setHTML('');
				$('tasks2').setHTML('');
			}
		}

		var a = $('allTasks').options.length;

		for( var i=0; i<a; i++ ) {
			if( $('allTasks').options[i].selected ) {
				$('allTasks').options[i].selected = false;
				$('info3').setHTML('');
			}
		}

		if( $('tasks2').getValue().length == 0 ) {
			$('info3').setHTML('');
		}
				
		$('assign').setStyle( 'visibility', 'hidden' );
		$('removeMentees').setStyle( 'visibility', 'visible' );
		$('removeMentors').setStyle( 'visibility', 'hidden' );
		$('removeTasksL').setStyle( 'visibility', 'hidden' );
		$('removeTasksR').setStyle( 'visibility', 'hidden' );
		$('add_tasks').setStyle( 'visibility', 'hidden' );				
		
	});
	
	//assigned task (select - left, bottom)
	tasksDiv.addEvent('click', function() {

		infoLeft( 'b' );
		
		$('assign').setStyle( 'visibility', 'hidden' );
		$('removeMentees').setStyle( 'visibility', 'hidden' );
		$('removeMentors').setStyle( 'visibility', 'hidden' );
		$('removeTasksL').setStyle( 'visibility', 'visible' );
		$('removeTasksR').setStyle( 'visibility', 'hidden' );
		$('add_tasks').setStyle( 'visibility', 'hidden' );

	});

	//mentees (select - centre, top)
	menteeDiv.addEvent('click', function() {

		infoMiddle( 't' );

		var mentees = $('mentees');
		var ser_mentees = ser.serialize( $('mentees').getValue() );

		var threadListUrl = 'index.php?option=com_arc_planner&view=ajax&scope=staff&format=xml&Itemid=318';

		
		new Ajax( threadListUrl, {
			'method': 'post',
			'update': assignedMentorsDiv,
			'data': 'personId='+mentees.value
		}).request();

		var taskUrl = 'index.php?option=com_arc_planner&view=ajax&scope=allTasks&format=xml&Itemid=318';

		new Ajax( taskUrl, {
			'method': 'post',
			'update': addTasksDiv,
			'data': 'assigneeIds='+ser_mentees+'&inputName=addTasks'
		}).request();


		if( $('allTasks').getValue().length == 0 ) {
			$('info3').setHTML('');	
		}
			
		
		$('assignedMentees').setHTML('');
		$('tasks1').setHTML('');
		$('tasks2').setHTML('');
		
		$('assign').setStyle( 'visibility', 'visible' );
		$('removeMentees').setStyle( 'visibility', 'hidden' );
		$('removeMentors').setStyle( 'visibility', 'hidden' );
		$('removeTasksL').setStyle( 'visibility', 'hidden' );
		$('removeTasksR').setStyle( 'visibility', 'hidden' );
		$('add_tasks').setStyle( 'visibility', 'hidden' );

	});

	//assigned mentors (select - centre, middle)
	assignedMentorsDiv.addEvent('click', function() {

		infoMiddle( 'c' );

		var threadListUrl = 'index.php?option=com_arc_planner&view=ajax&scope=tasks&format=xml&Itemid=318';		
		
		var ser_mentees = ser.serialize( $('mentees').getValue() );
		
		var ser_assignedMentors = ser.serialize( $('assignedMentors').getValue() );

		new Ajax( threadListUrl, {
			'method': 'post',
			'update': tasksDiv2,
			'data': 'assigneeIds='+ser_mentees+'&adminIds='+ser_assignedMentors+'&inputName=tasks2'
		}).request();
		
		var l = $('mentors').options.length;

		for( var i=0; i<l; i++ ) {
			if( $('mentors').options[i].selected ) {
				$('mentors').options[i].selected = false;
				$('assignedMentees').setHTML('');
				$('tasks1').setHTML('');
			}
		}

		var a = $('allTasks').options.length;

		for( var i=0; i<a; i++ ) {
			if( $('allTasks').options[i].selected ) {
				$('allTasks').options[i].selected = false;
				$('info3').setHTML('');
			}
		}
		
		$('assign').setStyle( 'visibility', 'hidden' );
		$('removeMentees').setStyle( 'visibility', 'hidden' );
		$('removeMentors').setStyle( 'visibility', 'visible' );
		$('removeTasksL').setStyle( 'visibility', 'hidden' );
		$('removeTasksR').setStyle( 'visibility', 'hidden' );
		$('add_tasks').setStyle( 'visibility', 'hidden' );
	});	
	
	//assigned task (select - centre, bottom)
	tasksDiv2.addEvent('click', function() {

		infoMiddle( 'b' );

		$('assign').setStyle( 'visibility', 'hidden' );
		$('removeMentees').setStyle( 'visibility', 'hidden' );
		$('removeMentors').setStyle( 'visibility', 'hidden' );
		$('removeTasksL').setStyle( 'visibility', 'hidden' );
		$('removeTasksR').setStyle( 'visibility', 'visible' );
		$('add_tasks').setStyle( 'visibility', 'hidden' );

	});
	
	//all tasks (select - right, top)
	allTasks.addEvent('click', function() {
		infoRight( 't' );
		
		$('assignedMentors').setHTML('');
		$('assignedMentees').setHTML('');
		$('tasks1').setHTML('');
		$('tasks2').setHTML('');


		var l = $('addTasks').options.length;
		for( var i=0; i<l; i++ ) {
			if( $('addTasks').options[i].selected ) {
				$('addTasks').options[i].selected = false;
			}
		}
		
	});
		
	//add tasks (select - right, bottom)
	addTasksDiv.addEvent('click', function() {
		infoRight( 'b' );
		
		var l2 = $('tasks2').options.length;

		for( var i=0; i<l2; i++ ) {
			if( $('tasks2').options[i].selected ) {
				$('tasks2').options[i].selected = false;
			}
		}
		
		var l1 = $('tasks1').options.length;
		for( var i=0; i<l1; i++ ) {
			if( $('tasks1').options[i].selected ) {
				$('tasks1').options[is].selected = false;
			}
		}
		
		var l = $('allTasks').options.length;
		for( var i=0; i<l; i++ ) {
			if( $('allTasks').options[i].selected ) {
				$('allTasks').options[i].selected = false;
			}
		}

		$('assign').setStyle( 'visibility', 'hidden' );
		$('removeMentees').setStyle( 'visibility', 'hidden' );
		$('removeMentors').setStyle( 'visibility', 'hidden' );
		$('removeTasksL').setStyle( 'visibility', 'hidden' );
		$('removeTasksR').setStyle( 'visibility', 'hidden' );
		$('add_tasks').setStyle( 'visibility', 'visible' );

	});


	//**** Buttons ****

	//assign mentors/mentees (button - left/centre, top)
	assign.addEvent('click', function(e) {
		
		new Event(e).stop();
		
		var threadListUrl = 'index.php?option=com_arc_planner&view=ajax&scope=assignPeople&format=xml&Itemid=318';		
		var ser_allTasks = ser.serialize( allTasks.getValue() );
		var ser_mentees = ser.serialize( $('mentees').getValue() );
		var ser_mentors = ser.serialize( $('mentors').getValue() );

		if( check == 0 ) {
			conf1( msg1 = 'Assign?', loc = 'confirm1' );
		}

		else if( check == 1 ) {
			conf2( msg2 = 'Assigned', loc = 'confirm1' );
			reset = 1;

			new Ajax( threadListUrl, {
				'method': 'post',
				'update': menteesDiv,
				'data': 'mentees='+ser_mentees+'&taskIds='+ser_allTasks+'&mentors='+ser_mentors+'&inputName=assign&list='+whichPM.value
			}).request();
		}
		
	});

	//removeMentees (button - left, middle)
	removeMentees.addEvent('click', function(e) {

		new Event(e).stop();
		
		var threadListUrl = 'index.php?option=com_arc_planner&view=ajax&scope=removeMentees&format=xml&Itemid=318';		
		var ser_assignedMentees = ser.serialize( $('assignedMentees').getValue() );

		if( check == 0 ) {
			conf1( msg1 = 'Remove?', loc );
		}

		else if( check == 1 ) {
			conf2( msg2 = 'Removed', loc );
			reset = 1;

			new Ajax( threadListUrl, {
				'method': 'post',
				'update': menteesDiv,
				'data': 'assigneeIds='+ser_assignedMentees+'&adminIds[]='+mentors.getValue()+'&inputName=assignedMentees'
			}).request();
		}
	});

	//remove assigned tasks (button - left, bottom)
	removeTasksL.addEvent('click', function(e) {

		new Event(e).stop();

		var threadListUrl = 'index.php?option=com_arc_planner&view=ajax&scope=removeTasks&format=xml&Itemid=318';
		var ser_allTasks = ser.serialize( $('tasks1').getValue() );
		var ser_assignedMentees = ser.serialize( $('assignedMentees').getValue() );
		var ser_mentors = ser.serialize( $('mentors').getValue() );

		if( check == 0 ) {
			conf1( msg1 = 'Remove?', loc = 'confirm2' );
		}

		else if( check == 1 ) {
			conf2( msg2 = 'Removed', loc = 'confirm2' );
			reset = 1;

			new Ajax( threadListUrl, {
				'method': 'post',
				'update': tasksDiv,
				'data': 'assigneeIds='+ser_assignedMentees+'&taskIds='+ser_allTasks+'&adminIds='+ser_mentors+'&inputName=tasks1'
			}).request();
		}
		
	});

	//removeMentors (button - centre, middle)
	removeMentors.addEvent('click', function(e) {

		new Event(e).stop();

		var threadListUrl = 'index.php?option=com_arc_planner&view=ajax&scope=removeMentors&format=xml&Itemid=318';		
		var ser_mentees = ser.serialize( $('mentees').getValue() );
		var ser_assignedMentors = ser.serialize( $('assignedMentors').getValue() );

		if( check == 0 ) {
			conf1( msg1 = 'Remove?', loc );
		}

		else if( check == 1 ) {
			conf2( msg2 = 'Removed', loc );
			reset = 1;

			new Ajax( threadListUrl, {
				'method': 'post',
				'update': assignedMentorsDiv,
				'data': 'assigneeIds='+ser_mentees+'&adminIds='+ser_assignedMentors+'&inputName=assignedMentors'
			}).request();
		}
	});

	//removeMentors (button - centre, bottom)
	removeTasksR.addEvent('click', function(e) {

		new Event(e).stop();

		var threadListUrl = 'index.php?option=com_arc_planner&view=ajax&scope=removeTasks&format=xml&Itemid=318';
		var ser_assignedMentors = ser.serialize( $('assignedMentors').getValue() );
		var ser_mentees = ser.serialize( $('mentees').getValue() );		
		var ser_allTasks = ser.serialize( $('tasks2').getValue() );

		if( check == 0 ) {
			conf1( msg1 = 'Remove?', loc = 'confirm2' );
		}

		else if( check == 1 ) {
			conf2( msg2 = 'Removed', loc = 'confirm2' );
			reset = 1;

			new Ajax( threadListUrl, {
				'method': 'post',
				'update': tasksDiv2,
				'data': 'assigneeIds='+ser_mentees+'&taskIds='+ser_allTasks+'&adminIds='+ser_assignedMentors+'&inputName=tasks2'
			}).request();
		}
	});

	//add tasks (button - right, bottom)
	add_tasks.addEvent('click', function(e) {

		new Event(e).stop();

		var threadListUrl = 'index.php?option=com_arc_planner&view=ajax&scope=addTasks&format=xml&Itemid=318';		
		var ser_addTasks = ser.serialize( $('addTasks').getValue() );
		var tmpUpdate;
		var ser_mentees;
		var tmpMentors = '';

		//lhs
		if ( $('assignedMentees').getValue().length != 0 ) {
			ser_mentees = ser.serialize( $('assignedMentees').getValue() );
			tmpMentors =  ser.serialize( $('mentors').getValue() );
			tmpUpdate = tasksDiv;
		}
		
		//rhs
		else {
			ser_mentees = ser.serialize( $('mentees').getValue() );
			tmpMentors =  ser.serialize( $('assignedMentors').getValue() );			
			tmpUpdate = tasksDiv2;
		}

		if( check == 0 ) {
			conf1( msg1 = 'Add?', loc = 'confirm2' );
		}
		
		else if( check == 1 ) {
			conf2( msg2 = 'Added', loc = 'confirm2' );
			reset = 1;

			new Ajax( threadListUrl, {
				'method': 'post',
				'update': tmpUpdate,
				'data': 'mentees='+ser_mentees+'&taskIds='+ser_addTasks+'&mentors='+tmpMentors+'&inputName=tasks1'
			}).request();
		}

	});
	
});
