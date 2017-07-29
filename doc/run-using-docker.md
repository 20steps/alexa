Startup Bricks Cloud using Docker
=================================

Warning: this is for development only.

Install and start Docker
------------------------

* <a href="https://docs.docker.com/docker-for-mac/">Mac OS</a>
* <a href="https://docs.docker.com/engine/installation/linux/">Linux</a>
* <a href="https://docs.docker.com/engine/installation/windows/">Windows</a>

Share project directory
-----------------------

* Possible add the (parent of the) project folder to the directories shared by Docker from the host to the containers using the Docker Management Tool.

Rebuild image
-------------

Change cwd to project root.

```bash
docker-compose build
```

Run containers
--------------

Run containers in foreground

```bash
docker-compose up
```

To stop containers enter <ctrl+c>.

Alternatively run containers in background

```bash
docker-compose up -d
```
To stop containers execute 

```bash
docker-compose down
```

After the webserver has started up goto <a href="http://127.0.0.1:8000">http://127.0.0.1:8000</a>.

Show logs
----------------

System logs of containers including logs of MySQL and Apache:

```bash
docker-compose logs -f
```

Platform logs:

TBD.

Execute console commands
------------------------

List all commands:

```bash
docker-compose exec web bin/console
```

Execute specific command:

```bash
docker-compose exec web bin/console your_command
```

Speed up Docker for Mac OS (Experimental, faster by a factor of > 20)
---------------------------------------------------------------------

* Remove previously shared project directory from Docker management tool
* Possibly add directory to be shared to the Mac OS NFS configuration residing in /etc/exports, than execute

```bash
sudo nfsd checkexports
```

* Download https://github.com/IFSight/d4m-nfs
* Add volume to be shared to etc/d4m-nfs-mounts.txt
* Run
```bash
d4m-nfs.sh
```

* Update docker-compose.yml to use /mnt/... instead of "."
* Execute docker-compose up
