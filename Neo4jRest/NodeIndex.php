<?php
/** 
 * Provide an index for nodes. 
 * 
 * @package Neo4jRest
 *  
 */

namespace Neo4jRest;

class NodeIndex extends Index {
   
   function getEntityType() {
      return get_class(new Node($this->_neo4j_db));
   }

   
}