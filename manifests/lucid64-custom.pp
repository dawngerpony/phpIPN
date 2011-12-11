# Basic Puppet Apache/PHP5 manifest
class lucid64 {

  # needed when VM doesn't have the puppet group already created (e.g. lucid64) - dafydd
  group { "puppet":
      ensure => "present",
  }

  Exec { path => [ "/bin/", "/sbin/" , "/usr/bin/", "/usr/sbin/" ] }
  
  exec { "apt-get_update":
    command => "apt-get update",
  }
  
  Package { ensure => "installed" }

  package { "php5" : }
  package { "php-pear" : }
  package { "multitail" : }
  package { "mysql-server" : }
  package { "php5-mysql" : }
  package { "postfix" : }
  package { "nail" : }
  
  exec { "symlink_vagrant":
    command => "ln -s /vagrant /var/www/vagrant",
  }

  service { "apache2":
    ensure => running,
    require => Package["php5"],
  }

  service { "mysql":
    ensure => running,
    require => Package["mysql-server"],
  }

  exec { "install_pear_packages":
    command => "sudo pear install MDB2 Log Mail MDB2_Driver_mysql"
  }

  exec { "setup_db":
    command => "sudo mysql -u root < /vagrant/manifests/db_setup.sql && sudo mysql -u root pangel_dev < /vagrant/scripts/create_database.sql";
  }
    
  # Restart apache because if you don't do this, apache2 will start without PHP loaded
  exec { "restart_apache":
    command => "service apache2 restart",
  }
  
}

include lucid64
