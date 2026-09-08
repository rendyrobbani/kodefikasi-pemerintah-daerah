<?php

use RendyRobbani\Kodefikasi\Pemda\Model\SplitPdfRange;
use RendyRobbani\Kodefikasi\Pemda\Utility\PdfUtility;

ini_set("memory_limit", "-1");

require_once __DIR__ . "/../../vendor/autoload.php";

$file = __DIR__ . "/pdf/Permendagri No. 90 Tahun 2019.pdf";
$output_directory = __DIR__ . "/pdf";
$ranges = [
	new SplitPdfRange(
		name: "B",
		from_page: 32,
		into_page: 188,
	),
	new SplitPdfRange(
		name: "C",
		from_page: 189,
		into_page: 310,
	),
	new SplitPdfRange(
		name: "D",
		from_page: 310,
		into_page: 311,
	),
	new SplitPdfRange(
		name: "G",
		from_page: 371,
		into_page: 393,
	),
	new SplitPdfRange(
		name: "H",
		from_page: 393,
		into_page: 1226,
	),
	new SplitPdfRange(
		name: "I",
		from_page: 1226,
		into_page: 1763,
	),
	new SplitPdfRange(
		name: "J",
		from_page: 1764,
		into_page: 2300,
	),
];

PdfUtility::split(
	file: $file,
	ranges: $ranges,
	output_directory: $output_directory,
	pad_length: 5,
	limit: 300,
);