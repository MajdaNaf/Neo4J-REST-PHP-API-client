<?php

/**
 * GraphDatabaseService is the main access point to a running Neo4j instance. 
 * 
 * 
 * Note: This API should be the same as the the Java API 
 * (http://api.neo4j.org/current/) when possible.
 * The main constraint preventing this is that we use the Neo4j REST
 * API (http://components.neo4j.org/neo4j-server/stable/rest.html) to 
 * communicate with the Neo4j server.  As the REST API grows to expose
 * more of the native API we should be able to get closer to this goal. 
 * 
 * @example ../examples/demo.php Using the GraphDatabaseService
 *
 * @package Neo4jRest
 */

namespace Neo4jRest;

class GraphDatabaseService
{
	public $base_uri;
	
	protected $httpHelper;
	protected $indexManager;
	
	public function __construct($base_uri)
	{
		 $this->base_uri = $base_uri;
		 $this->helper = new HttpHelper;
       $this->indexManager = new IndexManager($this); 
	}
	
	public function getNodeById($node_id)
	{
		$uri = $this->base_uri.'node/'.$node_id;
		
		list($response, $http_code) = $this->helper->jsonGetRequest($uri);

		switch ($http_code)
		{
			case 200:
				return Node::inflateFromResponse($this, $response);
			case 404:
				throw new Neo4jRest_NotFoundException('Node with id "' . $node_id . 
				'" not found.');
			default:
				throw new Neo4jRest_HttpException($http_code);
		}
	}
	
	public function createNode()
	{
		return new Node($this);
	}
	
	public function getBaseUri()
	{
		return $this->base_uri;
	}
	
   public function getRoot()
   {	
       $uri = $this->getBaseUri();
       list($response, $http_code) = HttpHelper::jsonGetRequest($uri);
       if ($http_code!=200) throw new Neo4jRest_HttpException($http_code);
       return $response;
   }
   
   public function index() {
      
      return $this->indexManager;
      
   }

	public function getNodeByUri($uri)
	{
		list($response, $http_code) = HTTPHelper::jsonGetRequest($uri);
	
		switch ($http_code)
		{
			case 200:
				break;
			case 404:
				throw new Neo4jRest_NotFoundException();
				break;
			default:
				throw new Neo4jRest_HttpException('http code: ' . $http_code . 
					', response: ' . print_r($response, true), $http_code);
				break;
		}
		return Node::inflateFromResponse($this, $response);
	}   

	public function getRelationshipByUri($uri)
	{
		list($response, $http_code) = HTTPHelper::jsonGetRequest($uri);
	
		switch ($http_code)
		{
			case 200:
				return Relationship::inflateFromResponse($this, $response);
			case 404:
				throw new Neo4jRest_NotFoundException();
			default:
				throw new Neo4jRest_HttpException('http code: ' . $http_code . 
					', response: ' . print_r($response, true), $http_code);
		}
	}

	public function getRelationshipById($id)
	{
		$uri = $this->base_uri . 'relationship/' . $id;
		
		list($response, $http_code) = $this->helper->jsonGetRequest($uri);

		switch ($http_code)
		{
			case 200:
				return Relationship::inflateFromResponse($this, $response);
			case 404:
				throw new Neo4jRest_NotFoundException("Relationship not found", $http_code);
			default:
				throw new Neo4jRest_HttpException($http_code);
		}
	}	
	
	
}
