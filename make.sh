#!/bin/bash
#
# Install Helper
#
# SPDX-License-Identifier: GPL-3.0-only
#

set -o errexit
set -o errtrace
set -o nounset
set -o pipefail

BIN_SELF=$(readlink -f "$0")
APP_ROOT=$(dirname "$BIN_SELF")

action=${1:-help}

# Do Stuff
case "$action" in
# Install Stuff
install)

	composer update --no-ansi --no-dev --no-progress --quiet --classmap-authoritative

	npm install --quiet

	;;

# Help, the default target
"help"|*)

	echo
	echo "You must supply a make command"
	echo
	awk '/^# [A-Z\-].+/ { h=$0 }; /^[0-9a-z\-]+\)/ { printf " \033[0;49;31m%-15s\033[0m%s\n", gensub(/\)$/, "", 1, $$1), h }' "$BIN_SELF" |sort
	echo

esac
