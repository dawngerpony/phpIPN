# Basic Puppet Apache manifest

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

  # package { "libapache2-mod-php5" : }
  # package { "apache2" : }
  package { "php5" : }
  # package { "libapache2-mod-php5" : }
  
  exec { "symlink_vagrant":
    command => "ln -s /vagrant/ /var/www/",
  }

  # $php5_packages = [ "php5", "apache2" ]
  # # $php5_packages = [ "libapache2-mod-php5" ]
  # 
  # package { $php5_packages: }

  service { "apache2":
    ensure => running,
    require => Package["php5"],
  }

  # Service["apache2"] -> Package["apache2"]
  # Service["apache2"] -> Package["php5"]
  
  # Restart apache because if you don't do this, apache2 will start without PHP loaded
  exec { "restart_apache":
    command => "service apache2 restart",
  }
    
}

include lucid64
