#!/bin/bash

## From http://rootslabs.net/blog/511-publier-automatiquement-phpdoc-github-travis-ci

# echo "TRAVIS_REPO_SLUG: $TRAVIS_REPO_SLUG"
# echo "TRAVIS_PHP_VERSION: $TRAVIS_PHP_VERSION"
# echo "TRAVIS_PULL_REQUEST: $TRAVIS_PULL_REQUEST"
# echo "TRAVIS_BRANCH: $TRAVIS_BRANCH"
# echo "TRAVIS_BUILD_NUMBER: $TRAVIS_BUILD_NUMBER"

if [ "$TRAVIS_REPO_SLUG" == "locomotivemtl/charcoal-app" ] && [ "$TRAVIS_PULL_REQUEST" == "false" ] && [ "$TRAVIS_PHP_VERSION" == "5.6" ]; then

  echo -e "Publishing PHPDoc to Github pages...\n"

  # Copie de la documentation generee dans le $HOME
  cp -R build/docs $HOME/docs-latest

  cd $HOME
  ## Initialisation et recuperation de la branche gh-pages du depot Git
  git config --global user.email "travis@travis-ci.org"
  git config --global user.name "travis-ci"
  git clone --quiet --branch=gh-pages https://${GH_TOKEN}@${GH_REPO} gh-pages > /dev/null

  cd gh-pages

  ## Suppression de l'ancienne version
  git rm -rf ./docs/$TRAVIS_BRANCH

  ## Création des dossiers
  mkdir docs
  cd docs
  mkdir $TRAVIS_BRANCH

  ## Copie de la nouvelle version
  cp -Rf $HOME/docs-latest/* ./$TRAVIS_BRANCH/
  rm -rf ./$TRAVIS_BRANCH/phpdoc-cache-*

  ## On ajoute tout
  git add -f .
  ## On commit
  git commit -m "PHPDocumentor (Travis Build : $TRAVIS_BUILD_NUMBER  - Branch : $TRAVIS_BRANCH)"
  ## On push
  git push -fq origin gh-pages > /dev/null
  ## Et c est en ligne !

  echo "Published PHPDoc to gh-pages.\n"

fi

