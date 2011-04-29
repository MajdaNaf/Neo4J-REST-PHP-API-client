<?php
/**
 * Provide a list of descriptions of Relationships.
 * 
 * @package Neo4jRest
 */

namespace Neo4jRest;

class RelationshipDescription {
	
	private  $_descriptions;
	
	function __construct( $type, $direction=null ) {
		if ( $direction ) {
			$this->_descriptions[] = array( 'type' => $type, 
				'direction' => $direction );
		} else {
			$this->_descriptions[] = array( 'type' => $type );
		}		
	}
	
	function add( $type, $direction=null ) {
		if ( $direction ) {
			$this->_descriptions[] = array( 'type' => $type, 'direction' => $direction );
		} else {
			$this->_descriptions[] = array( 'type' => $type );
		}		
	}
	
	function get()
	{
		return $this->_descriptions;
	}
}