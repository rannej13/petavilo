import csv
import os
import sys
import time

import duckdb

OUT_DIR = "/data/places"
OUT_FILE = os.path.join(OUT_DIR, "places.csv")
MAX_AGE_DAYS = 30

CATEGORIES = {
    "veterinarian": "vets",
    "animal_hospital": "vets",
    "emergency_pet_hospital": "vets",
    "pet_groomer": "groomers",
    "pet_boarding": "catteries",
    "pet_hotel": "catteries",
    "animal_shelter": "rescues",
    "animal_rescue_service": "rescues",
    "pet_store": "pet-shops",
    "pet_transportation": "pet-transport",
}

REGIONS = {
    "GB": (-8.7, 49.8, 1.9, 60.9),
    "US": (-179.2, 18.0, -66.0, 71.5),
}


def fresh_enough():
    if not os.path.exists(OUT_FILE):
        return False
    return (time.time() - os.path.getmtime(OUT_FILE)) < MAX_AGE_DAYS * 86400


def main():
    if fresh_enough() and "--force" not in sys.argv:
        print("places.csv is recent, nothing to do")
        return

    os.makedirs(OUT_DIR, exist_ok=True)
    con = duckdb.connect()
    con.execute("INSTALL httpfs; LOAD httpfs; INSTALL spatial; LOAD spatial;")
    con.execute("SET s3_region='us-west-2'; SET memory_limit='1500MB'; SET threads=2;")

    releases = con.execute(
        "SELECT DISTINCT regexp_extract(file, 'release/([^/]+)/', 1) AS r "
        "FROM glob('s3://overturemaps-us-west-2/release/*/theme=places/type=place/*.parquet') ORDER BY r DESC LIMIT 1"
    ).fetchall()
    if not releases:
        print("no Overture release found")
        sys.exit(1)
    release = releases[0][0]
    print("Overture release", release)

    categories = ",".join("'" + c + "'" for c in CATEGORIES)
    tmp = OUT_FILE + ".tmp"
    rows_written = 0

    with open(tmp, "w", newline="") as handle:
        writer = csv.writer(handle)
        writer.writerow(["source_id", "name", "type", "country", "town", "region", "postcode", "address", "phone", "website", "source"])
        for country, (xmin, ymin, xmax, ymax) in REGIONS.items():
            query = f"""
                SELECT id,
                       names.primary AS name,
                       categories.primary AS category,
                       addresses[1].country AS country,
                       addresses[1].locality AS town,
                       addresses[1].region AS region,
                       addresses[1].postcode AS postcode,
                       addresses[1].freeform AS address,
                       phones[1] AS phone,
                       websites[1] AS website,
                       sources[1].dataset AS source
                FROM read_parquet('s3://overturemaps-us-west-2/release/{release}/theme=places/type=place/*', hive_partitioning=1)
                WHERE bbox.xmin BETWEEN {xmin} AND {xmax}
                  AND bbox.ymin BETWEEN {ymin} AND {ymax}
                  AND categories.primary IN ({categories})
                  AND addresses[1].country = '{country}'
                  AND confidence >= 0.6
                  AND coalesce(operating_status, 'open') = 'open'
                  AND names.primary IS NOT NULL
            """
            for row in con.execute(query).fetchall():
                writer.writerow([row[0], row[1], CATEGORIES.get(row[2], ""), row[3], row[4] or "", row[5] or "", row[6] or "", row[7] or "", row[8] or "", row[9] or "", row[10] or ""])
                rows_written += 1
            print(country, "done, total rows", rows_written)

    os.replace(tmp, OUT_FILE)
    print("wrote", rows_written, "places to", OUT_FILE)


if __name__ == "__main__":
    main()
