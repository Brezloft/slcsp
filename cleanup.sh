#!/bin/bash

DB_HOST='localhost'
DB_USER='adHoc2'
DB_PASS='Homework'

mysql -h "$DB_HOST" -u "$DB_USER" --password="$DB_PASS" < cleanup.sql


