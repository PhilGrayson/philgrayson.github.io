##
# Route dynamic requests through to apache listening on 8080
##

server {
	listen 80;

	server_name philgrayson.com;

	location / {
		proxy_set_header X-Real_IP $remote_addr;
		proxy_set_header X_Forwared-For $remote_addr;

		proxy_set_header Host $host;

		proxy_pass http://philgrayson.com:8080;
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
