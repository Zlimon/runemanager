#!/bin/bash
set -e

mongosh "${DATABASE_NAME:-osrsbox-db}" <<EOF
  const admin = db.getSiblingDB('admin');
  admin.auth('${MONGO_INITDB_ROOT_USERNAME}', '${MONGO_INITDB_ROOT_PASSWORD}');

  db.createUser({
    user: '${PROJECT_USERNAME}',
    pwd: '${PROJECT_PASSWORD}',
    roles: [{ role: 'readWrite', db: '${DATABASE_NAME:-osrsbox-db}' }]
  });

  db.temp.insertOne({ random: 'bootstrap' });
EOF
