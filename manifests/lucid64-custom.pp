# Basic Puppet Apache/PHP5 manifest
class setup {
  exec { "apt-get_update":
    command => "apt-get update",
  }
  
  # needed when VM doesn't have the puppet group already created (e.g. lucid64) - dafydd
  group { "puppet":
      ensure => "present",
  }

  Package { ensure => "installed" }

  package { "php5": }
  package { "php-pear": }
  
  exec { "upgrade_pear":
    command => "pear upgrade-all",
    require => Package["php5", "php-pear"]
  }
  
  exec { "pear_auto_discover":
    command => "pear config-set auto_discover 1",
    require => Exec["upgrade_pear"]
  }
}

class lucid64 {

  Exec { path => [ "/bin/", "/sbin/" , "/usr/bin/", "/usr/sbin/" ] }
  
  Package { ensure => "installed" }

  package { "apache2": }
  package { "mysql-server": }
  package { "php5-mysql": }

  exec { "symlink_vagrant":
    command => "ln -s /vagrant /var/www/vagrant",
    require => Package["apache2"],
    unless => "[ -L /var/www/vagrant ]"
  }
  
  service { "apache2":
    ensure => running,
    require => Package["php5-mysql", "apache2"],
  }
  
  service { "mysql":
    ensure => running,
    require => Package["mysql-server"],
  }

  define install_custom_pear_package($package) {
    exec { "pear_install_$package":
      command => "pear install -a $package",
      unless => "pear info $package",
    }
  }
  
  install_custom_pear_package { "MarkdownExtra": package => "pear.michelf.com/MarkdownExtra" }
  install_custom_pear_package { "phing": package => "pear.phing.info/phing" }
  install_custom_pear_package { "phpunit": package => "pear.phpunit.de/PHPUnit" }

  # Install PEAR packages, but wait until php5-mysql is installed
  # otherwise the MDB2_Driver_mysql installation will fail.
  exec { "install_pear_packages":
    command => "pear install Log Mail MDB2 MDB2_Driver_mysql",
    require => Package["php5-mysql"],
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
