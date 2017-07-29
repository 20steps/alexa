Apache Solr
============

Apache Solr comes bundled with bricks-core and the bricks-platform-standard-edition.

Create Apache Solr schemata for active foundlets:

```bash
bin/console bricks:found:setup
```

Startup the Apache Solr process by executing

```bash
bin/vendor/solr
```

Goto <a href="http://localhost.com:10071">Solr Mgmt Console</a> - configured cores will be shown

To index execute

```bash
bin/console bricks:found:process
```

Hints:
* Apache Solr has to be up and running while indexing
* You don't have to index manually if the Bricks Job Service is up and running
* Apache Solr requires a current Java runtime.

TBD: Explain what a foundlet is