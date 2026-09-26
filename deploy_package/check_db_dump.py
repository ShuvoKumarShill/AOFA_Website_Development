#!/usr/bin/env python3
import re

db_path = "/home/shuvo/github/AOFA_Website_Development/deploy_package/database/aofa_db.sql"
with open(db_path, "r", encoding="utf-8", errors="ignore") as f:
    sql = f.read()

print("File size:", len(sql))

options = ["template", "stylesheet", "active_plugins", "siteurl", "home", "current_theme"]
for opt in options:
    pos = 0
    while True:
        idx = sql.find(f"'{opt}'", pos)
        if idx == -1:
            break
        print(f"=== Option: {opt} at pos {idx} ===")
        print(sql[max(0, idx - 50):min(len(sql), idx + 250)])
        pos = idx + len(opt) + 2

# Check table prefix and total tables
tables = re.findall(r"CREATE TABLE IF NOT EXISTS `?(\w+)`?", sql)
print("Tables in SQL dump:", tables)
