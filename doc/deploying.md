Deploying
=========

1. Setup new node(s) for deployment and perform initial deploy

* Ask your hosting provider for ssh URLs for the new nodes
* Add node(s) in section bricks.platform.remote of composer.json
* Add or update deployment target comprising selected remotes in section bricks-platform.target of composer.json
* Check that the file "etc/credentials/.credentials" in section bricks.platform.config for stage live points to the right location for the given target
* Check that the file "etc/parameters/node.yml" in section bricks.platform.config for stage live has a corresponding host-based target prepared.
Execute:

```bash
composer deploy -- your_target
```

After successfull deploy on new node register the node in Bricks Cluster Database by executing on the node

```bash
bin/console bricks:core:cluster:node:register
```

3. Deploy software update to target

Execute

```bash
composer deploy your_target
```
