<?php

use RendyRobbani\Kodefikasi\Pemda\Model\SplitPdfRange;
use RendyRobbani\Kodefikasi\Pemda\Utility\PdfUtility;

ini_set("memory_limit", "-1");

require_once __DIR__ . "/../../vendor/autoload.php";

$file = __DIR__ . "/pdf/Kepmendagri No. 050-3708 Tahun 2020.pdf";
$output_directory = __DIR__ . "/pdf";
$ranges = [
	new SplitPdfRange(
		name: "B",
		from_page: 30,
		into_page: 207,
	),
	new SplitPdfRange(
		name: "C",
		from_page: 208,
		into_page: 353,
	),
	new SplitPdfRange(
		name: "D",
		from_page: 354,
		into_page: 369,
	),
	new SplitPdfRange(
		name: "E",
		from_page: 370,
		into_page: 383,
	),
	new SplitPdfRange(
		name: "H",
		from_page: 446,
		into_page: 474,
	),
	new SplitPdfRange(
		name: "I",
		from_page: 475,
		into_page: 1608,
	),
	new SplitPdfRange(
		name: "J",
		from_page: 1608,
		into_page: 2338,
	),
	new SplitPdfRange(
		name: "K",
		from_page: 2338,
		into_page: 3143,
	),
];

PdfUtility::split(
	file: $file,
	ranges: $ranges,
	output_directory: $output_directory,
	pad_length: 5,
	limit: 300,
);