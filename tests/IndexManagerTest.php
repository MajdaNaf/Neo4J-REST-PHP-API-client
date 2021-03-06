<?php

require_once 'Neo4jRestTestCase.php';

/**
 * Test class for IndexManager.
 * Generated by PHPUnit on 2011-04-17 at 16:54:54.
 */
class IndexManagerTest extends Neo4jRestTestCase
{
   /**
    * @var IndexManager
    */
   protected $indexMgr;

   /**
    * Sets up the fixture, for example, opens a network connection.
    * This method is called before a test is executed.
    */
   protected function setUp()
   {
      parent::setUp();
      $this->indexMgr = $this->graphDb->index();
   }

   /**
    * Tears down the fixture, for example, closes a network connection.
    * This method is called after a test is executed.
    */
   protected function tearDown()
   {
   }

   /**
    * @todo Delete the index fixture when tests are complete. Not yet
    * 	available in the REST api.
    */
   public function testNodeIndexNames()
   {

      $response = $this->indexMgr->nodeIndexNames();
      $countBefore =  count($response);
       
      // Create an index so we can test if it is listed, as well as making
      // sure there is at least one item on the list for checking keys.
      $indexName = strval(mt_rand());
      $this->indexMgr->forNodes($indexName);
       
      $response = $this->indexMgr->nodeIndexNames();
      $countAfter = count($response);
       
      $this->assertTrue($countAfter >= $countBefore, 'Count of total'. 
      	' indexes should have increased.');
      
      $this->assertArrayHasKey($indexName, $response, 'Newly created' . 
         ' index should be in the list');

      // Expected keys in the response for a specific index.
      $this->assertArrayHasKey('template', $response[$indexName], 
      	'Expected key for index.');
      $this->assertArrayHasKey('provider', $response[$indexName],
      	'Expected key for index.');
      $this->assertArrayHasKey('type', $response[$indexName],
      	'Expected key for index.');
   }

   /**
    * @todo Delete the index fixture when tests are complete. Not yet
    * 	available in the REST api.
    */
   public function testForNodes()
   {
      // Create an index so we can test if it has been added.
      $indexName = strval(mt_rand());
      $index = $this->indexMgr->forNodes($indexName);
      
      // Make sure we got back an Index
      $this->assertInstanceOf('Neo4jRest\Index', $index);
   }

   /**
    * @todo Delete the index fixture when tests are complete. Not yet
    * 	available in the REST api.
    */
   public function testRelationshipIndexNames()
   {

      $response = $this->indexMgr->relationshipIndexNames();
      $countBefore =  count($response);
       
      // Create an index so we can test if it is listed, as well as making
      // sure there is at least one item on the list for checking keys.
      $indexName = strval(mt_rand());
      $this->indexMgr->forRelationships($indexName);
       
      $response = $this->indexMgr->relationshipIndexNames();
      $countAfter = count($response);
       
      $this->assertTrue($countAfter >= $countBefore, 'Count of total'. 
      	' indexes should have increased.');
      
      $this->assertArrayHasKey($indexName, $response, 'Newly created' . 
         ' index should be in the list');

      // Expected keys in the response for a specific index.
      $this->assertArrayHasKey('template', $response[$indexName], 
      	'Expected key for index.');
      $this->assertArrayHasKey('provider', $response[$indexName],
      	'Expected key for index.');
      $this->assertArrayHasKey('type', $response[$indexName],
      	'Expected key for index.');
      
   }

   /**
    * @todo Delete the index fixture when tests are complete. Not yet
    * 	available in the REST api.
    */
   public function testForRelationships()
   {
      // Create an index so we can test if it has been added.
      $indexName = strval(mt_rand());
      $index = $this->indexMgr->forRelationships($indexName);

      $this->assertInstanceOf('Neo4jRest\RelationshipIndex', $index);

   }
}
?>
