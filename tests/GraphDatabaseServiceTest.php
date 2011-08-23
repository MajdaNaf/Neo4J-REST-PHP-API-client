<?php

require_once 'Neo4jRestTestCase.php';

use Neo4jRest\GraphDatabaseService as GraphDatabaseService;

use Neo4jRest\Neo4jRest_HttpException as Neo4jRest_HttpException;
use Neo4jRest\Neo4jRest_NotFoundException as Neo4jRest_NotFoundException;


class GraphDatabaseServiceTest extends Neo4jRestTestCase
{
        
    // Todd Chaffee: this seems like a not very useful test.  It is just
    // testing that the graphDB stores a string correctly...
    public function testUrlSetting()
    {
        $this->assertEquals(
            $this->graphDbUri,
            $this->graphDb->getBaseUri(),
            'Hm, it seems like we can not get the URI back.'
        );
        
    }
    
    public function testGetRoot()
    {
       try {
          $response = $this->graphDb->getRoot();
       }
       catch (Neo4jRest_HttpException $e) {
          $this->fail('Make sure neo4jd service is running. Caught ' . 
          	'exception: ' . $e);
       }
          
       $this->assertInternalType('array', $response);
       
       $this->assertArrayHasKey('relationship_index', $response);
       $this->assertArrayHasKey('node', $response);
       $this->assertArrayHasKey('relationship_types', $response);
       $this->assertArrayHasKey('node_index', $response);
       
       // Root should contain at least a description of how to get to
       // the reference node.
       $this->assertEquals($this->graphDb->getBaseUri(). 'node/0',
          $response['reference_node']);
       
    }
    
    public function testGetRootHttpException() {
       
       $this->setExpectedException('Neo4jRest\Neo4jRest_HttpException');
       
       // Bad URI should generate the Exception.
       $this->graphDb = 
          new Neo4jRest\GraphDatabaseService(
          	'nohttp://localhost:7474/db/data/');

       $response = $this->graphDb->getRoot();
       
    }
    
    public function testCreateNode() {
       $node = $this->graphDb->createNode();
       
       $this->assertInstanceOf('Neo4jRest\Node', $node);
       
    }

    // Shallow test.  No properties.
    public function testGetReferenceNodeById() {
       
       $nodeId = 0;
       
       $node = $this->graphDb->getNodeById($nodeId);
       
       $this->assertInstanceOf('Neo4jRest\Node', $node);
       
       
       $this->assertEquals($nodeId, $node->getId() );
                     
    }
        
    public function testGetNodeById() {
       
       // Create a node with a random property value.
       $node = $this->graphDb->createNode();
       
       $random = mt_rand();
       $node->randProperty = $random;
       $node->save();
       $id = $node->getId();
       unset($node);  // Make sure we don't reuse it.
       
       // Get it back from the graph.
       $e = NULL;
       try {
           $node2 = $this->graphDb->getNodeById($id);
       }
       catch (Neo4jRest_NotFoundException $e) {
       }

       $this->assertEquals($random, $node2->randProperty);
       $this->assertEquals($id, $node2->getId());
       $this->assertNull($e);      
       
       // Delete it.  TODO: If a test fails, this doesn't get executed!
       $node2->delete();
       
       // Now that it's deleted, we should get a NotFound exception
       $e = NULL;
       try {
           $node2 = $this->graphDb->getNodeById($id);
       }
       catch (Neo4jRest_NotFoundException $e) {
       }       

       $this->assertInstanceOf('Neo4jRest\Neo4jRest_NotFoundException', $e);
       
       // String id should give NotFound exception.
       $e = NULL;
       try {
           $node2 = $this->graphDb->getNodeById('ShouldBeInt');
       }
       catch (Neo4jRest_NotFoundException $e) {
       }       

       $this->assertInstanceOf('Neo4jRest\Neo4jRest_NotFoundException', $e);
       
       // Bad url for graphDb should give general Http exception.
       $e = NULL;
       $graphDb = new GraphDatabaseService('x');
       try {
           $node2 = $graphDb->getNodeById($id);
       }
       catch (Neo4jRest_HttpException $e) {
       }       

       $this->assertInstanceOf('Neo4jRest\Neo4jRest_HttpException', $e);
              
    }
    
    public function testIndex() {
       
       $indexMgr = $this->graphDb->index();
       
       $this->assertInstanceOf('Neo4jRest\IndexManager', $indexMgr);
       
    }
    
    public function testGetNodeByUri() {
        
        $node = new Neo4jRest\Node($this->graphDb);
        $node->save();
        
        $uri = $node->getUri();
        $nodeGot = $this->graphDb->getNodeByUri($uri);
        $this->assertEquals($node, $nodeGot);
                
        // Deleted node should raise NotFound exception.
        $node->delete();
        $e = NULL;
        try {
            $this->graphDb->getNodeByUri($uri);
        }
        catch (Neo4jRest_NotFoundException $e) {
        }
        
        $this->assertInstanceOf('Neo4jRest\Neo4jRest_NotFoundException', $e);

        // Bogus uri should raise Http exception.
        $e = NULL;
        try {
            $this->graphDb->getNodeByUri('x');
        }
        catch (Neo4jRest_HttpException $e) {
        }
        
        $this->assertInstanceOf('Neo4jRest\Neo4jRest_HttpException', $e);
        
    }    

    public function testGetRelationshipByUri() {
        
        $startNode = new Neo4jRest\Node($this->graphDb);
        $startNode->save();
        $endNode = new Neo4jRest\Node($this->graphDb);
        $endNode->save();
        $type = 'Test Relationship Type';
        $rel = new Neo4jRest\Relationship($this->graphDb, $startNode, 
           $endNode, $type);
        $rel->save();
        
        $uri = $rel->getUri();
        $relGot = $this->graphDb->getRelationshipByUri($uri);
        $this->assertEquals($rel, $relGot);
                
        // Deleted Relationship with same uri should raise NotFound exception.
        $id = $rel->getId();
        $rel->delete();
        $e = NULL;
        try {
            $this->graphDb->getRelationshipByUri($uri);
        }
        catch (Neo4jRest_NotFoundException $e) {
        }
        
        $this->assertInstanceOf('Neo4jRest\Neo4jRest_NotFoundException', $e);

        // Bogus uri should raise Http exception.
        $e = NULL;
        try {
            $this->graphDb->getRelationshipByUri('x');
        }
        catch (Neo4jRest_HttpException $e) {
        }
        
        $this->assertInstanceOf('Neo4jRest\Neo4jRest_HttpException', $e);        
        
    }       

    public function testGetRelationshipById() {
       
        // Create a relationship and get it back by id.
        $startNode = new Neo4jRest\Node($this->graphDb);
        $startNode->save();
        $endNode = new Neo4jRest\Node($this->graphDb);
        $endNode->save();
        $type = 'Test Relationship Type';
        $rel = new Neo4jRest\Relationship($this->graphDb, $startNode, 
           $endNode, $type);
        $rel->save();
        
       
        $id = $rel->getId();
       
        // Get it back from the graph.
        $rel2 = $this->graphDb->getRelationshipById($id);

        $this->assertEquals($rel, $rel2);
       
        // Deleted relationship should raise NotFound exception.
        $rel->delete();
        $e = NULL;
        try {
            $rel2 = $this->graphDb->getRelationshipById($id);
        }
        catch (Neo4jRest_NotFoundException $e) {
        }       

        $this->assertInstanceOf('Neo4jRest\Neo4jRest_NotFoundException', $e);
              
       // Bad url for graphDb should give general Http exception.
       $e = NULL;
       $graphDb = new Neo4jRest\GraphDatabaseService('x');
       try {
           $rel2 = $graphDb->getRelationShipById($id);
       }
       catch (Neo4jRest_HttpException $e) {
       }       

       $this->assertInstanceOf('Neo4jRest\Neo4jRest_HttpException', $e);
              
    }
    
    
}