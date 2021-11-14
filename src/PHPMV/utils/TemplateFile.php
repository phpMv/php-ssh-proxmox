<?php
namespace PHPMV\utils;

/**
 * Manage a template.
 * PHPMV/utils$TemplateFile
 * This class is part of php-ssh-proxmox
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class TemplateFile {

	private $originalContent;

	private $variables;

	const VAR_PREFIX = '{{';

	const VAR_POSTFIX = '}}';

	public function __construct($filename) {
		if (\file_exists($filename)) {
			$this->originalContent = \file_get_contents($filename);
		} else {
			throw new \Exception("$filename doesn't exist!");
		}
	}

	/**
	 *
	 * @return array
	 */
	public function getVariables(): array {
		return $this->variables ?? $this->_getVariables();
	}

	private function _getVariables(): array {
		if (\preg_match_all('/' . preg_quote(self::VAR_PREFIX) . '(.*?)' . preg_quote(self::VAR_POSTFIX) . '/', $this->originalContent, $matches)) {
			return $matches[1];
		}
		return [];
	}

	/**
	 * Parses the template file with an associative array of key=>variable.
	 *
	 * @param array $varArray
	 * @return string
	 */
	public function parse(array $varArray = []): string {
		$variables = $this->getVariables();
		$content = $this->originalContent;
		foreach ($variables as $var) {
			$content = \str_replace(self::VAR_PREFIX . $var . self::VAR_POSTFIX, $varArray[$var] ?? '', $content);
		}
		return $content;
	}

	public function getOriginalContent(): string {
		return $this->originalContent;
	}
}

