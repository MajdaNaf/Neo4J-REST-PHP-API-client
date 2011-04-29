<?php
/** 
 * IndexManager provides access to Indices for Nodes and Relationships. 
 * An IndexManager is paired with a GraphDatabaseService 
 * via GraphDatabaseService.index() so that indexes can be accessed 
 * directly from the graph database.
 * 
 * @package Neo4jRest
 *  
 */

namespace Neo4jRest;

class IndexManager
{
   
	public $_neo4j_db;
	
	public function __construct(GraphDatabaseService $neo4j_db)
	{
		$this->_neo4j_db = $neo4j_db;
	}   

   /**
    * Returns the names of all indexes of the specified type.
    * 
    * @return string array
    */
   function indexNames($type='node') {

      // curl -H Accept:application/json 
      // http://localhost:7474/db/data/index/{type}
      
      $uri = $this->getUri() . '/' . $type;
      
      list($response, $http_code) = HttpHelper::jsonGetRequest($uri);
      
      if ($http_code != 200) throw new Neo4jRest_HttpException($http_code);
			
      return $response;
   } 
   	
   /**
    * Returns the names of all existing Node indexes.
    * 
    * @return string array
    */
   function nodeIndexNames() {

      // curl -H Accept:application/json 
      // http://localhost:7474/db/data/index/node
      
      $response = $this->indexNames('node');
      
      return $response;
   }          
   
   /**
    * Add or get index with provided configuration parameters
    * 
    * @return NodeIndex returns an Index for Nodes.
    */
   function forNodes($indexName, 
      array $customConfiguration=NULL) {
      // 'curl-X POST -H Accept:application/json 
      // -H Content-Type:application/json 
      // -d '{"name":"fulltext", 
      // "config":{"type":"fulltext","provider":"lucene"}}' 
      // http://localhost:7474/db/data/index/node

      $response = $this->forType($indexName, $customConfiguration, 'node');
      
      $index = new NodeIndex($indexName, $this->_neo4j_db);      
            
      return $index;
      
      // throw new RuntimeException('Not yet implemented.');
   }
       
   /**
    * Returns the names of all existing Relationship indexes.
    * 
    * @return string array
    */
   function relationshipIndexNames() {
      // curl -H Accept:application/json 
      // http://localhost:7474/db/data/index/relationship
      
      $response = $this->indexNames('relationship');
      
      return $response;
   }

   /**
    * Add index with provided configuration parameters
    * 
    */
   function forRelationships($indexName, 
      array $customConfiguration=NULL) {
      // 'curl-X POST -H Accept:application/json 
      // -H Content-Type:application/json 
      // -d '{"name":"fulltext", 
      // "config":{"type":"fulltext","provider":"lucene"}}' 
      // http://localhost:7474/db/data/index/relationship

      $response = $this->forType($indexName, $customConfiguration, 
      	'relationship');
      
      $index = new RelationshipIndex($indexName, $this->_neo4j_db);      
            
      return $index;
   }   

	public function getUri()
	{
		$uri = $this->_neo4j_db->getBaseUri().'index';
		return $uri;
	}   

   /**
    * Add or get index of type with provided configuration parameters
    * 
    * @return Index returns an Index for Nodes.
    */
   function forType($indexName, 
      array $customConfiguration=NULL, $type='node') {
      // 'curl-X POST -H Accept:application/json 
      // -H Content-Type:application/json 
      // -d '{"name":"fulltext", 
      // "config":{"type":"fulltext","provider":"lucene"}}' 
      // http://localhost:7474/db/data/index/{relationship | node}
         
      $uri = $this->getUri() . '/' . $type;
      $config = $customConfiguration;
      if ( is_null($config) ) {
         $config = 
            array(
            	'type' => 'fulltext', 
            	'provider' => 'lucene'
            );
      }
      
      $data = array('name' => $indexName, 'config' => $config);
            
      list($response, $http_code) = HttpHelper::jsonPostRequest($uri, $data);
      
      if ($http_code != 201) throw new Neo4jRest_HttpException($http_code);
      
      return $response;
         
   }	
	
   
   // Could be implemented using nodeIndexNames()?   
   // boolean	existsForNodes(String indexName) 
   
   // Could be implemented using nodeRelationshipNames()?      
   // boolean	existsForRelationships(String indexName) 

}


                    
