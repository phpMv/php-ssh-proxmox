<?php
namespace PHPMV;

use PHPMV\utils\CommandParser;

class ProxmoxMaster extends RemoteHost {

	public function getVMs(): string {
		return $this->runCommand('qm list');
	}

	public function getIptablesNat(): string {
		return $this->runCommand('iptables -t nat -L');
	}

	public function getVMsAsArray(): array {
		$string = $this->getIptablesNat();
		return CommandParser::readCommandOutput($string, '', '', [
			'vmid',
			'name',
			'status',
			'mem',
			'bootdisk',
			'pid'
		]);
	}

	public function getIptablesNatAsArray(): array {
		$string = $this->getIptablesNat();
		return CommandParser::readCommandOutput($string, 'target ', 'Chain INPUT (policy ACCEPT)', [
			'target',
			'prot',
			'opt',
			'source',
			'destination',
			'proto_dest',
			'from',
			'to'
		]);
	}
}

