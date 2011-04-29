<?php
/**
 * An index for retreiving Relationships.
 * Note: use the IndexManager->forRelationships method rather
 * than creating an instance of this class.
 * 
 * @package Neo4jRest
 */

namespace Neo4jRest;

class RelationshipIndex extends Index {
   
   function getEntityType() {
      return 'Neo4jRest\Relationship';
   }
   
}