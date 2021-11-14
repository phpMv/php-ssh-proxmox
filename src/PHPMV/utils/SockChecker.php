<?php
namespace PHPMV\utils;

/**
 * For checking ports on a remote host.
 * PHPMV/utils$SockChecker
 * This class is part of php-ssh-proxmox
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class SockChecker {

	public const SERVICES = [
		21 => 'ftp',
		22 => 'ssh',
		25 => 'smtp',
		80 => 'http',
		81 => 'http',
		110 => 'pop3',
		115 => 'sftp',
		443 => 'https',
		3306 => 'mysql',
		8080 => 'tomcat'
	];

	/**
	 * Checks if a host:port is open.
	 *
	 * @param string $host
	 * @param int $port
	 * @return bool
	 */
	public static function isOpen(string $host, int $port): bool {
		\set_error_handler(function () { /* ignore errors */
		});
		try {
			$connection = fsockopen($host, $port);
			$result = \is_resource($connection);
			\fclose($connection);
			return $result;
		} catch (\ErrorException $e) {
			return false;
		} finally {
			\restore_error_handler();
		}
	}

	/**
	 * Returns the service running on the host:port.
	 *
	 * @param string $host
	 * @param int $port
	 * @return string|NULL
	 */
	public static function getService(string $host, int $port): ?string {
		\set_error_handler(function () { /* ignore errors */
		});
		try {
			$connection = fsockopen($host, $port);
			if (\is_resource($connection)) {
				$result = \getservbyport($port, 'tcp');
				\fclose($connection);
				return $result;
			}
		} catch (\ErrorException $e) {
			return null;
		} finally {
			\restore_error_handler();
		}
		return null;
	}

	/**
	 * Returns all services running on host:port.
	 *
	 * @param string $host
	 * @param array $ports
	 * @return array
	 */
	public static function getAllServices(string $host, ?array $ports = null): array {
		$ports ??= self::SERVICES;
		$result = [];
		foreach ($ports as $port => $service) {
			if (($srv = self::getService($host, $port)) !== null) {
				$result[$port] = $srv;
			} else {
				$result[$port] = "$service not available!";
			}
		}
		return $result;
	}
}

