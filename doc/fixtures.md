Execute fixtures to generate some dummy content
===============================================

Load fixtures for all bundles (and do not purge database)

```bash
bin/console fixtures:load --append
```

... or only for a specific Custom Brick

```
bin/console fixtures:load -b BUNDLE_SHORTNAME_OF_CUSTOM_BRICK  --append
```
