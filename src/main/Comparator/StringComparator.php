<?php

namespace RendyRobbani\Kodefikasi\Pemda\Comparator;

class StringComparator
{
	private function __construct()
	{
	}

	public static function isEqual(string|null $a, string|null $b): bool
	{
		if ($a === null && $b === null) return true;
		if ($a === null && $b !== null) return false;
		if ($a !== null && $b === null) return false;

		$a1 = preg_replace("/[^0-9a-z]/", "", strtolower($a));
		$b1 = preg_replace("/[^0-9a-z]/", "", strtolower($b));

		return $a1 === $b1;
	}
}