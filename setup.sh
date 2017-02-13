#!/bin/bash
DB_HOST='localhost'
DB_USER='root'
DB_PASS='<ROOT_PASSWORD>'

mysql -h "$DB_HOST" -u "$DB_USER" --password="$DB_PASS" < setup.sql


