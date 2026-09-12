<?php

namespace RendyRobbani\Kodefikasi\Pemda\Utility;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class SpreadsheetUtility
{
	private function __construct()
	{
	}

	private static function ensureUtf8(string $value): string
	{
		// Source bukan UTF-8 → convert dari Windows-1252
		if (!mb_check_encoding($value, "UTF-8")) {
			$value = mb_convert_encoding($value, "UTF-8", "Windows-1252");

			return $value === false ? "" : $value;
		}

		// UTF-8 valid, tetapi kemungkinan mojibake
		$mojibakePatterns = [
			"â€",
			"â€¦",
			"â€“",
			"â€”",
			"â€˜",
			"â€™",
			"â€œ",
			"â€",
			"â€¢",
			"Ã",
			"Â",
			"ðŸ",
		];

		foreach ($mojibakePatterns as $pattern) {
			if (str_contains($value, $pattern)) {
				$fixed = mb_convert_encoding($value, "Windows-1252", "UTF-8");

				if ($fixed !== false) {
					$fixed = mb_convert_encoding($fixed, "UTF-8", "Windows-1252");

					if ($fixed !== false) {
						return $fixed;
					}
				}

				break;
			}
		}

		return $value;
	}

	/**
	 * @param float $mm
	 * @return float
	 */
	public static function inch(float $mm): float
	{
		return $mm / 25.4;
	}

	/**
	 * @param string|null $value
	 * @return string|null
	 */
	public static function cleanValue(null|string $value): null|string
	{
		if ($value == null || trim($value) == "") return null;
		$value = preg_replace("/[\r\n\t ]/", " ", $value);
		while (str_contains($value, "  ")) $value = str_replace("  ", " ", $value);
		foreach (str_split("-:/") as $separator) {
			foreach ([" $separator", "$separator "] as $search) {
				while (str_contains($value, $search)) $value = str_replace($search, $separator, $value);
			}
		}
		foreach (str_split("([{") as $separator) {
			while (str_contains($value, "$separator ")) $value = str_replace("$separator ", $separator, $value);
		}
		foreach (str_split(")]}") as $separator) {
			while (str_contains($value, " $separator")) $value = str_replace(" $separator", $separator, $value);
		}

		foreach (["‟", "„"] as $search) $value = str_replace($search, "'", $value);

		$value = trim($value);

		if (!mb_check_encoding($value, "UTF-8")) {
			if (mb_check_encoding($value, "Windows-1252")) $value = mb_convert_encoding($value, "UTF-8", "Windows-1252");
		}

		$value = trim($value);

		if ($value === "Dst...") $value = "Dst ...";

		return $value == "" ? null : $value;
	}

	/**
	 * @param Worksheet $worksheet
	 * @param int $rowNum
	 * @param int $colNum
	 * @return string|null
	 */
	public static function getCellValueAsString(Worksheet $worksheet, int $rowNum, int $colNum): null|string
	{
		return self::cleanValue($worksheet->getCell([$colNum, $rowNum])->getValueString());
	}

	/**
	 * @param Worksheet $worksheet
	 * @param int $rowNum
	 * @param int $fromColNum
	 * @param int $intoColNum
	 * @return array
	 */
	public static function getCellValuesAsStringFromRow(Worksheet $worksheet, int $rowNum, int $fromColNum, int $intoColNum): array
	{
		$values = [];
		for ($colNum = $fromColNum; $colNum <= $intoColNum; $colNum++) $values[] = self::getCellValueAsString($worksheet, $rowNum, $colNum);
		return $values;
	}

	public static function countNotNullColumnsAntNotBlank(array $values): int
	{
		return array_sum(array_map(fn($value) => $value === null || $value === "" ? 0 : 1, $values));
	}
}