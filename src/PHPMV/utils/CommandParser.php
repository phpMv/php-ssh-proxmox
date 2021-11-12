<?php
namespace PHPMV\utils;

class CommandParser {

	public static function extractStringBetween($output, $start = '', $end = ''): string {
		$ini = 0;
		if ($start != '') {
			$ini = \strpos($output, $start);
			$ini += \strlen($start);
		}
		if ($end != '') {
			$len = \strpos($output, $end, $ini) - $ini;
		} else {
			$len = \strlen($output) - $ini;
		}
		return \substr($output, $ini, $len);
	}

	public static function readCommandOutput(string $output, string $start = '', string $end = '', array $headers = [], array $excludeLines = [], array $ignore = []): array {
		$string = self::extractStringBetween($output, $start, $end);
		return self::readLines($string, $headers, $excludeLines, $ignore);
	}

	private static function readLines($string, $header, $exclude = [
		0
	], $ignore = []): array {
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
