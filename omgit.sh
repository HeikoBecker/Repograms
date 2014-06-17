#!/usr/bin/env bash

# $1: the commit for which you want to obtain the children
function list_children() {
  git rev-list --all --not $1^@ --children | grep "^$1" \
 | gawk '{$1=$1}1' OFS="\n" \
 | tail -n +2 
}

# Test
list_children 6f8d17c604045e312ff5b0d373946b4fcaea5c55

# Wenn commmit mehrere Children hat, finde oldest common ancestor
