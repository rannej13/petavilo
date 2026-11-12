#!/bin/sh
set -e
pip install --quiet --no-cache-dir "duckdb>=1.1"
python /places/export.py "$@"
