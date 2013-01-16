<?php
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

defined( '_JEXEC' ) or die( 'Restricted access' );
	
/**
 * Repository of library function common to both the
 * admin and component sides of the Apotheosis core component
 *
 * @author     lightinthedark <code@lightinthedark.org.uk>
 * @package    Apotheosis
 * @subpackage Core
 * @since 0.1
 */
class ApotheosisLibArray
{
	// #############  Arrays (functions not already provided by JArrayHelper  ############
	
	/**
	 * Counts the elements of a multi-dimensional array using recursion.
	 * 
	 * @param array $array  The multi-dimensional array to be counted
	 * @param boolean $leavesOnly  Should we count only the leaves (not every node)?
	 * @return int  The count of elements
	 */
	function countMulti( &$array, $leavesOnly = true )
	{
		$count = 0;
		foreach ($array as $v) {
			if (is_array($v)) {
				$count += ApotheosisLibParent::countMulti( $array );
				if (!$leavesOnly) {
					$count++;
				}
			}
			else {
				$count++;
			}
		}
		return $count;
	}
	
	/**
	 * Calculates the weighted average of an array of values
	 * Weights are matched to values by array index / key
	 *
	 * @param array $values  The values to be averaged
	 * @param array $weights  The weights to apply to the values. Any values without a corresponding weight 
	 *                        (which will be all if weights is omitted) will be given weight 1.
	 * @return float  The weighted average of the values given
	 */
	function weightedAverage( $values, $weights = array() )
	{
		$wTotal = 0;
		$vTotal = 0;
		foreach( $values as $k=>$v ) {
			$w = ( isset($weights[$k]) ? $weights[$k] : 1 );
			$wTotal += $w;
			$vTotal += ($w * $v);
		}
		if( $wTotal == 0 ) {
			return 0;
		}
		else {
			return ($vTotal / $wTotal);
		}
	}

	/**
	 * Utility function to sort an array of objects on one or more given field(s)
	 * Based on JArrayHelper::sortObjects, but can take an array for second parameter
	 *
	 * @param array $a          An array of objects
	 * @param mixed $k          The key(s) to sort on
	 * @param int   $direction  Direction to sort in [1 = Ascending] [-1 = Descending]. Other values are replaced with 1
	 * @param bool  $assoc      Indicator of if associative keys should be preserved
	 * @return array  The sorted array of objects
	 */
	function sortObjects( &$a, $k, $directions = 1, $assoc=false )
	{
		if( !is_array($a) ) {
			return $a;
		}
		if( !is_array($k) ) {
			$k = array($k);
		}
		if( !is_array($directions) ) {
			$directVal = $directions;
			$directions = array(key($k)=>$directions);
		}
		foreach( $directions as $key=>$val ) {
			if( ($val !== 1) && ($val !== -1) ) {
				$directions[$key] = 1;
			}
		}
		
		$diff = array_diff(array_keys($k), array_keys($directions));
		if( !empty($diff) ) {
			$default = end($directions);
			foreach($diff as $key=>$value) {
				$directions[$key] = $default;
			}
		}
		
		$GLOBALS['APOTH_so'] = array(
			'keys'		=> $k,
			'directions'	=> $directions
		);
		$assoc ?
			uasort( $a, array('ApotheosisLibArray', '_sortObjects') ) :
			usort(  $a, array('ApotheosisLibArray', '_sortObjects') ) ;
		unset( $GLOBALS['APOTH_so'] );
		
		return $a;
	}

	/**
	 * Private callback function for sorting an array of objects on a key
	 *
	 * @since	1.5
	 * @see		JArrayHelper::sortObjects()
	 * 
	 * @static
	 * @param array $a  An array of objects
	 * @param array $b  An array of objects
	 * @return int  Comparison status
	 */
	function _sortObjects( &$a, &$b )
	{
		$params = $GLOBALS['APOTH_so'];
		$retVal = 0;
		$k = reset($params['keys']);
		do {
			$index = key($params['keys']);
			if ( $a->$k > $b->$k ) {
				$retVal = $params['directions'][$index];
			}
			elseif ( $a->$k < $b->$k ) {
				$retVal = -1 * $params['directions'][$index];
			}
		} while (($retVal == 0) && (($k = next($params['keys'])) !== false) );
		return $retVal;
	}
	
	
	/**
	 * Sorts a flat array which holds tree data so elements are as: ( 1, 1.1, 1.2, 1.2.1, 1.2.2, 1.3, 1.3.1, 2, 2.1, ... )
	 * Users ApotheosisLibArray::sortObjects
	 * 
	 * @param array	 $items      An array of objects
	 * @param string $idk        The property which holds the id
	 * @param int    $parentId   The id of the parent element
	 * @param string $pk         The property which holds the parent id
	 * @param mixed	 $k          The key(s) to sort on
	 * @param int    $direction  Direction to sort in [1 = Ascending] [-1 = Descending]. Other values are replaced with 1
	 * @param bool   $assoc      Indicator of if associative keys should be preserved
	 * @return array  The sorted array of objects
	 */
	function sortTree( &$items, $idk, $parentId, $pk, $k, $direction, $assoc, $fresh = true  )
	{
		static $processed = array();
		if( $fresh ) {
			$processed = array();
		}
		if( isset($processed[$parentId]) ) {
			return array();
		}
		else {
			$processed[$parentId] = $parentId;
		}
		
		$children = array();
		$result = array();
		
		foreach( $items as $key=>$i ) {
			if( ($i->$pk == $parentId) && ($i->$pk != $i->$idk) ) {
				$children[$key] = $i;
			}
		}
		
		if( !empty($children) ) {
			ApotheosisLibArray::sortObjects( $children, $k, $direction, $assoc );
			
			$lastId = null;
			$item = reset($children);
			$key = key($children);
			while( $item !== false ) {
				$id = $item->$idk;
				$result += array( $key=>$item );
				
				$item = next($children);
				$key = key($children);
				
				if( ($item === false) || ($id != $item->$idk) ) {
					$result += ApotheosisLibArray::sortTree( $items, $idk, $id, $pk, $k, $direction, $assoc, false );
				}
			}
		}
		
		if( $fresh ) {
			foreach( $items as $key=>$i ) {
				if( is_null($i->$idk) ) {
					$result[] = $i;
				}
			}
		}
		return $result;
	}

	/**
	 * Adjustment of enhancement of built in php function array_search to allow for searching for objects
	 * 
	 * @param mixed $needle  What are you searching for?
	 *                       (if this is an object and strict is false, then a match is recorded for an object in haystack
	 *                        if all the needle's properties match those of the haystack object
	 *                        regardless of if the haystack object has more properties)
	 * @param array $haystack  What are you searching in?
	 * @param bool $strict  Do you want type comparisons?
	 * @param int $limit  how many results to return (defaults to -1, unlimited)
	 * @param string|array $sortedOn  What (if any) properties is the haystack sorted on
	 * @return mixed  Array of all indexes for matching items, or false if none found.
	 */
	function array_search_partial( &$needle, &$haystack, $strict = false, $limit = -1, $sortedOn = false, $keys = false )
	{
		if( $keys === false ) {
			$keys = array_keys($haystack);
		}
		if( $sortedOn !== false ) {
			if( !is_array($sortedOn) ) {
				$sortedOn = array($sortedOn);
			}
			$keys = ApotheosisLibArray::_array_search_reduce_range( $needle, $haystack, $keys, $sortedOn );
		}
//		echo 'searching for ';var_dump_pre($needle);
//		echo 'in things like: ';var_dump_pre(reset($haystack));
		
		if(!is_array($haystack)) { return NULL; }
		$results = array();
		if (is_object($needle)) {
			$properties = get_object_vars($needle);
		}
		if( $limit == -1 ) {
			end($keys);
			$limit = key($keys); // count was too slow
		}
		$numFound = 0;
		foreach( $keys as $k ) {
			if (is_object($needle)) {
				$match = true;
				foreach ($properties as $pk=>$pv) {
					if ((!$strict && ($pv != $haystack[$k]->$pk)) || ($strict && ($pv !== $haystack[$k]->$pk))) {
						$match = false;
						break;
					}
				}
				if ($match) {
					$results[] = $k;
					$numFound++;
				}
			}
			else {
				if ( ($strict && $needle===$haystack[$k]) || (!$strict && $needle==$haystack[$k]) ) {
				  $results[] = $k;
					$numFound++;
				}
			}
			if( $numFound >= $limit ) {
				break;
			}
		}
		
//		var_dump_pre($results, 'results');
		return $results == array() ? false : $results;
	}
	
	/**
	 * Cut a sorted array down to only include entries that match the provided criteria
	 */
	function _array_search_reduce_range( $needle, &$haystack, $keys, $sortedOn )
	{
		$prop = array_shift($sortedOn);
		if( isset($needle->$prop) ) {
			$start = ApotheosisLibArray::array_search_sorted( $needle, $haystack, $prop, 1, $keys );
			$end = ApotheosisLibArray::array_search_sorted( $needle, $haystack, $prop, -1, $keys, $start );
			$dif = $end - $start;
			$retVal = array_slice( $keys, $start, ($dif + 1) );
			if( !empty($sortedOn) ) {
				$retVal = ApotheosisLibArray::_array_search_reduce_range( $needle, $haystack, $retVal, $sortedOn );
			}
			return $retVal;
		}
		else {
			return $keys;
		}
	}
	
	/**
	 * Find the offset of an occurance of an object with the given property value in the haystack
	 * array_search_partial uses this to reduce its search space if it is given a sorted array
	 * @param int $order: 1 = find first in list
	 *                    0 = find any (returns first occurence it finds with binary search)
	 *                   -1 = find last in list
	 * @return int  The offset of the item in the array. To make sense of this use array_keys, then $array[$keys[$retVal]]
	 */
	function array_search_sorted( &$needle, &$haystack, $property, $order, &$keys, $low = false, $high = false)
	{
		if( $low === false ) {
			$low = 0;
		}
		if( $high === false ) {
			end($keys);
			$high = key($keys); // count was too slow
		}
		
		$numKeys = ($high - $low) + 1;
		
		if( $numKeys > 2 ) {
			$mid = $low + floor( $numKeys / 2 );
			$hp = &$haystack[$keys[$mid]]->$property;
			
			if( ($hp == $needle->$property) && ($order == 0) ) {
				return $mid;
			}
			elseif( ($hp > $needle->$property)
			 || (($hp == $needle->$property) && ($order == 1)) ) {
				return ApotheosisLibArray::array_search_sorted( $needle, $haystack, $property, $order, $keys, $low, $mid );
			}
			else {
				return ApotheosisLibArray::array_search_sorted( $needle, $haystack, $property, $order, $keys, $mid, $high );
			}
		}
		elseif( $numKeys == 2 ) {
			if( $order == 1 ) {
				$key = $keys[$low];
				return ( ($haystack[$key]->$property == $needle->$property) ? $low : $high );
			}
			else {
				$key = $keys[$high];
				return ( ($haystack[$key]->$property == $needle->$property) ? $high : $low );
			}
		}
		else {
			return $low;
		}
	}
	
	
	/**
	 * Return the value of a deeply nested array element or object property
	 * 
	 * @param mixed $item  Array or object haystack to deep search
	 * @param mixed $properties  String or array of strings of array element or
	 *                           object property needles to deep search on
	 */
	function deepProperty( &$item, $properties ) {
		if( !is_array($properties) ) {
			$properties = array( $properties );
		}
		
		$property = array_shift( $properties );
		if( !is_null($property) ) {
			if( is_object($item) ) {
				return ApotheosisLibArray::deepProperty( $item->$property, $properties );
			}
			elseif( is_array($item) ) {
				return ApotheosisLibArray::deepProperty( $item[$property], $properties );
			}
		}
		else {
			return $item;
		}
	}
	
}
?>