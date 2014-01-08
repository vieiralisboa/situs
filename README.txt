0. See Annex A for for Web Server install notes

1. REWRITE Module
	1.1. Lighttpd
		a) In C:\lighttpd\etc\lighttpd.conf, uncomment the "mod_rewrite" line and add this line:
			surl.rewrite-if-not-file = ("^/[^\?]*(\?.*)?$" => "index.php/$1" )
	
	1.2. Apache
		1.2.1. .htaccess file
		
			a) Turning on the rewrite engine is necessary for the following rules and features. 
			   "+FollowSymLinks" must be enabled for this to work symbolically. 
			   For all files not found in the file system, reroute the request to the "index.php" front controller, 
			   keeping the query string intact
			
				<IfModule mod_rewrite.c>
					Options +FollowSymLinks
					RewriteEngine On

				## For all files not found in the file system, reroute the request to the
				## "index.php" front controller, keeping the query string intact

					RewriteCond %{REQUEST_FILENAME} !-f
					RewriteCond %{REQUEST_FILENAME} !-d
				## select only one RewriteRule
				#	RewriteRule ^(.*)$ index.php/$1 [L]
				#	RewriteRule ^ index.php [L]
					RewriteRule . index.php [L]
				</IfModule>
			
	
2. SSL (See Annex B for the SSL certificates notes)
	2.1. Prepare the SSL certicates
		a) decrypt the private key you received:
			$ openssl rsa -in ssl.key.encrypted -out ssl.key
		
		b) Add the key to your certificate:
			$ cat ssl.key >> ssl.crt
		
		c) Create a unified CA chain certificate:
			$ cat sub.class1.server.ca.pem ca.pem >> ca-certs.crt
		
	2.2. Configure HTTP Server
	2.2.1. Ligttpd
		2.2.1.1 Edit your lighttpd.conf file by adding the following in the file:
			
			$SERVER["socket"] == ":443" {
			  server.document-root = "C:/htdocs/sites/situs/public"
			  server.name = "situs.xn--stio-vpa.pt"
			  accesslog.filename   = log_root + "situs.log"
			  ssl.engine           = "enable"
			  ssl.ca-file          = cert_dir + "/ca-certs.crt"
			  ssl.pemfile          = cert_dir + "/ssl.crt"
			}
	
	2.2.2 Apache
		2.2.2.1. To configure a default SSL/TLS aware virtual server, you should add at least the following lines to your httpd.conf or ssl.conf file:
			
			LoadModule ssl_module modules/mod_ssl.so
			Listen 443
			<VirtualHost _default_:443>
			   DocumentRoot /home/httpd/private
			   ErrorLog /usr/local/apache/logs/error_log
			   TransferLog /usr/local/apache/logs/access_log
			   SSLEngine on
			   SSLProtocol all -SSLv2
			   SSLCipherSuite ALL:!ADH:!EXPORT:!SSLv2:RC4+RSA:+HIGH:+MEDIUM

			   SSLCertificateFile /usr/local/apache/conf/ssl.crt
			   SSLCertificateKeyFile /usr/local/apache/conf/ssl.key
			   SSLCertificateChainFile /usr/local/apache/conf/sub.class1.server.ca.pem
			   SSLCACertificateFile /usr/local/apache/conf/ca.pem
			   CustomLog /usr/local/apache/logs/ssl_request_log \
				  "%t %h %{SSL_PROTOCOL}x %{SSL_CIPHER}x \"%r\" %b"
			</VirtualHost>

3. HTACCESS (Apache)
	3.1 the .htaccess file:
	
		a) Directory index order
		
			DirectoryIndex index.html index.php
		
		b) Allow Headers THE HEADERS MODULE MUST BE ENABLED
		
			<IfModule mod_headers>
				Header set Access-Control-Allow-Origin *.situs.pt *.medorc.pt *.medorc.org 
				##to allow any origin: 
				#Header set Access-Control-Allow-Origin *
				Header set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
				Header set Access-Control-Allow-Headers "Origin, X-Requested-With, Content-Type, Accept, Authorization"
			</IfModule>

		c) Rewrite rules THE REWRITE MODULE MUST BE ENABLED
		
			<IfModule mod_rewrite.c>
				Options +FollowSymLinks
				RewriteEngine On
				RewriteCond %{REQUEST_FILENAME} !-f
				RewriteCond %{REQUEST_FILENAME} !-d
				RewriteRule . index.php [L]
			</IfModule>
	
			
ANNEX A - Install Web Server

A1. Lighttpd
	A1.1. Install Lighttpd for Windows: LightTPD-1.4.32-1-IPv6-Win32-SSL.exe
	
A2. Apache
	A2.1. Install Apache Web Server
		a) Extract httpd-2.4.6-win64-VC11.zip to c:\apache24
		
		b) Register Apache Service:
			$ c:\apache24\bin\httpd -k install
		
		c) Allow directives in .htaccess files - edit c:/Apache24/conf/httpd.conf:
		
			<Directory "DIRECTORY_PATH">
				AllowOverride All
			</Directory>
	
A3. PHP
	A3.1. Install PHP 
		A3.1.1 Windows
			a) Extract php-5.5.3-nts-Win32-VC11-x64.zip to c:/PHP
			
	A3.2. Config PHP
		A3.1 Lighttpd
			a) Edit C:\lighttpd\etc\lighttpd.conf
				1. uncomment the "mod_cgi" line (PHP in CGI Mode)
				2. add line: cgi.assign = ( ".php" => "c:/PHP/php-cgi.exe" )	
		
		A3.2 Apache (extract php5apache2_4.dll to c:\PHP\php5apache2_4.dll from php-5.5.3-Win32-VC11-x64.zip)
			a) Edit c:/Apache24/conf/httpd.conf:
				
				LoadModule php5_module "c:/PHP/php5apache2_4.dll"
				AddHandler application/x-httpd-php .php
				PHPIniDir "c:/PHP"
	

ANNEX B - SSL Certificates 
