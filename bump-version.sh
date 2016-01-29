#!/bin/bash

set -e

if [ $# -ne 1 ]; then
  echo "Usage: `basename $0` <tag>"
  exit 65
fi


# CHECK MASTER BRANCH
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
if [[ "$CURRENT_BRANCH" != "master" ]]; then
  echo "You have to be on master branch currently on $CURRENT_BRANCH . Aborting"
  exit 65
fi

# CHECK FORMAT OF THE TAG 
php -r "if(preg_match('/^\d+\.\d+\.\d+(?:-([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?(?:\+([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?\$/',\$argv[1])) exit(0) ;else{ echo 'format of version tag is not invalid' . PHP_EOL ; exit(1);}" $1

# CHECK THAT WE CAN CHANGE BRANCH
git checkout gh-pages
git checkout --quiet master

TAG=$1

#
# Tag & build master branch
#
git checkout master
git tag -a ${TAG} -m"Release ${TAG}"
box build

#
# Copy executable file into GH pages
#
git checkout gh-pages

cp gancinph.phar downloads/gancinph-${TAG}.phar
cp gancinph.phar latest/gancinph.phar
git add downloads/gancinph-${TAG}.phar
git add latest/gancinph.phar

SHA1=$(openssl sha1 gancinph.phar | sed 's/^.* //')

JSON='[{"name":"gancinph.phar"'
JSON="${JSON},\"sha1\":\"${SHA1}\""
JSON="${JSON},\"url\":\"http://andou.github.io/gancinph/downloads/gancinph-${TAG}.phar\""
JSON="${JSON},\"version\":\"${TAG}\""

if [ -f gancinph.phar.pubkey ]; then
    cp gancinph.phar.pubkey pubkeys/gancinph-${TAG}.phar.pubkeys
    git add pubkeys/gancinph-${TAG}.phar.pubkeys
    JSON="${JSON},publicKey:\"http://andou.github.io/gancinph/pubkeys/gancinph-${TAG}.phar.pubkey\""
fi

JSON="${JSON}}]"

#
# Update manifest
#
echo "${JSON}" > manifest.json;
git add manifest.json
git commit -m "Bump version ${TAG}"
git push origin gh-pages

#
# Go back to master
#
git checkout master

git push origin ${TAG}
