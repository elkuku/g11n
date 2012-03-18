#!/bin/bash
#
# This will create a file "version.txt" with the version of the **previous*** commit
#
# Must be symlinked/copied to .git/hooks/pre-commit

projectDir=library/g11n

git describe --long > $projectDir/version.txt

git add $projectDir/version.txt
