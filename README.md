# Neo4J PHP REST API client #

* Complete implementation of the [Neo4J REST API](http://components.neo4j.org/neo4j-server/stable/rest.html) except Traversal.
* Based on Neo4j 1.3 Server which includes a breaking change in the index management.
* Complete set of unit tests

## Features ##

*  Nodes
*  Relationships
*  Indexes
*  Paths

## Todo ##

* Traversal access
* Better Documentation!
* Prevent multiple copies of the same node or relationship object (implement cache in load node and load relationship)
* Replace HTTP Helper with a pluggable implementation, possible a standard library that already exists.

## Getting started ##

* Download the latest version (1.3) of the [Neo4j Server](http://neo4j.org/) (tested on Community Edition) and follow the installation instructions.
* Run it
* `php examples\demo.php`
* Or better, run and examine the tests using phpunit (version 3.5.13) or using phing to build the enther project including documentation.

## Requirements ##

PHP 5.3 (uses namespaces) with:

* curl

## Going further ##

Generate API documentation:

`phing docs`

To generate documentation, you need 

* [Phing](http://phing.info/trac/wiki/Users/Download)
* [PhpDocumentor](http://www.phpdoc.org/)
