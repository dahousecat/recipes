#!/usr/bin/env bash

PROJECT_NAME="recipes"
PROJECT_HUMAN_NAME="recipes"
HOST_NAME="recipes.dev"
IP="192.168.56.101"
FRAMEWORK="laravel"
CMS_SPECIFIC_BOOTSTRAP="laravel"
WEBROOT="/vagrant"
DB_NAME="recipes"
DB_ROOT_PASS="password"
DB_USER="recipes_db"
DB_HOST="localhost"
DB_PASS="password"
SITE_LOCALE="au"
ADMIN_EMAIL="dahousecat@gmail.com"
ADMIN_USERNAME="admin"
ADMIN_PASS="password"
USERNAME="vagrant"

# new 'sites' variable for Drupal installs - space-separated sites, default value "default"
# for other frameworks, this will be an empty string - need to iterate over ""$SITES in scripts to ensure it's run once
SITES="recipes"

# If $SITES lists multiple sites, we should use $DB_NAME as a prefix and create
# databases named after each site
if echo "$SITES" | grep ' ' > /dev/null; then
  for SITE in ""$SITES; do
    DATABASES="${DB_NAME}_${SITE} $DATABASES"
  done
else
  DATABASES="$DB_NAME"
fi