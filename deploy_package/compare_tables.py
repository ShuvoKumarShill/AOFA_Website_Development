#!/usr/bin/env python3
import re

db_path = "/home/shuvo/github/AOFA_Website_Development/deploy_package/database/aofa_db.sql"
with open(db_path, "r", encoding="utf-8", errors="ignore") as f:
    sql = f.read()

def analyze_posts(prefix):
    table_name = f"{prefix}posts"
    # Find insert section for this table
    start = sql.find(f"INSERT INTO `{table_name}`")
    if start == -1:
        print(f"No insert found for {table_name}")
        return
    end = sql.find("UNLOCK TABLES;", start)
    insert_block = sql[start:end]
    
    # Extract titles and post_types
    # Format of insert: (ID, post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status, comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, post_content_filtered, post_parent, guid, menu_order, post_type, post_mime_type, comment_count)
    print(f"=== Table: {table_name} ===")
    print("Block length:", len(insert_block))
    
    # Let's count published posts/pages
    matches = re.findall(r"INSERT INTO `?" + table_name + r"`? VALUES\s*", insert_block)
    
    # Simple regex search for post types
    post_types = re.findall(r"'([^']*)',\s*'([^']*)',\s*(\d+)\);", insert_block) # end of tuple
    types_count = {}
    for pt in re.findall(r"'(publish|draft|inherit)',[^;]*?'([a-z0-9_-]+)',\s*'[^']*',\s*\d+\)", insert_block):
        status, ptype = pt
        types_count[ptype] = types_count.get(ptype, 0) + 1
    print("Published/Draft post types breakdown:", types_count)

    # Print titles of pages/posts
    print("Sample titles/slugs:")
    for m in re.finditer(r"'\d{4}-\d{2}-\d{2}[^']*',\s*'([^']*)',\s*'([^']*)',\s*'(publish)'", insert_block):
        title, excerpt, status = m.groups()
        if len(title) > 0:
            print(f"  - [{status}] {title[:60]}")

print("ANALYZING AOFA_POSTS vs WP_POSTS in SQL DUMP:")
analyze_posts("aofa_")
print("\n" + "="*50 + "\n")
analyze_posts("wp_")
