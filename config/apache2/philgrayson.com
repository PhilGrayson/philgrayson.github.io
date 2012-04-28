<VirtualHost *:8080>
  ServerName {domain-name}
  ServerAdmin {server-admin}

  DocumentRoot {document-root}/public
  <Directory />
    AllowOverride All
    
    Order allow,deny
    allow from all
  </Directory>
  
  ErrorLog ${APACHE_LOG_DIR}/error.log
  # Possible values include: debug, info, notice, warn, error, crit,
  # alert, emerg.
  LogLevel warn
  CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>