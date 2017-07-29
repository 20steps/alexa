Building and distributing the Bricks platform standard edition
==============================================================

Test locally and via Travis-CI
------------------------------
1. Change your cwd to bricks-platform-standard-edition directory
2. Execute unit tests by executing
```bash
phpunit
```
3. If all tests pass push to github
```bash
git commit -am "your commit message"
git push origin master
```
4. Inspect test results on [Travis][2]

Pack and distribute
-------------------
0. If you are on MacOS X install gnu-tar using

```bash
brew install gnu-tar
echo "\n" >> ~/.bash_profile
echo 'PATH="/usr/local/opt/gnu-tar/libexec/gnubin:$PATH"' >> ~/.bash_profile
source ~/.bash_profile
```

1. If all tests pass add an appropriate version number to the file versions.json and push to github
```bash
git commit -am "your release message"
git push origin master
```
2. Create a git tag and push the tag to github by executing
```bash
git tag new_version
git push origin --tags
```
3. Create a packed version in downloads/latest.tgz for the [Bricks Installer][2] by executing
```bash
bin/build/pack
```
4. Make the packed version available via https://bricks.20steps.de/downloads/* by executing
```bash
bin/build/upload
```

[1]:  https://phpunit.de/manual/current/en/installation.html
[2]:  https://travis-ci.org/20steps/bricks-platform-standard-edition