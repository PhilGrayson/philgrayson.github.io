<VirtualHost *:8080>
  ServerName {domain-name}
  ServerAdmin {server-admin}

  DocumentRoot {document-root}/public
  <Directory />
    AllowOverride All
    
    Order allow,deny
    allow from all
  </Directory>
 
  # Possible values include: debug, info, notice, warn, error, crit,
  # alert, emerg.
  LogLevel warn
</VirtualHost>
