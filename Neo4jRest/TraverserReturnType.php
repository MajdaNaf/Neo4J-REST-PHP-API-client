<?php
/**
 * 
 * Defines return types as used by the traversal framework.
 * 
 * @author Zahiar Ahmed
 * @package Neo4jRest
 * 
 */

namespace Neo4jRest;

class TraverserReturnType {
	const NODE = 'node';
	const RELATIONSHIP = 'relationship';
	const PATH = 'path';
	const FULLPATH = 'fullpath';
}