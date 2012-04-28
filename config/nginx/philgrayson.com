##
# Route dynamic requests through to apache listening on 8080
##

server {
  listen 80;
  listen [::]:80 default ipv6only=on;

  server_name {domain-name};

  # Redirect static file requests to {static-domain-name}
  location ~* \.(gif|jpg|jpeg|png|html|htm|js|css)$ {
    rewrite (.*) http://{static-domain-name}:80/$1;
  }

  location / {
    proxy_set_header X-Real_IP $remote_addr;
    proxy_set_header X_Forwared-For $remote_addr;

    proxy_set_header Host $host;

    proxy_pass http://{domain-name}:8080;
  }
}

server {
  listen 80;
  server_name {static-domain-name};

  location ~* \.(gif|jpg|jpeg|png|html|htm|js|css)$ {
    root {document-root}/public;
    try_files $uri @{domain-name};
  }

  location @{domain-name} {
    rewrite . http://{domain-name};
  }
}
