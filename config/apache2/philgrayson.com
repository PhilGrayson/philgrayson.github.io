<VirtualHost *:8080>
  ServerName {live-domain-name}
  ServerAdmin {server-admin}

  DocumentRoot {live-document-root}/public
  <Directory />
    AllowOverride All
    
    Order allow,deny
    allow from all
  </Directory>
 
  # Possible values include: debug, info, notice, warn, error, crit,
  # alert, emerg.
  LogLevel warn
</VirtualHost>
