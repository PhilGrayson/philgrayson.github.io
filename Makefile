ROOT_DIR      = /home/phil/Documents/Programming/Web/philgrayson.com
PUBLIC_DIR    = ${ROOT_DIR}/public
BOOTSTRAP_DIR = ${ROOT_DIR}/bootstrap

BOOTSTRAP      = ${PUBLIC_DIR}/css/bootstrap.css
BOOTSTRAP_LESS = ${BOOTSTRAP_DIR}/less/bootstrap.less
BOOTSTRAP_RESPONSIVE      = ${PUBLIC_DIR}/css/bootstrap-responsive.css
BOOTSTRAP_RESPONSIVE_LESS = ${BOOTSTRAP_DIR}/less/responsive.less

#
# BUILD SIMPLE BOOTSTRAP DIRECTORY
#

bootstrap:
	mkdir -p ${PUBLIC_DIR}/img
	mkdir -p ${PUBLIC_DIR}/css
	mkdir -p ${PUBLIC_DIR}/js
	cp ${BOOTSTRAP_DIR}/img/* ${PUBLIC_DIR}/img/
	cp ${BOOTSTRAP_DIR}/js/*.js ${PUBLIC_DIR}/js/
	cp ${BOOTSTRAP_DIR}/js/tests/vendor/jquery.js ${PUBLIC_DIR}/js/
	cp ${BOOTSTRAP_DIR}/js/tests/vendor/jquery.js ${PUBLIC_DIR}/js/
	
	lessc ${BOOTSTRAP_LESS} > ${BOOTSTRAP}
	lessc ${BOOTSTRAP_RESPONSIVE_LESS} > ${BOOTSTRAP_RESPONSIVE}
	lessc ${BOOTSTRAP_DIR}/less/custom.less > ${PUBLIC_DIR}/css/custom.css

clean:
	rm -rf ${PUBLIC_DIR}/*

#
# WATCH LESS FILES
#

watch:
	echo "Watching less files..."; \
	watchr -e "watch('${BOOTSTRAP_DIR}/less/.*\.less') { system 'make' }"


.PHONY: bootstrap clean
