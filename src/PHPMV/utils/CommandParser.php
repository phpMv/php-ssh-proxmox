<?php
namespace PHPMV\utils;

/**
 * A commands parser.
 * PHPMV/utils$CommandParser
 * This class is part of php-ssh-proxmox
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class CommandParser {

	/**
	 * Extracts a part of a command output.
	 *
	 * @param string $output
	 * @param string $start
	 * @param string $end
	 * @return string
	 */
	public static function extractStringBetween(string $output, string $start = '', string $end = ''): string {
		$ini = 0;
		$output = \preg_replace('![ \t]+!', ' ', $output);
		if ($start != '') {
			if (($ini = \strpos($output, $start)) !== false) {
				$ini += \strlen($start);
			} else {
				return '';
			}
		}
		if ($end != '') {
			if (($lastPos = \strpos($output, $end, $ini)) !== false) {
				$len = \strpos($output, $end, $ini) - $ini;
			} else {
				$len = \strlen($output) - $ini;
			}
		} else {
			$len = \strlen($output) - $ini;
		}
		return \substr($output, $ini, $len);
	}

	/**
	 * Reads a command result an returns an array.
	 *
	 * @param string $output
	 * @param string $start
	 * @param string $end
	 * @param array $headers
	 * @param array $excludeLines
	 * @param array $ignore
	 * @return array
	 */
	public static function readCommandOutput(string $output, string $start = '', string $end = '', array $headers = [], array $excludeLines = [], array $ignore = []): array {
		$string = self::extractStringBetween($output, $start, $end);
		return self::readLines($string, $headers, $excludeLines, $ignore);
	}

	private static function readLines(string $string, array $header, array $exclude = [
		0
	], array $ignore = []): array {
		$string = \str_replace($ignore, '', $string);
		$string = \preg_replace('![ \t]+!', ' ', $string);
		$string = \str_replace(PHP_EOL . PHP_EOL, PHP_EOL, $string);
		$lines = explode(PHP_EOL, \trim($string));
		$result = [];
		foreach ($lines as $i => $line) {
			$line = \trim($line);
			if (\array_search($i, $exclude) === false) {
				$values = \explode(' ', $line);
				$resultLine = [];
				foreach ($values as $index => $v) {
					if (isset($header[$index])) {
						$resultLine[$header[$index]] = $v;
					} else {
						$resultLine[] = $v;
					}
				}
				$result[] = $resultLine;
			}
		}
		return $result;
	}
}
