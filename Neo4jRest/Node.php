<?php
/**
 * Node in the graph.
 *
 * @package Neo4jRest
 */

namespace Neo4jRest;

class Node extends PropertyContainer
{
	public $_neo_db;
	public $_id;
	public $_is_new;
		
	public function __construct(GraphDatabaseService $neo_db)
	{
		$this->_neo_db = $neo_db;
		$this->_is_new = TRUE;
	}
	
	public function delete()
	{
		if (!$this->_is_new) 
		{
			list($response, $http_code) = 
			    HttpHelper::deleteRequest($this->getUri());
			
			if ($http_code!=204) throw new Neo4jRest_HttpException($http_code);
			
			$this->_id = NULL;
			$this->_is_new = TRUE;
		}
	}
	
	public function save()
	{
		if ($this->_is_new) {
			list($response, $http_code) = HttpHelper::jsonPostRequest(
			    $this->getUri(), $this->_data);
			if ($http_code!=201) throw new Neo4jRest_HttpException($http_code);
		} else {
			list($response, $http_code) = HttpHelper::jsonPutRequest(
			    $this->getUri().'/properties', $this->_data);
			if ($http_code!=204) throw new Neo4jRest_HttpException($http_code);
		}

		if ($this->_is_new) 
		{
			$this->_id = end(explode("/", $response['self']));
			$this->_is_new=FALSE;
		}
	}
	
	public function getId()
	{
		return $this->_id;
	}
	
	public function isSaved()
	{
		return !$this->_is_new;
	}
	
	public function getRelationships($direction=Relationship::DIRECTION_BOTH, $types=NULL)
	{
	    
	   if (!$this->isSaved()) {
	       return array();
	   }
		$uri = $this->getUri().'/relationships';
		
		$uri .= '/' . $direction;
		
		if ($types)
		{
			if (is_array($types)) $types = implode("&", $types);
			
			$uri .= '/'.$types;
		}
		
		list($response, $http_code) = HttpHelper::jsonGetRequest($uri);
		
		$relationships = array();
		
		foreach($response as $result)
		{
			$relationships[] = Relationship::inflateFromResponse($this->_neo_db, $result);
		}
		
		return $relationships;
	}
	
	public function createRelationshipTo($node, $type)
	{
		$relationship = new Relationship($this->_neo_db, $this, $node, $type);
		return $relationship;
	}
	
	public function getUri()
	{
		$uri = $this->_neo_db->getBaseUri().'node';
	
		if (!$this->_is_new) $uri .= '/'.$this->getId();
	
		return $uri;
	}
	
	public static function inflateFromResponse(GraphDatabaseService $neo_db, $response)
	{
		$node = new Node($neo_db);
		$node->_is_new = FALSE;
		$node->_id = end(explode("/", $response['self']));
		
		if(!empty($response['data'])) {
		    $node->setProperties($response['data']);
	   }

		return $node;
	}
	
	public function findPaths(Node $toNode, $maxDepth=null, 
	   RelationshipDescription $relationships=null, 
	   $algorithm='allSimplePaths')
	{
	   $pathFinderData = array();
		
		$pathFinderData['to'] = $this->_neo_db->getBaseUri().'node'.
			'/'.$toNode->getId();
		
		if ($maxDepth) { 
		    $pathFinderData['max depth'] = $maxDepth;
		}
		
		if ($relationships) {
		    $pathFinderData['relationships'] = $relationships->get();
		}

		list($response, $http_code) = HTTPHelper::jsonPostRequest(
		    $this->getUri().'/paths', $pathFinderData);

		// TODO: Uncomment this out when the REST API works per the docs.
/*		
		if ($http_code==204) {
		    throw new Neo4jRest_NotFoundException(
		        "http code: " . $http_code . ", response: " . 
		        print_r($response, true), $http_code);
		}
*/		
		
		if ($http_code!=200) {
		    throw new Neo4jRest_HttpException("http code: " . 
		        $http_code . ", response: " . print_r($response, true), 
		        $http_code
		    );
		}
		
		$paths = array();
		foreach($response as $result)
		{
				$paths[] = Path::inflateFromResponse($this->_neo_db, $result);	
		}
		
		// TODO: Needed?  Shouldn't we get exception from above?
		//    Seems like no, but this means REST API docs are broken.
		if (empty($paths)) {
			throw new Neo4jRest_NotFoundException("Paths array was empty.", 0);
		}
		
		return $paths;
	}	

	/**
	 * Returns a single path connecting this node and the toNode.
	 *  
	 * @param Neo4jRest\Node $toNode
	 * @param integer $maxDepth
	 * @param Neo4jRest\RelationshipDescription $relationships
	 * @param string $algorithm
	 * 
	 * @return Neo4jRest\Path
	 * 
	 */
	public function findPath(Node $toNode, $maxDepth=null, 
	    RelationshipDescription $relationships=null, 
	    $algorithm='allSimplePaths')
	{
	   $pathFinderData = array();	    
		
		$pathFinderData['to'] = $this->_neo_db->getBaseUri() . 'node' . '/' .
		    $toNode->getId();
		if ($maxDepth) { 
		    $pathFinderData['max depth'] = $maxDepth;
		}
		
		if ($relationships) { 
		    $pathFinderData['relationships'] = $relationships->get(); 
		}
		    
		list($response, $http_code) = HTTPHelper::jsonPostRequest(
		    $this->getUri().'/path', $pathFinderData);
		
		if ($http_code==404) {
		    throw new Neo4jRest_NotFoundException("http code: " . 
		        $http_code . ", response: " . print_r($response, true), 
		        $http_code
		    );
		}
		if ($http_code!=200) {
		    throw new Neo4jRest_HttpException("http code: " . 
		        $http_code . ", response: " . print_r($response, true), 
		        $http_code
		    );
		}
		
	   $path = Path::inflateFromResponse($this->_neo_db, $response);
		
	   return $path;
		
	}
	
	/**
	 * 
	 * Visits other nodes, starting from this node and based on traversal rules.
	 * 
	 * Modeled directly from the neo4j java API so please see those docs for 
	 * more information
	 * 
	 * @param string $traversalOrder Use the constants defined in the 
	 * 	TraverserOrder object.  E.g. TraverserOrder::DEPTH_FIRST
	 * @param string $stopEvaluator javascript that determines if the traversal
	 * 	should stop at this Node.
	 * @param string $returnFilter determines which nodes should be included in
	 * 	the returned list.  Use the TraverserReturnFilter object for 
	 *    the valid string values.
	 * @param RelationshipDescription $relationshipTypesandDirections a list of 
	 * 	relationship type and direction pairs used to determine which edges 
	 *    to follow.
	 * @param integer $maxdepth A shorthand way of specifying a stop evaluator
	 * 	which stops after a certain depth.
	 * @param string $returnType either 'node', 'relationship', or 'path'.
	 * @param string $uniquness 
         * 
	 * @return array depends on the $returnType parameters.  Either a list of
	 * 	nodes, relationships, or paths.
	 * 
	 */
    function traverse($traversalOrder=TraverserOrder::DEPTH_FIRST, 
        $maxDepth=null, 
        $stopEvaluator=null, 
	     $returnFilter=null, 
	     RelationshipDescription $relationshipTypesAndDirections=null,
	     $returnType='node',
             $uniqueness=TraverserUniquenessFilter::NONE) {
	        	        	        
        $data['order'] = $traversalOrder;
        
        if ($relationshipTypesAndDirections) {
           $data['relationships'] = $relationshipTypesAndDirections->get();
        }
        
        if ($stopEvaluator) {
           $data['prune_evaluator'] = array( 'language' => 'javascript',
               'body' => $stopEvaluator);
        }
        
        if ($returnFilter) {
           $data['return_filter'] = array( 'language' => 'builtin', 
               'name' => $returnFilter);
        }
        
        if ($maxDepth) { 
            $data['max_depth'] = $maxDepth;
        }
        
        $data['uniqueness'] = $uniqueness;
        
        $uri = $this->getUri().'/traverse/' . $returnType;
      
        list($response, $http_code) = HTTPHelper::jsonPostRequest(
            $uri, $data);

        if ($http_code!=200) {
            throw new Neo4jRest_HttpException("http code: " . 
                $http_code . ", response: " . print_r($response, true), 
                $http_code
            );
        }
        
        $results = array();
        foreach($response as $result)
        {
        		$results[] = Node::inflateFromResponse($this->_neo_db, $result);	
        }
        
        // TODO: Needed?  Shouldn't we get exception from above?  REST API
        //     docs are not clear.
        if (empty($results)) {
            throw new Neo4jRest_NotFoundException("Response was empty.", 404);
        }
		
        return $results;
    }
	
}
