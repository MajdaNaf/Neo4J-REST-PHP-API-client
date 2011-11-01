<?php

namespace Neo4jRest;

if (!function_exists('curl_init')) {
  throw new Exception('Neo4jRest needs the CURL PHP extension.');
}

require_once 'Neo4jRest/HttpHelper.php';
require_once 'Neo4jRest/Exception.php';
require_once 'Neo4jRest/PropertyContainer.php';
require_once 'Neo4jRest/GraphDatabaseService.php';
require_once 'Neo4jRest/IndexManager.php';
require_once 'Neo4jRest/Index.php';
require_once 'Neo4jRest/Node.php';
require_once 'Neo4jRest/Relationship.php';
require_once 'Neo4jRest/NodeIndex.php';
require_once 'Neo4jRest/RelationshipIndex.php';
require_once 'Neo4jRest/Path.php';
require_once 'Neo4jRest/RelationshipDescription.php';
require_once 'Neo4jRest/RelationshipDirection.php';
require_once 'Neo4jRest/TraverserOrder.php';
require_once 'Neo4jRest/TraverserReturnFilter.php';
require_once 'Neo4jRest/TraverserUniquenessFilter.php';