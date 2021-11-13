<?php
namespace PHPMV;

use PHPMV\utils\CommandParser;
use phpseclib3\Net\SFTP;

class RemoteHost {

	public const VHOST_FIELDS = [
		'port',
		'host',
		'config'
	];

	private SFTP $ssh;

	private string $prompt;

	private function waitForPrompt(?string $prompt = null): string {
		if (isset($prompt)) {
			return $this->ssh->read($prompt);
		}
		return $this->ssh->read();
	}

	public function login($host, $user, $password, $port = 22): bool {
		$this->ssh = new SFTP($host, $port);
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
		return $this->waitForPrompt($this->prompt);
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

	public function runInteractiveCommand(string $command, array $promptResponses = []): string {
		$this->ssh->write("$command\n");
		foreach ($promptResponses as $prompt => $response) {
			$this->ssh->read($prompt);
			$this->ssh->write("$response\n");
		}
		return $this->waitFor();
	}

	public function getSshInstance(): SFTP {
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

	/**
	 * Downloads a file from the SFTP server.
	 *
	 * Returns a string containing the contents of $remote_file if $local_file is left undefined or a boolean false if
	 * the operation was unsuccessful. If $local_file is defined, returns true or false depending on the success of the
	 * operation.
	 *
	 * $offset and $length can be used to download files in chunks.
	 *
	 * @param string $remote_file
	 * @param string|bool|resource|callable $local_file
	 * @param int $offset
	 * @param int $length
	 * @param callable|null $progressCallback
	 * @throws \UnexpectedValueException on receipt of unexpected packets
	 * @return mixed
	 * @access public
	 */
	public function getFile($remote_file, $local_file = false, $offset = 0, $length = - 1, $progressCallback = null) {
		return $this->ssh->get($remote_file, $local_file, $offset, $length, $progressCallback);
	}

	/**
	 * Uploads a file to the SFTP server.
	 *
	 * By default, \phpseclib3\Net\SFTP::put() does not read from the local filesystem. $data is dumped directly into $remote_file.
	 * So, for example, if you set $data to 'filename.ext' and then do \phpseclib3\Net\SFTP::get(), you will get a file, twelve bytes
	 * long, containing 'filename.ext' as its contents.
	 *
	 * Setting $mode to self::SOURCE_LOCAL_FILE will change the above behavior. With self::SOURCE_LOCAL_FILE, $remote_file will
	 * contain as many bytes as filename.ext does on your local filesystem. If your filename.ext is 1MB then that is how
	 * large $remote_file will be, as well.
	 *
	 * Setting $mode to self::SOURCE_CALLBACK will use $data as callback function, which gets only one parameter -- number
	 * of bytes to return, and returns a string if there is some data or null if there is no more data
	 *
	 * If $data is a resource then it'll be used as a resource instead.
	 *
	 * Currently, only binary mode is supported. As such, if the line endings need to be adjusted, you will need to take
	 * care of that, yourself.
	 *
	 * $mode can take an additional two parameters - self::RESUME and self::RESUME_START. These are bitwise AND'd with
	 * $mode. So if you want to resume upload of a 300mb file on the local file system you'd set $mode to the following:
	 *
	 * self::SOURCE_LOCAL_FILE | self::RESUME
	 *
	 * If you wanted to simply append the full contents of a local file to the full contents of a remote file you'd replace
	 * self::RESUME with self::RESUME_START.
	 *
	 * If $mode & (self::RESUME | self::RESUME_START) then self::RESUME_START will be assumed.
	 *
	 * $start and $local_start give you more fine grained control over this process and take precident over self::RESUME
	 * when they're non-negative. ie. $start could let you write at the end of a file (like self::RESUME) or in the middle
	 * of one. $local_start could let you start your reading from the end of a file (like self::RESUME_START) or in the
	 * middle of one.
	 *
	 * Setting $local_start to > 0 or $mode | self::RESUME_START doesn't do anything unless $mode | self::SOURCE_LOCAL_FILE.
	 *
	 * {@internal ASCII mode for SFTPv4/5/6 can be supported by adding a new function - \phpseclib3\Net\SFTP::setMode().}
	 *
	 * @param string $remote_file
	 * @param string|resource $data
	 * @param int $mode
	 * @param int $start
	 * @param int $local_start
	 * @param callable|null $progressCallback
	 * @throws \UnexpectedValueException on receipt of unexpected packets
	 * @throws \BadFunctionCallException if you're uploading via a callback and the callback function is invalid
	 * @throws \phpseclib3\Exception\FileNotFoundException if you're uploading via a file and the file doesn't exist
	 * @return bool
	 * @access public
	 */
	public function putFile($remote_file, $data, $mode = 2, $start = - 1, $local_start = - 1, $progressCallback = null) {
		if (! $this->ssh->put($remote_file, $data, $mode, $start, $local_start, $progressCallback)) {
			throw new \RuntimeException("\n", \implode($this->ssh->getSFTPErrors()));
		}
		return true;
	}
}
