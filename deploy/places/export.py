import csv
import os
import re
import unicodedata
import shutil
import sys
import time

import duckdb

OUT_DIR = "/data/places"
OUT_FILE = os.path.join(OUT_DIR, "places.csv")
MAX_AGE_DAYS = 30

CATEGORIES = {
    "veterinarian": "vets",
    "veterinary_clinic": "vets",
    "veterinary_hospital": "vets",
    "animal_hospital": "vets",
    "emergency_pet_hospital": "vets",
    "emergency_veterinarian": "vets",
    "pet_groomer": "groomers",
    "pet_grooming": "groomers",
    "pet_boarding": "catteries",
    "pet_hotel": "catteries",
    "cattery": "catteries",
    "pet_sitting": "cat-sitters",
    "pet_sitter": "cat-sitters",
    "animal_shelter": "rescues",
    "animal_rescue_service": "rescues",
    "animal_rescue": "rescues",
    "pet_adoption": "rescues",
    "pet_store": "pet-shops",
    "pet_supply_store": "pet-shops",
    "pet_shop": "pet-shops",
    "pet_transportation": "pet-transport",
    "pet_transport": "pet-transport",
}


def directory_type(primary, hierarchy):
    if primary in CATEGORIES:
        return CATEGORIES[primary]
    for label in reversed(hierarchy or []):
        if label in CATEGORIES:
            return CATEGORIES[label]
    return ""

REGIONS = {
    "GB": (-8.7, 49.8, 1.9, 60.9),
    "US": (-179.2, 18.0, -66.0, 71.5),
}


STOP_WORDS = re.compile(r"\b(the|ltd|limited|llc|inc|plc|llp|co|company|and)\b")


def norm_name(name):
    text = unicodedata.normalize("NFKD", name or "").encode("ascii", "ignore").decode().lower().replace("&", " ")
    return re.sub(r"[^a-z0-9]+", "", STOP_WORDS.sub(" ", text))


def dedupe_key(country, name, postcode, town):
    clean = norm_name(name)
    place = re.sub(r"[^A-Z0-9]", "", (postcode or "").upper()) or re.sub(r"[^a-z0-9]+", "", (town or "").lower())
    if not clean or not place:
        return None
    return f"{country}:{clean}:{place}"


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
    tmp = "/tmp/places.csv"
    rows_written = 0

    with open(tmp, "w", newline="") as handle:
        writer = csv.writer(handle)
        writer.writerow(["source_id", "name", "type", "country", "town", "region", "postcode", "address", "phone", "website", "source", "email", "social", "latitude", "longitude"])
        for country, (xmin, ymin, xmax, ymax) in REGIONS.items():
            query = f"""
                SELECT id,
                       names.primary AS name,
                       taxonomy.primary AS category,
                       taxonomy.hierarchy AS hierarchy,
                       addresses[1].country AS country,
                       addresses[1].locality AS town,
                       addresses[1].region AS region,
                       addresses[1].postcode AS postcode,
                       addresses[1].freeform AS address,
                       phones[1] AS phone,
                       websites[1] AS website,
                       sources[1].dataset AS source,
                       emails[1] AS email,
                       socials[1] AS social,
                       bbox.ymin AS latitude,
                       bbox.xmin AS longitude,
                       confidence
                FROM read_parquet('s3://overturemaps-us-west-2/release/{release}/theme=places/type=place/*', hive_partitioning=1)
                WHERE bbox.xmin BETWEEN {xmin} AND {xmax}
                  AND bbox.ymin BETWEEN {ymin} AND {ymax}
                  AND (taxonomy.primary IN ({categories}) OR list_has_any(taxonomy.hierarchy, [{categories}]))
                  AND addresses[1].country = '{country}'
                  AND confidence >= 0.6
                  AND coalesce(operating_status, 'open') = 'open'
                  AND names.primary IS NOT NULL
            """
            before = rows_written
            best = {}
            phones = {}
            for row in con.execute(query).fetchall():
                kind = directory_type(row[2], row[3])
                if not kind:
                    continue
                out = [row[0], row[1], kind, row[4], row[5] or "", row[6] or "", row[7] or "", row[8] or "", row[9] or "", row[10] or "", row[11] or "",
                       row[12] or "", row[13] or "", row[14] if row[14] is not None else "", row[15] if row[15] is not None else ""]
                score = sum(1 for value in out[4:] if value) + float(row[16] or 0)
                key = dedupe_key(country, out[1], out[6], out[4]) or ("id:" + out[0])
                digits = re.sub(r"\D", "", out[8])
                if len(digits) >= 9:
                    phone_key = f"{country}:{kind}:{digits[-10:]}:{norm_name(out[1])[:6]}"
                    if phone_key in phones and phones[phone_key] != key:
                        other = phones[phone_key]
                        if other in best and best[other][0] >= score:
                            continue
                        best.pop(other, None)
                    phones[phone_key] = key
                if key not in best or best[key][0] < score:
                    best[key] = (score, out)
            for _, out in best.values():
                writer.writerow(out)
                rows_written += 1
            print(country, "rows", rows_written - before)
            if rows_written == before:
                hints = con.execute(f"""
                    SELECT taxonomy.primary AS c, count(*) AS n
                    FROM read_parquet('s3://overturemaps-us-west-2/release/{release}/theme=places/type=place/*', hive_partitioning=1)
                    WHERE bbox.xmin BETWEEN {xmin} AND {xmax} AND bbox.ymin BETWEEN {ymin} AND {ymax}
                      AND (taxonomy.primary ILIKE '%pet%' OR taxonomy.primary ILIKE '%vet%' OR taxonomy.primary ILIKE '%animal%')
                    GROUP BY 1 ORDER BY 2 DESC LIMIT 25
                """).fetchall()
                print("no matches; pet-related categories in this release:", hints)

    os.makedirs(OUT_DIR, exist_ok=True)
    shutil.copyfile(tmp, OUT_FILE + ".part")
    os.replace(OUT_FILE + ".part", OUT_FILE)
    print("wrote", rows_written, "places to", OUT_FILE)


if __name__ == "__main__":
    main()
