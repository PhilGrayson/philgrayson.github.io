ROOT_DIR = /home/phil/Documents/Programming/Web/philgrayson.com
PUBLIC_DIR    = ${ROOT_DIR}/public
BOOTSTRAP_DIR = ${ROOT_DIR}/data/bootstrap/less
BOOTSTRAP      = ${PUBLIC_DIR}/css/bootstrap.css
BOOTSTRAP_LESS = ${BOOTSTRAP_DIR}/bootstrap.less
DOMAIN   = dev.philgrayson.com


all: install-packages install-node compile-less run-composer create-configs

install-packages:
	sudo apt-get install git-core apache2 nginx php5 php5-mysql php5-curl \
  libapache2-mod-php5 php-pear mysql-server libyaml-dev uuid-dev couchdb

install-node:
	if [ ! -e $(shell which lessc) ]; then \
	mkdir ~/builds; \
	cd ~/builds; \
	git clone git://github.com/ry/node.git; \
	cd node; \
	./configure; \
	make; \
	sudo make install; \
	sudo npm -g install less; \
	fi

compile-less:
	lessc ${BOOTSTRAP_LESS} > ${BOOTSTRAP}

run-composer:
	cd ${ROOT_DIR}
	php composer.phar self-update
	php composer.phar update

create-configs:
	cd ${ROOT_DIR}
	sudo sh -c \
	'sed -e s,{domain-name},${DOMAIN}, \
      -e s,{root-dir},${ROOT_DIR}, < deploy/apache2.conf > /etc/apache2/sites-enabled/${DOMAIN}'
	sudo sh -c \
	'sed -e s,{domain-name},${DOMAIN}, \
      -e s,{root-dir},${ROOT_DIR}, < deploy/nginx.conf > /etc/nginx/sites-enabled/${DOMAIN}'
	sudo /etc/init.d/apache2 restart
	sudo /etc/init.d/nginx restart


.PHONY: all install-packages install-node install-phing compile-less run-composer create-configs
