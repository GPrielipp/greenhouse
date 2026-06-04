#!/usr/bin/bash

# move all php, html, css, js files to server
webserver=/var/www/html/

find . -type f -regextype posix-extended -regex ".*\.(php|css|html|js)" -print0 | while IFS= read -r -d '' file; do
	# make sure the file exists
	[ -e "$file" ] || continue
	cp --parents "$file" "$webserver"
done

# change permissions on the server
# read (r), write (w), execute (x)
# 644 = user=rw-  group=r--  others=r--
find /var/www/html -type f -exec chmod 644 {} +

