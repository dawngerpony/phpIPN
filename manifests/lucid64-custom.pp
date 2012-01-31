# Basic Puppet Apache/PHP5 manifest
class setup {
  exec { "apt-get_update":
    command => "apt-get update",
  }
  
  # needed when VM doesn't have the puppet group already created (e.g. lucid64) - dafydd
  group { "puppet":
      ensure => "present",
  }
  
}

class lucid64 {

  Exec { path => [ "/bin/", "/sbin/" , "/usr/bin/", "/usr/sbin/" ] }
  
  Package { ensure => "installed" }

  package { "php5": }
  package { "apache2": }
  package { "php-pear": }
  package { "mysql-server": }
  package { "php5-mysql": }
  package { "phpunit": }
  
  exec { "symlink_vagrant":
    command => "ln -s /vagrant /var/www/vagrant",
    require => Package["apache2"]
  }
  
  service { "apache2":
    ensure => running,
    require => Package["php5-mysql", "apache2"],
  }
  
  service { "mysql":
    ensure => running,
    require => Package["mysql-server"],
  }

  $pear_channels = ["pear.michelf.com", "pear.phing.info"]

  define discover_pear_channel($hostname) {
    exec { "channel_$hostname":
      command => "pear channel-discover $hostname",
      require => Package["php5", "php-pear"],
      unless => "pear channel-info $hostname"
    }
  }
  
  # discover_pear_channel($pear_channels)
  discover_pear_channel { "pear.michelf.com": hostname => "pear.michelf.com" }
  discover_pear_channel { "pear.phing.info": hostname => "pear.phing.info" }
  
  # Install PEAR packages, but wait until php5-mysql is installed
  # otherwise the MDB2_Driver_mysql installation will fail.
  exec { "install_pear_packages":
    command => "pear install phpunit MDB2 Log Mail MDB2_Driver_mysql phing/phing michelf/MarkdownExtra",
    require => Package["php5", "php-pear", "php5-mysql"],
    require => Discover_pear_channel["pear.michelf.com", "pear.phing.info"]
  }

  define import_mysql_file($user, $file, $schema) {
    exec { "mysql_$file":
      command => "mysql -u $user $schema < $file",
      require => Service['mysql']
    }
  }

  # Set up the database with a basic phpIPN schema; use the 'require' attribute
  # to wait until MySQL is ready before executing any commands.
  # See http://docs.puppetlabs.com/learning/ordering.html for more info - dafydd 2011-12-11
  import_mysql_file { "db_setup": user => "root", file => "/vagrant/manifests/db_setup.sql", schema => "" }
  import_mysql_file { "create_database": user => "root", file => "/vagrant/scripts/create_database.sql", schema => "pangel_dev" }
  
    
  # Restart apache because if you don't do this, apache2 will start without PHP loaded
  exec { "restart_apache":
    command => "service apache2 restart",
    require => Package["apache2", "php5", "php5-mysql"]
  }
  
  class { setup: stage => pre }

}

stage { pre: before => Stage[main] }

include lucid64
