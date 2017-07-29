Startup PHP Process Manager (Experimental)
==========================================

Startup [**PHP Process Manager (PPM)**][1] for development environment by executing

```bash
bin/vendor/ppm start --bootstrap=symfony --app-env=dev  --logging=0 --debug=0 --workers=20 --socket-path=var/ppm/run/ --port=8000
```

... or for production environment by executing

```bash
bin/vendor/ppm start --bootstrap=symfony --app-env=prod  --logging=0 --debug=0 --workers=20 --socket-path=var/ppm/run/ --port=8000
```

After PPM started up goto <a href="http://127.0.0.1:8000">http://127.0.0.1:8000</a>

Performance increase will be about factor 15 relative to PHP-FPM, mod_php, integrated webserver etc.

[1]:  https://github.com/php-pm/php-pm
