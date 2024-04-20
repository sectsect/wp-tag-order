#!/usr/bin/env bash

if [ $# -lt 3 ]; then
	echo "usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version] [skip-database-creation]"
	exit 1
fi

DB_NAME=$1
DB_USER=$2
DB_PASS=$3
DB_HOST=${4-127.0.0.1}
WP_VERSION=${5-latest}
SKIP_DB_CREATE=${6-false}

# ...

if [ ${SKIP_DB_CREATE} = "true" ]; then
	return 0
fi

# Create database using mysql command instead of mysqladmin
mysql --user=$DB_USER --password=$DB_PASS --host=$DB_HOST --protocol=tcp -e "CREATE DATABASE IF NOT EXISTS $DB_NAME"
