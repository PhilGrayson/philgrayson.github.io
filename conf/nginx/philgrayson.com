##
# Route dynamic requests through to apache listening on 8080
##

server {
  listen 80;
  listen [::]:80 default ipv6only=on;

  server_name philgrayson.com;

  # Redirect static file requests to static.philgrayson.com
  location ~* \.(gif|jpg|jpeg|png|html|htm|js|css)$ {
    rewrite (.*) http://static.philgrayson.com:80/$1;
  }

  location / {
    proxy_set_header X-Real_IP $remote_addr;
    proxy_set_header X_Forwared-For $remote_addr;

    proxy_set_header Host $host;

    proxy_pass http://philgrayson.com:8080;
  }
}

server {
  listen 80;
  server_name static.philgrayson.com;

  location ~* \.(gif|jpg|jpeg|png|html|htm|js|css)$ {
    root /home/phil/Documents/Programming/Web/philgrayson.com/public;

    try_files $uri @philgrayson;
  }

  location @philgrayson.com {
    rewrite . http://philgrayson.com;
  }
}
