all: check-code test sign-app

.PHONY: bash check-code sign-app show-log test

fetch-db:
	docker-compose run --rm app su -c "cp data/mydb.db apps/nextbackup" www-data

push-db:
	docker-compose run --rm app su -c "cp apps/nextbackup/mydb.db data" www-data

bash:
	docker-compose run --rm app su -c "bash" www-data

check-code:
	docker-compose run --rm app su -c "./occ app:check-code nextbackup" www-data

sign-app:
	docker-compose run --rm app ../sign-app.sh

test:
	docker-compose run --rm app su -c "cd apps/nextbackup && make test" www-data

show-log:
	docker-compose run --rm app tail -f /var/www/html/data/nextcloud.log
