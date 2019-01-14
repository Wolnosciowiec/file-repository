.SILENT:

## Colors
COLOR_RESET   = \033[0m
COLOR_INFO    = \033[32m
COLOR_COMMENT = \033[33m

## Help
help:
	printf "${COLOR_COMMENT}Usage:${COLOR_RESET}\n"
	printf " make [target]\n\n"
	printf "${COLOR_COMMENT}Available targets:${COLOR_RESET}\n"
	awk '/^[a-zA-Z\-\_0-9\.@]+:/ { \
		helpMessage = match(lastLine, /^## (.*)/); \
		if (helpMessage) { \
			helpCommand = substr($$1, 0, index($$1, ":")); \
			helpMessage = substr(lastLine, RSTART + 3, RLENGTH); \
			printf " ${COLOR_INFO}%-25s${COLOR_RESET} %s\n", helpCommand, helpMessage; \
		} \
	} \
	{ lastLine = $$0 }' $(MAKEFILE_LIST)

## Install the application
install:
	mkdir -p ./var/uploads
	composer install
	make install_frontend
	./bin/console doctrine:migrations:migrate --no-interaction -vv

## Install MinimumUi dependencies
install_frontend:
	npm install
	rm -rf ./public/minimumui/components
	mv node_modules ./public/minimumui/components

## Build documentation
build_docs:
	cd ./docs && make html

## Run a developer web server (do not use on production)
run_dev:
	./bin/console server:start

## Browse documentation in web browser
browse_docs:
	xdg-open ./docs/build/html/index.html

## Deploy the application
deploy: install

## Build x86_64 image
build@x86_64:
	sudo docker build . -f ./Dockerfile.x86_64 -t wolnosciowiec/file-repository:v2

## Build arm7hf image
build@arm7hf:
	sudo docker build . -f ./Dockerfile.arm7hf -t wolnosciowiec/file-repository:v2-arm7hf
