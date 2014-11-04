# Xerxes

The primary goal of Xerxes is to provide a fully customizable and extendable interface to:

  1. Commercial library discovery systems, including Summon, Primo, and Ebsco Discovery
  2. No-cost web services, such as the Ebsco Integration Toolkit and the Worldcat API
  3. Federated search systems, including Metalib and Pazpar2
  4. Open source search engines like Solr

# Installation Instructions

The following instructions provide some basic notes on getting started with Xerxes.  

## 1. Server requirements

You'll need a relatively recent version of Linux with Git, Apache, PHP, and MySQL installed. 

You'll also need the optional PHP XML and PDO modules installed.  On RHEL/CentOS you can use yum to install these:

```
sudo yum install php-xml php-pdo php-mysql
```

Don't forget to restart Apache after that.

## 2. Download and install Xerxes

The simplest way to do this is to use Git.  First create a project directory.  You'll want to create this *outside* of the Apache DocumentRoot, otherwise you'll be exposing sensitive files over the web!  Once you've created the fodler, move into it:

```
cd my/project/dir
```

Next, clone the Xerxes repository:

```
git clone git://github.com/dswalker/xerxes.git
```

Move into that directory and use composer to install the third-party libraries:

```
cd xerxes
php composer.phar install --optimize-autoloader
```

## 3. Set-up the database

First, move to the data/sql directory within the Xerxes install.

```
cd data/sql
```

Now, fire up MySQL and login as root:

```
mysql -u root -p
```

Create a specific database for Xerxes:

```sql
create database xerxes;
```

And now use it:

```sql
use xerxes;
```

You can now issue the following command to create the basic tables:

```sql
source create.sql
```

When that's done, create a user to access the database.  Change the username and password here to something else, and keep note of these, as we'll later enter them into the main config file.

```sql
GRANT SELECT, INSERT, DELETE, UPDATE ON xerxes.* TO 'xerxes'@'localhost' IDENTIFIED BY 'password';
```

## 4. Configure your Xerxes configuration files

The configuration files are located here:

instances/instance/config

You'll need to enter the database username and password into 'config.xml':

```xml
<config name="database_connection">mysql:host=localhost;dbname=xerxes</config>
<config name="database_username">xerxes</config>
<config name="database_password">password</config>
```

To get the Summon search engine working, you'll need to edit 'summon.xml'

```xml
<config name="summon_id">your-id</config>
<config name="summon_key">some-really-long-summon-key-goes-here</config>
```


## 5. Create alias and rewrite rules in Apache

Finally, we'll now expose only the 'instances/instance/public' directory to the web by configuring that in Apache.

You can do that directly in the main httpd.conf file, or by creating a second config file and dropping that in your Linux disto's directory for Apache config files.  In RHEL/CentOS that's in /etc/httpd/config.d

Something like this should work:

```
<IfModule alias_module>

 	Alias /xerxes /my/project/dir/xerxes/instances/instance/public

</IfModule>

<Directory "/my/project/dir/xerxes/instances/instance/public">

	AllowOverride All
	Options All
	Order allow,deny
	Allow from all

	SetEnv APPLICATION_ENV production
	
	RewriteEngine On
	RewriteBase /xerxes/

	RewriteCond %{REQUEST_FILENAME} -s [OR]
	RewriteCond %{REQUEST_FILENAME} -l [OR]
	RewriteCond %{REQUEST_FILENAME} -d
	RewriteRule ^.*$ - [NC,L]
	RewriteRule ^.*$ index.php [NC,L]

</Directory>
```



