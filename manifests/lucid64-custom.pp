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

  package { "php5" : }
  package { "apache2" : }
  package { "php-pear" : }
  package { "mysql-server" : }
  package { "php5-mysql" : }
  package { "phpunit" : }
  
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

  # Install PEAR packages, but wait until php5-mysql is installed
  # otherwise the MDB2_Driver_mysql installation will fail.
  exec { "install_pear_packages":
    command => "sudo pear channel-discover pear.phing.info && sudo pear install phpunit MDB2 Log Mail MDB2_Driver_mysql phing/phing",
    require => Package["php5", "php5-mysql"]
  }

  # Set up the database with a basic phpIPN schema; use the 'require' attribute
  # to wait until MySQL is ready before executing any commands.
  # See http://docs.puppetlabs.com/learning/ordering.html for more info - dafydd 2011-12-11
  exec { "setup_db":
    command => "mysql -u root < /vagrant/manifests/db_setup.sql && mysql -u root pangel_dev < /vagrant/scripts/create_database.sql",
    require => Service['mysql']
  }
    
  # Restart apache because if you don't do this, apache2 will start without PHP loaded
  exec { "restart_apache":
    command => "service apache2 restart",
    require => Package["apache2", "php5", "php5-mysql"]
  }
  
  class { setup: stage => pre }

}

stage { pre: before => Stage[main] }

include lucid64
