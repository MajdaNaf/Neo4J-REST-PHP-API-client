<?php
/**
 * 
 * Defines uniquness filters as used by the traversal framework.
 * 
 * @author Zahiar Ahmed
 * @package Neo4jRest
 * 
 */

namespace Neo4jRest;

class TraverserUniquenessFilter {
	const NODE_GLOBAL = 'node_global';
	const NODE_PATH = 'node_path';
        const RELATIONSHIP_GLOBAL = 'relationship_global';
        const RELATIONSHIP_PATH = 'relationship_path';
        const NONE = 'none';
}