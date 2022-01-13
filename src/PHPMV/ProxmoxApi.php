<?php
namespace PHPMV;

use Proxmox\PVE;

/**
 * Request Proxmox PVE API.
 * PHPMV$ProxmoxApi
 * This class is part of php-ssh-proxmox
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class ProxmoxApi {

	private PVE $proxmox;

	private string $parNode;

	private function getParNode(): string {
		$nodes = $this->proxmox->nodes()->get();
		return $this->parNode = \current($nodes['data'])['node'] ?? null;
	}

	public function __construct(string $hostname, string $username, string $password, int $port = 8006, string $authType = 'pam', bool $debug = false) {
		$this->proxmox = new PVE($hostname, $username, $password, $port, $authType, $debug);
	}

	public function getVMs(): array {
		$parNode = $this->parNode ?? $this->getParNode();
		return $this->proxmox->nodes()
			->node($parNode)
			->qemu()
			->get()['data'];
	}

	/**
	 * Starts a virtual machine.
	 *
	 * @param string $vmid
	 * @return string
	 */
	public function vmStart(string $vmid): string {
		$parNode = $this->parNode ?? $this->getParNode();
		return $this->proxmox->nodes()
			->node($parNode)
			->qemu()
			->vmId($vmid)
			->status()
			->start()
			->post();
	}

	/**
	 * Stops a virtual machine.
	 *
	 * @param string $vmid
	 * @return string
	 */
	public function vmStop(string $vmid): string {
		$parNode = $this->parNode ?? $this->getParNode();
		return $this->proxmox->nodes()
			->node($parNode)
			->qemu()
			->vmId($vmid)
			->status()
			->stop()
			->post();
	}

	/**
	 * Gets a virtual machine status
	 *
	 * @param string $vmid
	 * @return string
	 */
	public function vmStatus(string $vmid): string {
		$parNode = $this->parNode ?? $this->getParNode();
		return $this->proxmox->nodes()
			->node($parNode)
			->qemu()
			->vmId($vmid)
			->status()
			->get();
	}

	/**
	 * Clones a virtual machine
	 *
	 * @param string $vmid
	 * @param string $newId
	 * @return string
	 */
	public function vmClone(string $vmid, string $newId): string {
		$parNode = $this->parNode ?? $this->getParNode();
		return $this->proxmox->nodes()
			->node($parNode)
			->qemu()
			->vmId($vmid)
			->clone()
			->post([
			'newid' => $newId
		]);
	}

	/**
	 * Deletes a virtual machine
	 *
	 * @param string $vmid
	 * @return string
	 */
	public function vmDestroy(string $vmid): string {
		$parNode = $this->parNode ?? $this->getParNode();
		return $this->proxmox->nodes()
			->node($parNode)
			->qemu()
			->vmId($vmid)
			->delete();
	}

	/**
	 *
	 * @return \Proxmox\PVE
	 */
	public function getProxmox() {
		return $this->proxmox;
	}

	/**
	 *
	 * @param string $parNode
	 */
	public function setParNode(string $parNode) {
		$this->parNode = $parNode;
	}
}

