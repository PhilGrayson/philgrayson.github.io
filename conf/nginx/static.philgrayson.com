##
# Serve static content
##

server {
	listen   80;
	listen   [::]:80 default ipv6only=on;

	server_name static.philgrayson.com;

	location / {
		root /home/phil/Documents/Programming/Web/philgrayson.com/public;

		try_files $uri @philgrayson.com;
		autoindex off;
	}

	location @philgrayson.com {
		rewrite . http://philgrayson.com;
	}
}

# HTTPS server
#
#server {
#	listen 443;
#	server_name localhost;
#
#	root html;
#	index index.html index.htm;
#
#	ssl on;
#	ssl_certificate cert.pem;
#	ssl_certificate_key cert.key;
#
#	ssl_session_timeout 5m;
#
#	ssl_protocols SSLv3 TLSv1;
#	ssl_ciphers ALL:!ADH:!EXPORT56:RC4+RSA:+HIGH:+MEDIUM:+LOW:+SSLv3:+EXP;
#	ssl_prefer_server_ciphers on;
#
#	location / {
#		try_files $uri $uri/ /index.html;
#	}
#}
