#!/usr/bin/env bash

. "/vagrant/setup/helper-functions.sh"
. "/vagrant/setup/variables.sh"

su - vagrant -c "/vagrant/setup/scripts/post-project-update.sh"

service apache2 start