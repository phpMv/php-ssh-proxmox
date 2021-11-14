<?php
namespace PHPMV\utils;

class TemplateFile {

	private $originalContent;

	private $variables;

	const VAR_PREFIX = '{{';

	const VAR_POSTFIX = '}}';

	public function __construct($filename) {
		if (\file_exists($filename)) {
			$this->originalContent = \file_get_contents($filename);
		}
	}

	public function getVariables() {
		return $this->variables ?? $this->_getVariables();
	}

	private function _getVariables() {
		if (\preg_match_all('/' . preg_quote(self::VAR_PREFIX) . '(.*?)' . preg_quote(self::VAR_POSTFIX) . '/', $this->originalContent, $matches)) {
			return $matches[1];
		}
		return [];
	}

	public function parse(array $varArray = []): string {
		$variables = $this->getVariables();
		$content = $this->originalContent;
		foreach ($variables as $var) {
			$content = \str_replace(self::VAR_PREFIX . $var . self::VAR_POSTFIX, $varArray[$var] ?? '', $content);
		}
		return $content;
	}

	public function getOriginalContent() {
		return $this->originalContent;
	}
}

