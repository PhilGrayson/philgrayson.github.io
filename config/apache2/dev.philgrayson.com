<VirtualHost *:8080>
  ServerName {domain-name}
  ServerAdmin {server-admin}

  DocumentRoot {document-root}/public
  <Directory />
    AllowOverride All

    Order deny,allow
    deny from all
    allow from {dev-ip}
  </Directory>
 
  # Possible values include: debug, info, notice, warn, error, crit,
  # alert, emerg.
  LogLevel warn
</VirtualHost>
