# Neo4J PHP REST API client #

* Complete implementation of the Neo4J REST API except Traversal.
* Based on Neo4j 1.3 Server
* Complete set of unit tests


## Features ##

*  Nodes
*  Relationships
*  Indexes
*  Paths

## Todo ##

* Better Documentation!
* Traversal access
* Indexing
* Prevent multiple copies of the same node or relationship object (implement cache in load node and load relationship)
* Replace HTTP Helper with a pluggable implementation, possible a standard library that already exists.

## Getting started ##

* Download the latest version (1.3) of the Neo4j Server (tested on Community Edition)
* Run it
* `php examples\demo.php`
* Or better, run and examine the tests using phpunit (version 3.5.13) or using phing to build the enther project including documentation.

## Requirements ##

PHP 5.3 (uses namespaces) or greater that has:

* curl

## Going further ##

Generate API documentation:

`phing docs`

To generate documentation, you need 

* [Phing](http://phing.info/trac/wiki/Users/Download)
* [PhpDocumentor](http://www.phpdoc.org/)
