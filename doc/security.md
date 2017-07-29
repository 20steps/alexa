Security
=========

Check for known vulnerabilities
-------------------------------

Check for known vulnerabilities of dependencies in vendor/ by executing

```bash
bin/console security:check
```

.. and for known vulnerabilities of plugins in web/wp-content/plugins/ by executing

```bash
bin/wp wp-sec check
```

Hunt for installed exploits
---------------------------

```bash
bin/wp launchcheck secure
```

TBD: integrated bin/wp wp-sec and bin/wp launchcheck secure into Bricks monitoring.