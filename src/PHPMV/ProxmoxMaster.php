<?php
namespace PHPMV;

use PHPMV\utils\CommandParser;

class ProxmoxMaster extends RemoteHost {

	public const VM_FIELDS = [
		'vmid',
		'name',
		'status',
		'mem',
		'bootdisk',
		'pid'
	];

	public const NAT_FIELDS = [
		'target',
		'prot',
		'opt',
		'source',
		'destination',
		'proto_dest',
		'from',
		'to'
	];

	public function getVMs(): string {
		$this->checkBash();
		return $this->runCommand('qm list');
	}

	public function vmStart(string $vmid): string {
		$this->checkBash();
		return $this->runCommand("qm start $vmid");
	}

	public function vmStop(string $vmid): string {
		$this->checkBash();
		return $this->runCommand("qm stop $vmid");
	}

	public function vmStatus(string $vmid): string {
		$this->checkBash();
		return $this->runCommand("qm status $vmid");
	}

	public function getIptablesNat(): string {
		$this->checkBash();
		return $this->runCommand('iptables -t nat -L');
	}

	public function getVMsAsArray(): array {
		$string = $this->getVMs();
		return CommandParser::readCommandOutput($string, "qm list\n", '', self::VM_FIELDS);
	}

	public function getIptablesNatAsArray(): array {
		$string = $this->getIptablesNat();
		return CommandParser::readCommandOutput($string, 'target ', 'Chain INPUT (policy ACCEPT)', self::NAT_FIELDS);
	}

	public function iptablesRemoveNatRule(int $lineNumber): string {
		$this->checkBash();
		return $this->runCommand("iptables -t nat -D PREROUTING $lineNumber");
	}

	public function iptablesAddNatRule(string $dport, string $to, string $protocol = 'tcp') {
		$this->checkBash();
		return $this->runCommand("iptables -t nat -A PREROUTING -i vmbr0 -p $protocol --dport $dport -j DNAT --to $to");
	}
}

