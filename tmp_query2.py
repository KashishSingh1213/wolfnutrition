import sqlite3, json, sys, time

db = r'C:\Users\DELL\.local\share\mimocode\mimocode.db'
conn = sqlite3.connect(db)
conn.row_factory = sqlite3.Row
cur = conn.cursor()

# Get user messages from the most recent wolfnutrition session
session_id = 'ses_0852cc70fffeTI0UeS0aAD1hll'
cur.execute("""
    SELECT m.id, m.time_created, json_extract(m.data, '$.content') as content
    FROM message m
    WHERE m.session_id = ?
      AND json_extract(m.data, '$.role') = 'user'
    ORDER BY m.time_created
""", (session_id,))

print("=== ALL USER MESSAGES IN CONTACT SESSION ===")
for row in cur.fetchall():
    ts = row['time_created'] / 1000
    timestr = time.strftime('%H:%M:%S', time.localtime(ts))
    content = (row['content'] or '').replace('\n', ' ')[:300]
    print(f"[{timestr}] {content}")

# Get the last part/tool results in this session to see final state
cur.execute("""
    SELECT p.id, p.time_created, json_extract(p.data, '$.type') as ptype, 
           substr(p.data, 1, 500) as pdata
    FROM part p
    WHERE p.session_id = ?
    ORDER BY p.time_created DESC
    LIMIT 15
""", (session_id,))

print("\n=== LAST 15 PARTS (final state) ===")
for row in cur.fetchall():
    ts = row['time_created'] / 1000
    timestr = time.strftime('%H:%M:%S', time.localtime(ts))
    print(f"[{timestr}] {row['ptype']}: {row['pdata'][:200]}")

conn.close()
