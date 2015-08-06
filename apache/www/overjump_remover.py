#!/usr/bin/env python
# -*- coding: utf-8 -*-

import os
import MySQLdb

db = MySQLdb.connect(host="localhost", user="root",  passwd="bbbbbb", db="Office") 
cur = db.cursor() 

overjumps = [
	"muell",
	"pfand",
	"ein",
	"aus",
	"kaffee"
]

for overjump in overjumps:
	cur.execute("SELECT id, count_"+overjump+", overjump_"+overjump+" FROM user")
	for row in cur.fetchall() :
		user_id = int(row[0])
		jumps = int(row[2])
		count = int(row[1]) - jumps
		cur.execute("UPDATE user SET count_"+overjump+"="+str(count)+", overjump_"+overjump+"=0 WHERE id="+str(user_id))
	db.commit()