# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|
    config.vm.box = "ubuntu/trusty64"
    config.vm.network "private_network", ip: "192.168.33.14"

    config.vm.provision "ansible" do |ansible|
        ansible.playbook = "provision/setup.yml"
        ansible.verbose = "vvv"
        ansible.sudo = true
    end
end