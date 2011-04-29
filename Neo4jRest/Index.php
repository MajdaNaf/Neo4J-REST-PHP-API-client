<?php
/** 
 * Index provides quick lookup of key values.  See Java API for more info.
 * 
 * @package Neo4jRest
 *  
 */

namespace Neo4jRest;

abstract class Index {

   private $_indexName;
   protected $_neo4j_db;
   
   function __construct($indexName, $neo4j_db) {
      $this->_indexName = $indexName;
      $this->_neo4j_db = $neo4j_db;
   }
   
// REST: Add to index   
// Java API:
// void	add(T entity, String key, Object value) 
//          Adds a key/value pair for entity to the index.
   function add($entity, $key, $value ) {
      // curl -H Content-Type:application/json -X POST -d 
      // '"http://localhost:7474/db/data/node/123"' 
      // http://localhost:7474/db/data/index/node/my_nodes/the_key/the_value%20with%20space
      // 
      
      $data = $entity->getUri();
        
      $uri = $this->getUri() . '/' . rawurlencode($key) . '/' . 
          rawurlencode($value);        
      
      list($response, $httpCode) = HttpHelper::jsonPostRequest($uri, $data);
      
      if ($httpCode != 201) throw new Neo4jRest_HttpException($httpCode);
      
      return;
			
   }

    
/* REST: Remove from index
 * server.removeFromNodeIndex
 * server.removeFromRelationshipIndex
 * JAVA API:   
 * void	remove(T entity, String key, Object value) 
 *          Removes a key/value pair for entity from the index.
 */   
   function remove($entity, $key=NULL, $value=NULL) {
        // curl -X DELETE http://localhost:7474/db/data
        //    /index/node/my_nodes/the_key/the_value/123
        // curl -X DELETE http://localhost:7474/db/data
        //    /index/node/my_nodes/the_key/123
        // curl -X DELETE http://localhost:7474/db/data
        //    /index/node/my_nodes/123
       
        $uri = $this->getUri();

        if ($key) {
            $uri = $uri . '/' . rawurlencode($key);
        }
      
        if ($value) {
            $uri = $uri . '/' . rawurlencode($value);
        }
      
        $uri = $uri . '/' . $entity->getId();
        
        list($response, $httpCode) = HttpHelper::deleteRequest($uri);
      
        if ($httpCode == 404) {
            throw new Neo4jRest_NotFoundException(
            	'Index entry not found', $httpCode);
        }
        else if ($httpCode != 204) {
            throw new Neo4jRest_HttpException(
            'Http exception trying to find index entry', $httpCode); 
        }


   }

   /**
    * Query index -- Exact
    *
    * Returns exact matches from this index, given the key/value pair.
    * Implements the following REST API:
    *    http://components.neo4j.org/neo4j-server/stable/rest.html#Query_index_--_Exact
    *
    * JAVA API:
    * IndexHits<T>	get(String key, Object value)
    *
    *
    */
   function get( $key, $value )
   {
       // curl -H Accept:application/json
       //     http://localhost:7474/db/data
       //     /index/node/my_nodes/the_key/the_value%20with%20space
       // curl -H Accept:application/json
       //     http://localhost:7474/db/data
       //     /index/relationship/my_rels/the_key/the_value%20with%20space

       $uri = $this->getUri()  . '/' . rawurlencode($key) .
      	'/' . rawurlencode($value);

                    
       list($response, $httpCode) = HttpHelper::jsonGetRequest($uri);

       if ($httpCode != 200) {
           throw new Neo4jRest_HttpException(
          'Http exception trying to find index entry', $httpCode); 
       }

       // If the list is empty raise a Not Found exception.
       // TODO: Should be added to the REST API?  Lame that it just returns
       //     an empty list.
       if (empty($response)) {
           throw new Neo4jRest_NotFoundException('Entity not found using ' . 
               'supplied key and value', 400);
       }       
       
       $entities = array();
       $entityType = $this->getEntityType();
       foreach ($response as $result) {
           $entity = $entityType::inflateFromResponse($this->_neo4j_db,
               $result);
           $entities[] = $entity;
       }
       


       return $entities;

   }

   /* REST: Query index -- Advanced
    * JAVA:
    * IndexHits<T>	query(String key, Object queryOrQueryObject)
    *        Returns matches from this index based on the supplied key and query object, which can be a query string or an implementation-specific query object.
    *
    */
   function query($key, $query) {
       // curl -H Accept:application/json 
       //     http://localhost:7474/db/data/index/node/my_nodes
       //    /the_key?query=the_value%20with%20space
       // curl -H Accept:application/json 
       //     http://localhost:7474/db/data/index/relationship/my_rels
       //    /the_key?query=the_value%20with%20space

       $uri = $this->getUri()  . '/' . rawurlencode($key) .
      	'?query=' . rawurlencode($query);

       list($response, $httpCode) = HttpHelper::jsonGetRequest($uri);

       if ($httpCode != 200) {
           throw new Neo4jRest_HttpException(
          'Http exception trying to find index entry', $httpCode); 
       }

       $entities = array();
       $entityType = $this->getEntityType();
       $entityObj = $entityType;
       foreach ($response as $result) {
           $entity = $entityObj::inflateFromResponse($this->_neo4j_db,
               $result);
           $entities[] = $entity;
       }

       return $entities;
       
   }
   
   /**
    * Returns the name of this index.
    * 
    * @return string the name this index was created with
    * 
    */
   function getName() {
      return $this->_indexName;
   }
   
  /**
   * 
   * Provides type of entities managed by this index.
   * 
   * @return string name of the class managed by this index.
   * 
   */
   abstract function getEntityType();

	public function getUri()
	{
	    
	   $entityPath = NULL;
	   
	   if ($this->getEntityType() == 'Neo4jRest\Node') {
	       $entityPath = 'node';
	   } 
	   else if ($this->getEntityType() == 'Neo4jRest\Relationship') {
	       $entityPath = 'relationship';
	   }

	   
		$uri = $this->_neo4j_db->getBaseUri().'index/' . $entityPath . 
		    '/' . $this->getName();
		return $uri;
	}      
   
// Methods from the Java API not yet implemented.   
   
// IndexHits<T>	query(Object queryOrQueryObject) 
//          Returns matches from this index based on the supplied query object, which can be a query string or an implementation-specific query object.
   
   
   
   
}   