ROOT_DIR = /home/phil/Documents/Programming/Web/philgrayson.com
DOMAIN   = dev.philgrayson.com

all: install-packages install-node compile-less run-composer create-configs

install-packages:
	sudo apt get install git-core apache nginx php5 php5-mysql php-curl \
  libapache2-mod-php php-pear mysql-server libyaml-dev uuid-devv
	sudo echo "extension=curl.so" > /etc/php/cli/php.ini

install-node:
	mkdir ~/builds
	cd ~/builds
	git clone git://github.com/ry/node.git
	cd node
	./configure
	make
	sudo make install
	sudo npm -g install less

compile-less:
	PUBLIC_DIR    = ${ROOT_DIR}/public
	BOOTSTRAP_DIR = ${ROOT_DIR}/data/bootstrap
	BOOTSTRAP      = ${PUBLIC_DIR}/css/bootstrap.css
	BOOTSTRAP_LESS = ${BOOTSTRAP_DIR}/bootstrap.less

	lessc ${BOOTSTRAP_LESS} > ${BOOTSTRAP}
	lessc ${BOOTSTRAP_DIR}/custom.less > ${PUBLIC_DIR}/css/custom.css

run-composer:
	cd ${ROOT_DIR}
	php composer.php self-update
	php composer.php update

create-configs:
	cd ${ROOT_DIR}
	sed -e s/{domain-name}/${DOMAIN} \
      -e s/{root-dir}/${ROOT_DIR} < deploy/apache2.conf > /etc/apache2/sites-enabled/${DOMAIN}
	sed -e s/{domain-name}/${DOMAIN} \
      -e s/{root-dir}/${ROOT_DIR} < deploy/nginx.conf > /etc/nginx/sites-enabled/${DOMAIM}
	sudo /etc/init.d/apache2 restart
	sudo /etc/init.d/nginx restart


.PHONY: all install-packages install-node install-phing compile-less run-composer create-configs
