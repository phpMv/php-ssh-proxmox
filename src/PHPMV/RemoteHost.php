<?php
namespace PHPMV;

use phpseclib3\Net\SSH2;
use PHPMV\utils\CommandParser;

class RemoteHost {

	public const VHOST_FIELDS = [
		'port',
		'host',
		'config'
	];

	private SSH2 $ssh;

	private string $prompt;

	private function waitForPrompt(?string $prompt = null): string {
		if (isset($prompt)) {
			return $this->ssh->read($prompt);
		}
		return $this->ssh->read();
	}

	public function login($host, $user, $password, $port = 22): bool {
		$this->ssh = new SSH2($host, $port);
		return $this->ssh->login($user, $password);
	}

	public function runBash(int $timeout = 3, ?string $prompt = null): string {
		$this->ssh->setTimeout($timeout);
		$this->ssh->enablePTY();
		$this->ssh->exec('bash');
		return $this->waitFor($prompt);
	}

	public function waitFor(?string $prompt = null): string {
		if (isset($prompt)) {
			$this->prompt = $prompt;
		}
		return $this->waitForPrompt($prompt);
	}

	public function asSu(string $password, ?string $prompt = null): string {
		$this->ssh->write("sudo su\n");
		$this->ssh->read('password for');
		$this->ssh->write("$password\n");
		return $this->waitFor($prompt);
	}

	public function runCommand(string $command, ?string $prompt = null): string {
		$this->ssh->write("$command\n");
		return $this->waitFor($prompt);
	}

	public function getSshInstance(): SSH2 {
		return $this->ssh;
	}

	public function disconnect(): void {
		$this->ssh->disconnect();
	}

	public function getVhosts() {
		return $this->runCommand('apache2ctl -t -D DUMP_VHOSTS');
	}

	public function getVhostsAsArray(int $port = 80) {
		$result = $this->getVhosts();
		return CommandParser::readCommandOutput($result, "*:$port                   is a NameVirtualHost", '*:', self::VHOST_FIELDS, [], [
			'port',
			'namevhost'
		]);
	}
}
