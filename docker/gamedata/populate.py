"""
Populates the mongodb-static database with item, monster, and prayer data
from the `osrsreboxed` PyPI package (which bundles the data itself — no
network calls needed at runtime).

Skips if already populated, and retries on failure since mongodb-static
(mongodb-atlas-local) can report "healthy" while still mid-restart during
its own replica-set bootstrap, which can abort an in-progress bulk insert
with a transient "Replication is being shut down" error.
"""
import itertools
import os
import sys
import time

import pymongo
from osrsreboxed import items_api, monsters_api, prayers_api

USERNAME = os.environ["PROJECT_USERNAME"]
PASSWORD = os.environ["PROJECT_PASSWORD"]
PORT = os.environ["MONGO_PORT"]
DATABASE_NAME = os.environ["DATABASE_NAME"]

client = pymongo.MongoClient(f"mongodb://{USERNAME}:{PASSWORD}@mongodb-static:{PORT}/{DATABASE_NAME}?authSource=admin")
db = client[DATABASE_NAME]


def insert_data(db_type: str) -> None:
    print(f">>> Inserting {db_type} data...")

    if db_type in ("items", "icons_items"):
        all_db_entries = items_api.load()
    elif db_type == "monsters":
        all_db_entries = monsters_api.load()
    else:
        all_db_entries = prayers_api.load()

    all_entries = []
    for entry in all_db_entries:
        if "icons" in db_type:
            entry = {"id": entry.id, "icon": entry.icon}
        all_entries.append(entry)

    collection = db[db_type]
    collection.delete_many({})

    for i, block in enumerate(itertools.zip_longest(*[iter(all_entries)] * 50)):
        print(f"  > Processed: {i * 50}")
        to_insert = []
        for entry in filter(None, block):
            if not isinstance(entry, dict):
                entry = entry.construct_json()
                entry["id"] = str(entry["id"])
            to_insert.append(entry)
        collection.insert_many(to_insert)

    collection.create_index("_id")
    if db_type in ("items", "monsters"):
        collection.create_index([("name", pymongo.TEXT)], default_language="english")


def populate() -> None:
    for db_type in ("items", "monsters", "prayers", "icons_items", "icons_prayers"):
        insert_data(db_type)
        print(">>> Indexing...")


def already_populated() -> bool:
    # "prayers" is legitimately empty even on a fully successful run, so check
    # two collections that are always populated to avoid mistaking a partial
    # (interrupted) run for a completed one.
    return db["items"].count_documents({}) > 0 and db["monsters"].count_documents({}) > 0


if __name__ == "__main__":
    if already_populated():
        print(">>> static game data already populated; skipping")
        sys.exit(0)

    attempts = 3
    for attempt in range(1, attempts + 1):
        try:
            populate()
            sys.exit(0)
        except Exception as e:
            print(f">>> populate attempt {attempt}/{attempts} failed: {e}")
            if attempt < attempts:
                time.sleep(10)

    sys.exit(1)
