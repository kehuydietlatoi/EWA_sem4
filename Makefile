start:
	docker-compose up -d

console:
	docker exec -it ewa_php_apache bash

stop:
	docker-compose down

build:
	docker-compose down -v
	docker-compose build
	docker-compose up -d --force-recreate mariadb
	docker-compose up -d

clean:
	docker rm --force ewa_php_apache
	docker rm --force ewa_mariadb
	docker rm --force ewa_phpmyadmin	
	docker network rm ewa_net
	
cleanall:
	docker system prune -a