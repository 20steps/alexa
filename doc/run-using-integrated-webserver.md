Startup integrated webserver
============================

Warning: this is for development only.

Setup database
--------------

1. Create user bricks with password bricks
2. Create database bricks_platform_dev
3. Grant bricks all rights for this database

Create database schema:

```bash
bin/console doctrine:schema:create
```

Run webserver
-------------

```bash
sudo bin/console server:run --port 80
```

After webserver started up goto <a href="http://127.0.0.1:8000">http://127.0.0.1:8000</a>
