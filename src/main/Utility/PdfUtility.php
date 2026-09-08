<?php

namespace RendyRobbani\Kodefikasi\Pemda\Utility;

use RendyRobbani\Kodefikasi\Pemda\Model\SplitPdfRange;
use RendyRobbani\PHP\Exception\FileNotFoundException;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\PdfParserException;
use setasign\Fpdi\PdfReader\PdfReaderException;

final class PdfUtility
{
	private function __construct()
	{
	}

	/**
	 * @param string $file
	 * @param SplitPdfRange[] $ranges
	 * @param string $output_directory
	 * @param int $pad_length
	 * @param int $limit
	 * @return void
	 * @throws PdfParserException|PdfReaderException
	 */
	public static function split(string $file, array $ranges, string $output_directory, int $pad_length = 0, int $limit = 0): void
	{
		if (!file_exists($file)) throw new FileNotFoundException($file);

		if (!file_exists($output_directory)) mkdir($output_directory, 0777, true);

		$main_ranges = $ranges;

		if ($limit > 0) {
			$main_ranges = [];
			foreach ($ranges as $range) {
				while (true) {
					if ($range->totalPages() >= $limit) {
						$from_range = new SplitPdfRange($range->name, $range->from_page, $range->from_page + $limit - 1);
						$into_range = new SplitPdfRange($range->name, $from_range->into_page + 1, $range->into_page);

						$main_ranges[] = $from_range;
						$range = $into_range;
					} else {
						$main_ranges[] = $range;
						break;
					}
				}
			}
		}

		foreach ($main_ranges as $range) {
			$pdf = new Fpdi();
			$total_pages = $pdf->setSourceFile($file);
			for ($page = $range->from_page; $page <= min($total_pages, $range->into_page); $page++) {
				$template = $pdf->importPage($page);
				$size = $pdf->getTemplateSize($template);

				$orientation = $size["width"] > $size["height"] ? "L" : "P";

				$pdf->AddPage($orientation, [$size["width"], $size["height"]]);

				$pdf->useTemplate($template);
			}

			$pdf->Output("F", $output_directory . DIRECTORY_SEPARATOR . $range->fileName($pad_length));
		}
	}
}