<?php
namespace PHPMV;

use PHPMV\utils\CommandParser;

/**
 * Connection to a remote Proxmox server.
 * PHPMV$ProxmoxMaster
 * This class is part of php-ssh-proxmox
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
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

	/**
	 * Returns the list of virtual machines on this server (qm list).
	 *
	 * @return string
	 */
	public function getVMs(): string {
		$this->checkBash();
		return $this->runCommand('qm list');
	}

	/**
	 * Starts a virtual machine.
	 *
	 * @param string $vmid
	 * @return string
	 */
	public function vmStart(string $vmid): string {
		$this->checkBash();
		return $this->runCommand("qm start $vmid");
	}

	/**
	 * Stops a virtual machine.
	 *
	 * @param string $vmid
	 * @return string
	 */
	public function vmStop(string $vmid): string {
		$this->checkBash();
		return $this->runCommand("qm stop $vmid");
	}

	/**
	 * Gets a virtual machine status
	 *
	 * @param string $vmid
	 * @return string
	 */
	public function vmStatus(string $vmid): string {
		$this->checkBash();
		return $this->runCommand("qm status $vmid");
	}

	/**
	 * Clones a virtual machine
	 *
	 * @param string $vmid
	 * @param string $newId
	 * @return string
	 */
	public function vmClone(string $vmid,string $newId): string {
		$this->checkBash();
		return $this->runCommand("qm clone $vmid $newId");
	}

	/**
	 * Deletes a virtual machine
	 *
	 * @param string $vmid
	 * @return string
	 */
	public function vmDestroy(string $vmid): string {
		$this->checkBash();
		return $this->runCommand("qm destroy $vmid");
	}

	/**
	 * Returns the iptables nat table.
	 *
	 * @return string
	 */
	public function getIptablesNat(): string {
		$this->checkBash();
		return $this->runCommand('iptables -t nat -L');
	}

	/**
	 * Returns an array of the virtual machines on this server.
	 *
	 * @return array
	 */
	public function getVMsAsArray(): array {
		$string = $this->getVMs();
		return CommandParser::readCommandOutput($string, "qm list", $this->prompt ?? '', self::VM_FIELDS, [
			0
		]);
	}

	/**
	 * Returns iptables nat rules as array.
	 *
	 * @return array
	 */
	public function getIptablesNatAsArray(): array {
		$string = $this->getIptablesNat();
		return CommandParser::readCommandOutput($string, 'target ', 'Chain INPUT (policy ACCEPT)', self::NAT_FIELDS, [
			0
		]);
	}

	/**
	 * Removes an iptables nat rule.
	 *
	 * @param int $lineNumber
	 * @return string
	 */
	public function iptablesRemoveNatRule(int $lineNumber): string {
		$this->checkBash();
		return $this->runCommand("iptables -t nat -D PREROUTING $lineNumber");
	}

	/**
	 * Adds an iptables nat rule.
	 *
	 * @param string $dport
	 * @param string $to
	 * @param string $protocol
	 * @return string
	 */
	public function iptablesAddNatRule(string $dport, string $to, string $protocol = 'tcp'): string {
		$this->checkBash();
		return $this->runCommand("iptables -t nat -A PREROUTING -i vmbr0 -p $protocol --dport $dport -j DNAT --to $to");
	}
}

