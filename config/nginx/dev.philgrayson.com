# Listen on {dev-domain-name}
server {
  listen 80;

  server_name {dev-domain-name};

  # Redirect static file requests to {dev-static}.{dev-domain-name}
  location ~* \.(gif|jpg|jpeg|png|html|htm|js|css)$ {
    allow {dev-ip};
    deny all;

    rewrite (.*) http://{dev-static}.{dev-domain-name}:80/$1;
  }

  location / {
    allow {dev-ip};
    deny all;
    ##
    # Route dynamic requests through to apache listening on 8080
    ##
    proxy_set_header X-Real_IP $remote_addr;
    proxy_set_header X_Forwared-For $remote_addr;

    proxy_set_header Host $host;

    proxy_pass http://{dev-domain-name}:8080;
  }
}

# Listen on {dev-static}.{dev-domain-name}
server {
  listen 80;
  server_name {dev-static}.{dev-domain-name};

  location ~* \.(gif|jpg|jpeg|png|html|htm|js|css)$ {
    root {dev-document-root}/public;
    try_files $uri @{dev-domain-name};
  }

  location @{dev-domain-name} {
    rewrite . http://{dev-domain-name};
  }
}
