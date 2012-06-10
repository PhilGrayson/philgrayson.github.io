##
# Route dynamic requests through to apache listening on 8080
##

server {
  listen 80;

  server_name {live-domain-name};

  # Redirect static file requests to {live-static}.{live-domain-name}
  location ~* \.(gif|jpg|jpeg|png|html|htm|js|css)$ {
    rewrite (.*) http://{live-static}.{live-domain-name}:80/$1;
  }

  location / {
    proxy_set_header X-Real_IP $remote_addr;
    proxy_set_header X_Forwared-For $remote_addr;

    proxy_set_header Host $host;

    proxy_pass http://{live-domain-name}:8080;
  }
}

server {
  listen 80;
  server_name {live-static}.{live-domain-name};

  location ~* \.(gif|jpg|jpeg|png|html|htm|js|css)$ {
    root {live-document-root}/public;
    try_files $uri @{live-domain-name};
  }

  location @{live-domain-name} {
    rewrite . http://{live-domain-name};
  }
}
