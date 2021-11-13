<?php
namespace PHPMV\utils;

class TemplateFile {

	private $originalContent;

	const VAR_PREFIX = '{{';

	const VAR_POSTFIX = '}}';

	public function __construct($filename) {
		if (\file_exists($filename)) {
			$this->originalContent = \file_get_contents($filename);
		}
	}
}

