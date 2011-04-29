<?php

require_once 'Neo4jRest.php';

class Neo4jRestTestCase extends PHPUnit_Framework_TestCase
{
    protected function setUp() {
        $this->graphDbUri = 'http://localhost:7474/db/data/';
        $this->graphDb = new Neo4jRest\GraphDatabaseService($this->graphDbUri);
    }
}