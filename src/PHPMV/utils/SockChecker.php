<?php
namespace PHPMV\utils;

class SockChecker {

	public const SERVICES = [
		21 => 'ftp',
		22 => 'ssh',
		25 => 'smtp',
		80 => 'http',
		81 => 'http',
		110 => 'pop3',
		443 => 'https',
		3306 => 'mysql',
		8080 => 'tomcat'
	];

	public static function isOpen(string $host, int $port): bool {
		$connection = @fsockopen($host, $port);
		$result = \is_resource($connection);
		\fclose($connection);
		return $result;
	}

	public static function getService(string $host, int $port): ?string {
		$connection = @fsockopen($host, $port);
		if (\is_resource($connection)) {
			$result = \getservbyport($port, 'tcp');
			\fclose($connection);
			return $result;
		}
		return null;
	}

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

