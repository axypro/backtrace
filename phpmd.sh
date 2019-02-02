#!/usr/bin/env sh

cmd="./vendor/bin/phpmd --exclude vendor"
args="text phpmd.xml.dist"

if [ "$#" -ne 0 ]; then
${cmd} "$@" ${args}
else 
${cmd} . ${args}
fi

