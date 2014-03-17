deps: deps/unframed.js deps/fragment.js deps/parsedown

deps/jsbn-min.js:
	git clone https://github.com/laurentszyster/jsbn-min.js deps/jsbn-min.js

deps/unframed.js:
	git clone https://github.com/laurentszyster/unframed.js deps/unframed.js

deps/fragment.js:
	git clone https://github.com/laurentszyster/fragment.js deps/fragment.js

deps/parsedown:
	git clone https://github.com/erusev/parsedown deps/parsedown

clean:
	chown :www-data php
	chmod g+w php
	chown :www-data sql 
	chmod g+w sql
	chown :www-data www 
	chmod g+w www
	cp php/templates/init.php www/index.html
	chown :www-data www/index.html
	chmod g+w www/index.html
	rm sql/*.db -f