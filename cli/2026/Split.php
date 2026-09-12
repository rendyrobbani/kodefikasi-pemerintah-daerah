<?php

use RendyRobbani\Kodefikasi\Pemda\Model\SplitPdfRange;
use RendyRobbani\Kodefikasi\Pemda\Utility\PdfUtility;

ini_set("memory_limit", "-1");

require_once __DIR__ . "/../../vendor/autoload.php";

$file = __DIR__ . "/pdf/Kepmendagri No. 900.1-861 Tahun 2026.pdf";
$output_directory = __DIR__ . "/pdf";
$ranges = [
	new SplitPdfRange(
		name: "B-UPDATE",
		from_page: 0007,
		into_page: 1222,
	),
	new SplitPdfRange(
		name: "C-UPDATE",
		from_page: 1223,
		into_page: 2219,
	),
	new SplitPdfRange(
		name: "H-UPDATE",
		from_page: 2220,
		into_page: 2240,
	),
	new SplitPdfRange(
		name: "I-UPDATE",
		from_page: 2241,
		into_page: 2311,
	),
	new SplitPdfRange(
		name: "J-UPDATE",
		from_page: 2312,
		into_page: 2357,
	),
	new SplitPdfRange(
		name: "K-UPDATE",
		from_page: 2358,
		into_page: 2398,
	),
	new SplitPdfRange(
		name: "B-DELETE",
		from_page: 2399,
		into_page: 2561,
	),
	new SplitPdfRange(
		name: "C-DELETE",
		from_page: 2562,
		into_page: 2683,
	),
	new SplitPdfRange(
		name: "J-DELETE",
		from_page: 2684,
		into_page: 2684,
	),
];

PdfUtility::split(
	file: $file,
	ranges: $ranges,
	output_directory: $output_directory,
	pad_length: 5,
	limit: 300,
);