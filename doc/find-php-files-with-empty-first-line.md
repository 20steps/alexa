brew install pcre
pcregrep -Mron --buffer-size="100000000" --include="php" '\n\<\?php' web/ | grep "php:1:"