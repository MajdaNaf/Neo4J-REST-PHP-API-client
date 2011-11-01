<?php
/**
 * Test class for Node.
 * 
 * @author Todd Chaffee
 */

require_once 'Neo4jRestTestCase.php';

use Neo4jRest\TraverserUniquenessFilter;
use Neo4jRest\TraverserReturnFilter;
use Neo4jRest\TraverserOrder;
use Neo4jRest\Neo4jRest_HttpException as Neo4jRest_HttpException; 
use Neo4jRest\Neo4jRest_NotFoundException as Neo4jRest_NotFoundException;
use Neo4jRest\Relationship as Relationship;
use Neo4jRest\RelationshipDescription as RelationshipDescription;
use Neo4jRest\RelationshipDirection as RelationshipDirection;

class NodeTest extends Neo4jRestTestCase
{
    /**
     * @var Node
     */
    protected $node;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->node = new Neo4jRest\Node($this->graphDb);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        try {
            $this->node->delete();
        }
        catch (Neo4jRest_HttpException $e) {
        }
    }

    /**
     * @todo Should we be checking if the node really was deleted from the
     *    graph db?
     */
    public function testDelete()
    {
        
        $this->node->save();
        
        $this->node->delete();
        
        $this->assertNull($this->node->getId());
        $this->assertEquals(FALSE, $this->node->isSaved());
        
    }

    /**
     * @todo Not sure if we should be retrieving the node back from 
     * 	the graph db to make sure it actually was saved.
     */
    public function testSave()
    {
        $node = $this->node;
        
        $node->save();
        $this->assertEquals(TRUE, $node->isSaved());
        
        // Add a property.  Should save properties to existing node in graph db.
        $prop1 = 'test';
        $node->prop1 = 'test';
        $node->save();
        
        $nodeRetrieved = $this->graphDb->getNodebyId($node->getId());
        $this->assertEquals($node, $nodeRetrieved);
        $this->assertEquals($prop1, $nodeRetrieved->prop1);
        
    }

    /**
     * @todo Implement testGetId().
     */
    public function testGetId()
    {
        
        $node = $this->node;
        
        
        
        $this->assertInternalType('null', $node->getId(), 
            'New node should have Id of type "null"');
        
        $node->save();
        $this->assertInternalType('string', $node->getId(), 
            'Saved node should have Id of type "string"');
        
        $node->delete();
        $this->assertInternalType('null', $node->getId(),
            'Deleted node should have Id of type "null"');                
    }

    /**
     * 
     */
    public function testIsSaved()
    {
        
        $this->assertEquals(FALSE, $this->node->isSaved(),
            'Newly created node should not show status of "saved".');
        
        $this->node->save();
        $this->assertEquals(TRUE, $this->node->isSaved(),
            'Saved node should show status of "saved"');

        $this->node->delete();
        $this->assertEquals(FALSE, $this->node->isSaved(),
            'Deleted node should not show status of "saved"');
    }

    /**
     * @todo Implement testGetRelationships().
     */
    public function testGetRelationships()
    {
        // New node should have no relationships.
        $node = $this->node;
        $relationships = $node->getRelationships();
        
        $this->assertInternalType('array', $relationships);
        $this->assertEmpty($relationships, 
            'New node should have no relationships.');
        
        // Create relationship and make sure we can get it back.
        $relType = 'TEST';
        $otherNode = new Neo4jRest\Node($this->graphDb);
        $node->save();
        $otherNode->save();
        $rel = $this->node->createRelationshipTo($otherNode, 
            $relType);
        $rel->save();
        
        $rels = $node->getRelationships(Relationship::DIRECTION_OUT, 
            array($relType));
        $this->assertInternalType('array', $rels);
        $this->assertEquals(1, sizeof($rels));
        $this->assertInstanceOf('Neo4jRest\Relationship', $rels[0]);
        $this->assertEquals(TRUE, $rel == $rels[0]);
        
        
        $rel2 = $otherNode->createRelationshipTo($node, $relType);
        $rel2->save();
        $rels = $node->getRelationships();
        $this->assertEquals(2, sizeof($rels), 
            'After adding 2nd relationship the total count should be 2.');
        
        // Cleanup
        $rel->delete();
        $rel2->delete();
        $otherNode->delete();
        
    }

    /**
     * @todo Implement testCreateRelationshipTo().
     */
    public function testCreateRelationshipTo()
    {
        
        $node = $this->node;
        
        $relType = 'TEST';
        $otherNode = new Neo4jRest\Node($this->graphDb);
        $node->save();
        $otherNode->save();
        $rel = $node->createRelationshipTo($otherNode, 
            $relType);

        $this->assertInstanceOf('Neo4jRest\Relationship', $rel);
        $this->assertEquals($node, $rel->getStartNode());
        $this->assertEquals($otherNode, $rel->getEndNode());
        $this->assertEquals($relType, $rel->getType());
        
    }

    /**
     * @todo Implement testGetUri().
     */
    public function testGetUri()
    {
        $uri = $this->node->getUri();
        
        $this->assertInternalType('string', $uri);
        $this->assertStringStartsWith($this->graphDb->getBaseUri(), $uri);
                
    }

    /**
     * Node can be inflated from a response
     */
    public function testInflateFromResponse()
    {        
        // Create a dummy response with some properties.
        $id = 10;
        $prop1 = 'prop1';
        $prop1val = 'prop1val';
        $prop2 = 'prop2';
        $prop2val = 'prop2val';
        
        $response = array('self' => $this->node->getUri() . '/' . $id,
            'data' => array($prop1 => $prop1val, $prop2 => $prop2val));        
        
        $node = $this->node->inflateFromResponse($this->graphDb, $response);
        
        $this->assertInstanceOf('Neo4jRest\Node', $node);
        $this->assertTrue($node->isSaved());
        $this->assertEquals($id, $node->getId());
        $this->assertEquals($prop1val, $node->prop1);
        $this->assertEquals($prop2val, $node->prop2);
    }

    /**
     * @todo Implement testFindPaths().
     */
    public function testFindPaths()
    {
        $node = $this->node;
        $otherNode = new Neo4jRest\Node($this->graphDb);
        
        $gotException = FALSE;
        try {
            $node->findPaths($otherNode);
        }
        catch (Neo4jRest_HttpException $e) {
            if ($e->getCode() == 405) {
                $gotException = TRUE;
            }
        }
        
        $this->assertEquals(TRUE, $gotException, 
            'Finding a path with an unsaved node should raise ' . 
            'Neo4jRest_HttpException with code 405');
        
        // Now save the two nodes and we should get 204 
        // Neo4jRest_NotFoundException because they are now valid nodes 
        // in the graph db, but there is still no path between the nodes.
        $node->save();
        $otherNode->save();
        
        $gotException = FALSE;
        try {
            $paths = $node->findPaths($otherNode);
        }
        catch (Neo4jRest_NotFoundException $e) {
            $this->assertEquals(0, $e->getCode());
            if ($e->getCode() == 0) {
                $gotException = TRUE;
            }
        }
        
        $this->assertEquals(TRUE, $gotException, 
            'Finding paths with an unsaved node should raise ' . 
            'Neo4jRest_NotFoundException with code 0');

        // Now create a path between the two nodes and we should get back a 
        // valid list of paths.
        $relType = 'TEST';
        $rel = $node->createRelationshipTo($otherNode, $relType);
        $rel->save();
        
        $maxDepth = 1;
        $relDesc = new RelationshipDescription($relType, 
            Relationship::DIRECTION_OUT);
        $paths = $node->findPaths($otherNode, $maxDepth, $relDesc);
        
        $this->assertInternalType('array', $paths);
        $path = $paths[0];
        $this->assertInstanceOf('Neo4jRest\Path', $path);
        $this->assertEquals($node, $path->startNode());
        $this->assertEquals($otherNode, $path->endNode());
        $rels = $path->relationships();
        $this->assertEquals($rel, $rels[0]);
        $this->assertEquals(1, $path->length());
        
        $nodesOnPath = $path->nodes();
        $this->assertEquals(2, sizeof($nodesOnPath));
        $this->assertEquals($node, $nodesOnPath[0]);
        $this->assertEquals($otherNode, $nodesOnPath[1]);
        
        // Cleanup. $node is automatically cleaned up.
        $rel->delete();
        $otherNode->delete();
        
    }

    /**
     * 
     */
    public function testFindPath()
    {
        
        $node = $this->node;
        $otherNode = new Neo4jRest\Node($this->graphDb);
        
        $gotException = FALSE;
        try {
            $node->findPath($otherNode);
        }
        catch (Neo4jRest_HttpException $e) {
            if ($e->getCode() == 405) {
                $gotException = TRUE;
            }
        }
        
        $this->assertEquals(TRUE, $gotException, 
            'Finding a path with an unsaved node should raise ' . 
            'Neo4jRest_HttpException with code 405');
        
        // Now save the two nodes and we should get 404 
        // Neo4jRest_NotFoundException
        // because they are now valid nodes in the graph db, but there is
        // still no path between the nodes.
        $node->save();
        $otherNode->save();
        
        $gotException = FALSE;
        try {
            $node->findPath($otherNode);
        }
        catch (Neo4jRest_NotFoundException $e) {
            if ($e->getCode() == 404) {
                $gotException = TRUE;
            }
        }
        
        $this->assertEquals(TRUE, $gotException, 
            'Finding a path with an unsaved node should raise ' . 
            'Neo4jRest_NotFoundException with code 404');

        // Now create a path between the two nodes and we should get back a 
        // valid path.
        $relType = 'TEST';
        $rel = $node->createRelationshipTo($otherNode, $relType);
        $rel->save();
        
        $maxDepth = 1;
        $relDesc = new RelationshipDescription($relType, 
            Relationship::DIRECTION_OUT);
        $path = $node->findPath($otherNode, $maxDepth, $relDesc);
        
        $this->assertInstanceOf('Neo4jRest\Path', $path);
        $this->assertEquals($node, $path->startNode());
        $this->assertEquals($otherNode, $path->endNode());
        $rels = $path->relationships();
        $this->assertEquals($rel, $rels[0]);
        
        // Cleanup. $node is automatically cleaned up.
        $rel->delete();
        $otherNode->delete();        
        
    }
    
    /**
     * 
     * Tests the traverse function.
     * 
     */
    public function testTraverse() {
        
        // Create some nodes and relationships.
        $node = $this->node;
        $otherNode = new Neo4jRest\Node($this->graphDb);
                
        // Save the two nodes and we should a not found exception 
        // because they are now valid nodes in the graph db, but there is
        // still no path between the nodes.
        $node->save();
        $otherNode->save();

        $gotException = FALSE;
        try {
            $nodes= $node->traverse();
        }
        catch (Neo4jRest_NotFoundException $e) {
            if ($e->getCode() == 404) {
                $gotException = TRUE;
            }
        }
        
        $this->assertEquals(TRUE, $gotException, 
            'Traversing a node with no relationships should raise ' . 
            'Neo4jRest_NotFoundException with code 404');        
               
        // Create a path between the two nodes and we should get back 
        // the second node.
        $relType = 'TEST';
        $rel = $node->createRelationshipTo($otherNode, $relType);
        $rel->save();
        
        $maxDepth = 1;
        $nodes = $node->traverse(TraverserOrder::DEPTH_FIRST, $maxDepth); 

        $this->assertEquals($otherNode, $nodes[0]);        
                
        // Try to include the first node. 
        $nodes = $node->traverse(TraverserOrder::DEPTH_FIRST, $maxDepth,
            null, TraverserReturnFilter::ALL, null); 
        
        $this->assertEquals($node, $nodes[0]);   
        $this->assertEquals($otherNode, $nodes[1]);   

        // Test the stop evaluator.
        // First, without the stop evaluator to ensure we get the third node.
        // Then with the stop evaluator to ensure we don't get the third node.
        // Note: stop Evaluator should be javascript code.
        $name = 'Ugha Nama';
        $otherNode->name = $name;
        $otherNode->save();
        $thirdNode = new Neo4jRest\Node($this->graphDb);
        $thirdNode->save();
        $relType = 'TEST2';
        $rel2 = $otherNode->createRelationshipTo($thirdNode, $relType);
        $rel2->save();
        
        $stopEvaluator = 
            "position.endNode().getProperty('name')=='$name';";

        $nodes = $node->traverse(TraverserOrder::DEPTH_FIRST, 2);
        $this->assertEquals(2, sizeof($nodes));
        $this->assertEquals($otherNode, $nodes[0]);
        $this->assertEquals($thirdNode, $nodes[1]);
        
        $nodes = $node->traverse(TraverserOrder::DEPTH_FIRST, null,
            $stopEvaluator); 
        
        $this->assertEquals(1, sizeof($nodes));
        $this->assertEquals($otherNode, $nodes[0]);
                   
        // Test relationship types and directions.
        // First, with both relation types
        // Then with only one
        $relsAndDirs = new RelationshipDescription('TEST');
        $relsAndDirs->add('TEST2', Relationship::DIRECTION_OUT);
         
        $nodes = $node->traverse(TraverserOrder::DEPTH_FIRST, 2, null,
            null, $relsAndDirs);
        $this->assertEquals(2, sizeof($nodes));
        $this->assertEquals($otherNode, $nodes[0]);
        $this->assertEquals($thirdNode, $nodes[1]);

        $relsAndDirs = new RelationshipDescription('TEST', 
            Relationship::DIRECTION_OUT);
        
        $nodes = $node->traverse(TraverserOrder::DEPTH_FIRST, 2, null,
            null, $relsAndDirs);
                    
        $this->assertEquals(1, sizeof($nodes));
        $this->assertEquals($otherNode, $nodes[0]);

        // TODO: Tests for return types other than node.
        
        // Tests the uniqueness filter
        $nodes = $node->traverse(TraverserOrder::DEPTH_FIRST, $maxDepth,
            null, TraverserReturnFilter::ALL, null, 'node', TraverserUniquenessFilter::RELATIONSHIP_GLOBAL); 
        $this->assertEquals($node, $nodes[0]);
        
        $nodes = $node->traverse(TraverserOrder::DEPTH_FIRST, $maxDepth,
            null, TraverserReturnFilter::ALL, null, 'node', TraverserUniquenessFilter::RELATIONSHIP_PATH); 
        $this->assertEquals($node, $nodes[0]);
        
        $nodes = $node->traverse(TraverserOrder::DEPTH_FIRST, $maxDepth,
            null, TraverserReturnFilter::ALL, null, 'node', TraverserUniquenessFilter::NODE_GLOBAL); 
        $this->assertEquals($node, $nodes[0]);
        
        $nodes = $node->traverse(TraverserOrder::DEPTH_FIRST, $maxDepth,
            null, TraverserReturnFilter::ALL, null, 'node', TraverserUniquenessFilter::NODE_PATH); 
        $this->assertEquals($node, $nodes[0]);
        
        $nodes = $node->traverse(TraverserOrder::DEPTH_FIRST, $maxDepth,
            null, TraverserReturnFilter::ALL, null, 'node', TraverserUniquenessFilter::NONE); 
        $this->assertEquals($node, $nodes[0]);

        // Cleanup. $node is automatically cleaned up.
        $rel->delete();
        $rel2->delete();
        $otherNode->delete();
        $thirdNode->delete();        
                        
    }
}
?>
