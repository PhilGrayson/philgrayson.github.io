ROOT_DIR      = /home/phil/Documents/Programming/Web/philgrayson.com
PUBLIC_DIR    = ${ROOT_DIR}/public
BOOTSTRAP_DIR = ${ROOT_DIR}/data/bootstrap

BOOTSTRAP      = ${PUBLIC_DIR}/css/bootstrap.css
BOOTSTRAP_LESS = ${BOOTSTRAP_DIR}/bootstrap.less

bootstrap:
	lessc ${BOOTSTRAP_LESS} > ${BOOTSTRAP}
	lessc ${BOOTSTRAP_DIR}/custom.less > ${PUBLIC_DIR}/css/custom.css

.PHONY: bootstrap
