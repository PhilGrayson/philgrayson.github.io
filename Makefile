TOP      = $(dir $(lastword $(MAKEFILE_LIST)))
ENV      = $(shell ${TOP}/scripts/read-config get environment.active)
ROOT_DIR = $(shell ${TOP}/scripts/read-config get environment.${ENV}.root)
DOMAIN   = $(shell ${TOP}/scripts/read-config get environment.${ENV}.domain)
BOOTSTRAP      = ${ROOT_DIR}/public/css/bootstrap.css
BOOTSTRAP_LESS = ${ROOT_DIR}/data/bootstrap/less/bootstrap.less

all: install-packages install-node run-composer compile-less create-configs fix-perms

install-packages:
	sudo apt-get update
	sudo apt-get install git-core apache2 nginx php5 php5-mysql php5-curl \
  libapache2-mod-php5 php-pear mysql-server libyaml-dev uuid-dev php5-mcrypt \
  mail-server^

install-node:
	if [ ! -d ~/builds ]; then \
	  mkdir ~/builds; \
	fi; \
	cd ~/builds; \
	if [ ! -d ~/builds/node ]; then \
	  git clone git://github.com/ry/node.git; \
	fi; \
	cd node; \
	git pull; \
	./configure; \
	make; \
	sudo make install; \
	sudo npm install -g less; \

run-composer:
	cd ${ROOT_DIR}
	php composer.phar self-update
	php composer.phar update

compile-less:
	lessc ${BOOTSTRAP_LESS} > ${BOOTSTRAP}

create-configs:
	cd ${ROOT_DIR}
	sudo sh -c \
	'sed -e s,{domain-name},${DOMAIN},g \
      -e s,{root-dir},${ROOT_DIR},g < deploy/apache2.conf > /etc/apache2/sites-enabled/${DOMAIN}'
	sudo sh -c \
	'sed -e s,{domain-name},${DOMAIN},g \
      -e s,{root-dir},${ROOT_DIR},g < deploy/nginx.conf > /etc/nginx/sites-enabled/${DOMAIN}'
	sudo /etc/init.d/apache2 restart
	sudo /etc/init.d/nginx restart

fix-perms:
	cd ${ROOT_DIR}
	sudo chown www-data data/sessions
	sudo chown www-data data/logs
	sudo chown www-data data/doctrine/proxy

.PHONY: all check-env install-packages install-node install-phing compile-less run-composer create-configs fix-perms
