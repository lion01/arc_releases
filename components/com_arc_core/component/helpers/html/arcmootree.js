/**
 * @package     Arc
 * @subpackage  Core
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

var ArcMooTreeControl = MooTreeControl.extend({
	initialize: function( options, rootNode, holder, unselect, partGroups ) {
		this.parent( options, rootNode );
		this.holder = holder;
		this.unselect = unselect;
		this.partGroups = partGroups;
		this.ser = new PHP_Serializer();
		
		if( this.holder.value == '' ) {
			this.values = new Array();
		}
		else {
			this.values = this.ser.unserialize( this.holder.value );
		}
		
		if( this.partGroups.value == '' ) {
			this.partGroupsNodes = new Array();
		}
		else {
			this.partGroupsNodes = this.ser.unserialize( this.partGroups.value );
		}
		
		this.unselect.addEvent( 'click', function() {
			this._toggleNode( this.root, 1, false );
			this._toggleNode( this.root, 0, false );
			this.values = new Array();
			this.holder.value = this.ser.serialize( this.values );
			this.unselect.value = 'Unselect ( ' + this.values.length + ' selected )';
		}.bind(this) );
	},
	
	/**
	 * Toggles the selected status of a node using _apothToggleNode,
	 * @param node object  The node who's status is being toggled
	 * @param parent boolean  If this is true we update parents
	 * @param children boolean  If this is true we update children and parents
	 * @param onVal boolean  The value to set this node's selected status to.
	 *                       If omitted, the inverse of the current value is used, or true if no value already set.
	 */
	toggleNode: function( node, parent, children, onVal )
	{
		// change the node
		this._toggleNode( node, 0, onVal );
		
		// change the node's children(1)
		if( children ) {
			this._toggleNode( node, 1, node.apothOn );
			this._toggleNode( node, 0, node.apothOn );
		}
		
		// change the node's parent(2)
		if( parent ) {
			this._toggleNode( node, 2 );
		}
		
		this.holder.value = this.ser.serialize( this.values );
		this.unselect.value = 'Unselect ( ' + this.values.length + ' selected )';
	},
	
	/**
	 * Toggles the selected status of a node
	 * @param node object  The node who's status is being toggled
	 * @param recursive int  If this is 0 we update the node, if this is 1 we update children, if it's 2 we update the parents
	 * @param onVal boolean  The value to set this node's selected status to.
	 *                       If omitted, the inverse of the current value is used, or true if no value already set.
	 */
	_toggleNode: function( node, recursive, onVal )
	{
		if( node.apothOn === undefined ) node.apothOn = false;
		
		if( ((recursive == 0) || (recursive == false)) && node.id ) {
			node.apothOn = ( (onVal === undefined) ? !node.apothOn : onVal );
			if( node.color == 'fuchsia' ) {
				node.preColor = node.color;
			}
			node.color = ( node.apothOn ? 'Green' : (node.preColor === undefined ? 'black' : node.preColor) );
			node.icon  = ( node.apothOn ? (this.theme + '#11') : null );
			node.update( false );
			
			if( node.apothOn == true ) {
				this.values.include( node.id );
			}
			else {
				this.values.remove( node.id );
			}
			this._setPartOn( node );
		}
		else if( recursive == 1 ) {
			node.nodes.each( function(inode) {
				this._toggleNode( inode, recursive, onVal );
				this._toggleNode( inode, 0, onVal );
			}, this );
		}
		else if( (recursive == 2) && (node.parent != null) ) {
			this._toggleNode( node.parent, 0, ((node.apothOn == true) && (node.parent.apothOn == true)) );
			this._toggleNode( node.parent, recursive );
		}
	},
	
	/**
	 * Sets the newly expanded children to assume either on or partial status
	 */
	setDefault: function( node )
	{
		var setDefFunc = function() {
			this._setPartOn( node );
			if( node.nodes[0].id != null ) {
				node.nodes.each( function(jnode) {
					if( this.values.contains(jnode.id) ) {
						this._toggleNode( jnode, 0, true );
					}
					this._displayPartOn( jnode );
				}, this );
				window.clearInterval( timerID );
				this.holder.value = this.ser.serialize( this.values );
				this.unselect.value = 'Unselect ( ' + this.values.length + ' selected )';
			}
		};
		
		var timerID = window.setInterval( setDefFunc.bind(this), 200 );
	},
	
	/**
	 * Sets the newly expanded children of a node to share that node's selected status
	 */
	setDefaults: function( node )
	{
		var setDefFunc = function() {
			this._setPartOn( node );
			if( (typeof node.nodes[0] != 'undefined') && (node.nodes[0].id != null) ) {
				node.nodes.each( function(jnode) {
					if( this.values.contains(jnode.id) || node.apothOn ) {
						this._toggleNode( jnode, 0, true );
					}
					this._displayPartOn( jnode );
				}, this );
				window.clearInterval( timerID );
				this.holder.value = this.ser.serialize( this.values );
				this.unselect.value = 'Unselect ( ' + this.values.length + ' selected )';
			}
		};
		
		var timerID = window.setInterval( setDefFunc.bind(this), 200 );
	},
	
	/**
	 * Determines if nodes are partially selected
	 * @param node object  The node who's partially selected status is being determined
	 */
	_setPartOn: function( node )
	{
		var allChildrenOff = true;
		
		node.nodes.each( function(cnode) {
			if( allChildrenOff ) {
				allChildrenOff = !( (cnode.apothOn == true) || this.values.contains(cnode.id) || this.partGroupsNodes.contains(cnode.id) );
			}
		}, this );
		
		if( allChildrenOff || node.apothOn ) {
			this.partGroupsNodes.remove( node.id );
		}
		else {
			this.partGroupsNodes.include( node.id );
		}
		
		this._displayPartOn( node );
		this.partGroups.value = this.ser.serialize( this.partGroupsNodes );
	},
	
	/**
	 * Sets nodes to display their partially selected status
	 * @param node object  The node who's partially selected status is being tested
	 */
	_displayPartOn: function( node )
	{
		if( this.partGroupsNodes.contains(node.id) ) {
			node.icon  = ( this.theme + '#15' );
		}
		
		node.update( false );
	},
	
	/**
	 * Perform a one off check on first page load to see if the root node itself is checked
	 */
	checkRootNode: function()
	{
		if( this.values.contains(this.root.id) ) {
			this.root.apothOn = true;
			this.root.icon = ( this.theme + '#11' );
			this.root.update( false );
		}
	}
});