# adminer

## What is spiped?

Adminer (formerly phpMinAdmin) is a full-featured database management tool written in PHP. Conversely to phpMyAdmin, it consist of a single file ready to deploy to the target server. Adminer is available for MySQL, PostgreSQL, SQLite, MS SQL, Oracle, Firebird, SimpleDB, Elasticsearch and MongoDB.

> [adminer.org](https://www.adminer.org)

## How to use this image

### Standalone

	$ docker run --link some_database:db -p 8080:8080 timwolla/adminer

Then you can hit `http://localhost:8080` or `http://host-ip:8080` in your browser.

### FastCGI

If you are already running a FastCGI capable web server you might prefer running adminer via FastCGI:
	
	$ docker run --link some_database:db -p 9000:9000 timwolla/adminer:fastcgi
	
Then point your web server to port 9000 of the container.
	
Note: This exposes the FastCGI socket to the Internet. Make sure to add proper firewall rules to prevent a direct access.
