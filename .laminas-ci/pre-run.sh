#!/usr/bin/env bash

# Regenerate docblocks on test/StaticAnalysis files so psalm will pick up any breakages

WORKDIR=$2
JOB=$3
COMMAND=$(echo "${JOB}" | jq -r '.command')
if [[ ! ${COMMAND} =~ psalm ]];then
    exit 0
fi

cd "$WORKDIR"
vendor/bin/laminas --container test/container.php form:psalm-type test/StaticAnalysis
