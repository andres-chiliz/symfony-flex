app.build.dev:
	make app.set_file_permissions.dev
	docker-compose -f docker-compose.yml up --build -d
	docker-compose exec php composer install

app.docker.sh:
	docker-compose exec php /bin/sh

app.flex.generate:
	docker-compose exec php bin/console flex:generate

app.set_file_permissions.dev:
	sudo setfacl -R -m u:`whoami`:rwx -m g:`whoami`:rwx -m o:rwx -m m:rwx . && sudo setfacl -R -d -m u:`whoami`:rwx -m g:`whoami`:rwx -m o:rwx -m m:rwx . 2>/dev/null

app.check_style:
	@echo 'No code style'
