#!/bin/bash

EZPLATFORM_BRANCH=`php -r 'echo json_decode(file_get_contents("./composer.json"))->extra->_ezplatform_branch_for_behat_tests;'`
EZPLATFORM_BRANCH="${EZPLATFORM_BRANCH:-master}"
PACKAGE_BUILD_DIR=$PWD
EZPLATFORM_BUILD_DIR=${HOME}/build/ezplatform

echo "> Cloning ezsystems/ezplatform:${EZPLATFORM_BRANCH}"
git clone --depth 1 --single-branch --branch "${EZPLATFORM_BRANCH}" ${EZPLATFORM_REPO} ${EZPLATFORM_BUILD_DIR}
cd ${EZPLATFORM_BUILD_DIR}

# Copy over auth.json
cp $TRAVIS_BUILD_DIR/auth.json .

/bin/bash ./bin/.travis/trusty/setup_ezplatform.sh "${COMPOSE_FILE}" '' "${PACKAGE_BUILD_DIR}"
