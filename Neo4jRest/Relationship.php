<?php
/**
 * Relationship in the graph
 *
 * @package Neo4jRest
 */

namespace Neo4jRest;

class Relationship extends PropertyContainer
{
	const DIRECTION_BOTH 	= 'all';
	const DIRECTION_IN 		= 'in';
	const DIRECTION_OUT 	= 'out';
	
	public $_is_new;
	public $_neo_db;
	public $_id;
	public $_type;
	public $_node1;
	public $_node2;
	
	public function __construct(GraphDatabaseService $neo4jDb, Node $startNode, 
	    Node $endNode, $type)
	{
	    
	    $typeType = gettype($type);
	    if ($typeType != 'string') {
	        throw new Neo4jRest_InvalidParameterTypeException(
	            'Parameter "type" should be type "string". Found type "' .
	             $typeType . '" instead.');
	    }
	    
		 if (empty($type)) {
	        throw new Neo4jRest_RequiredParameterException(
	            'Parameter "type" required and not found.');
	    }	    
		 $this->_neo_db = $neo4jDb;
		 $this->_is_new = TRUE;
		 $this->_type = $type;
		 $this->_node1 = $startNode;
		 $this->_node2 = $endNode;
	}
	
	public function getId()
	{
		return $this->_id;
	}
	
	public function isSaved()
	{
		return !$this->_is_new;
	}
	
	public function getType()
	{
		return $this->_type;		
	}
	
	public function isType($type)
	{
		return $this->_type==$type;
	}
	
	public function getStartNode()
	{
		return $this->_node1;
	}
	
	public function getEndNode()
	{
		return $this->_node2;
	}
	
	public function getOtherNode(Node $node)
	{
	    if ($this->_node1->getId() == $node->getId()) {
	        return $this->_node2;
	    }
	    else if ($this->_node2->getId() == $node->getId()) {
	        return $this->_node1;
	    } 
	    else {
	        throw new Neo4jRest_NotFoundException('Supplied node is not ' . 
	            'a the start node or end node of this Relationship');
	    }
	}
	
	public function save()
	{
		if ($this->_is_new) {
			$payload = array(
				'to' => $this->getEndNode()->getUri(),
				'type' => $this->_type,
				'data'=>$this->_data
			);
			
			list($response, $http_code) = HttpHelper::jsonPostRequest($this->getUri(), $payload);
			
			if ($http_code!=201) throw new NeoRestHttpException($http_code);
		} else {
			list($response, $http_code) = HttpHelper::jsonPutRequest($this->getUri().'/properties', $this->_data);
			if ($http_code!=204) throw new NeoRestHttpException($http_code);
		}
				
		if ($this->_is_new) 
		{
			$this->_id = end(explode("/", $response['self']));
			$this->_is_new=FALSE;
		}
	}
	
	public function delete()
	{
		if (!$this->_is_new) 
		{
			list($response, $http_code) = HttpHelper::deleteRequest($this->getUri());

			if ($http_code!=204) throw new Neo4jRest_HttpException($http_code);
			
			$this->_id = NULL;
			$this->_is_new = TRUE;
		}
	}
	
	public function getUri()
	{
		if ($this->_is_new)
			$uri = $this->getStartNode()->getUri().'/relationships';
		else
			$uri  = $this->_neo_db->getBaseUri().'relationship/'.$this->getId();
	
		//if (!$this->_is_new) $uri .= '/'.$this->getId();
	
		return $uri;
	}
	
	public static function inflateFromResponse(GraphDatabaseService $neo_db, $response)
	{
		$start_id = end(explode("/", $response['start']));
		$end_id = end(explode("/", $response['end']));

		$start = $neo_db->getNodeById($start_id);
		$end = $neo_db->getNodeById($end_id);
		
		$relationship = new Relationship($neo_db, $start, $end, 
		    $response['type']);
		$relationship->_is_new = FALSE;
		$relationship->_id = end(explode("/", $response['self']));
		
		if(!empty($response['data'])) {
		    $relationship->setProperties($response['data']);
		}
		
		return $relationship;
	}
}
