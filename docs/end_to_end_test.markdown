1. Set up port forwarding through router on static IP to laptop

        Internal IP: 192.168.2.5
        Router: http://192.168.2.1/
        Log in to router: (Router password: <blank>)
        "Virtual Servers" (Port forwarding): http://192.168.2.1/fw_virt.html
        Add "Web Server (HTTP)": 8080 -> 8080 (192.168.2.5)
        Apply changes
        WAN IP: 93.97.43.125
        whois -r 93.97.43.125 | grep -ic static
        Set router password: tinofparty
        DynDNS: tophouse.dynathome.net

2. Set up port forwarding from laptop to vagrant VM

        <already done 8080 -> 80>

3. Generate external URL for PayPal IPN dev
    NB. PayPal only supports connections on port 80 and 443!

        http://anubis.vm.bytemark.co.uk/~dafydd/dev/phpIPN/notify.php
        http://93.97.43.125:8080/vagrant/notify.php
        http://tophouse.dynathome.net/vagrant/notify.php

4. Send PayPal test IPN notification to VM

        tail -F /tmp/phpipn.log
