<?php

use RendyRobbani\Kodefikasi\Pemda\Model\SplitPdfRange;
use RendyRobbani\Kodefikasi\Pemda\Utility\PdfUtility;

ini_set("memory_limit", "-1");

require_once __DIR__ . "/../../vendor/autoload.php";

$file = __DIR__ . "/pdf/Kepmendagri No. 900.1.15.5-1317 Tahun 2023.pdf";
$output_directory = __DIR__ . "/pdf";
$ranges = [
	new SplitPdfRange(
		name: "B-UPDATE",
		from_page: 0005,
		into_page: 1127,
	),
	new SplitPdfRange(
		name: "C-UPDATE",
		from_page: 1128,
		into_page: 2082,
	),
	new SplitPdfRange(
		name: "H-UPDATE",
		from_page: 2083,
		into_page: 2084,
	),
	new SplitPdfRange(
		name: "I-UPDATE",
		from_page: 2085,
		into_page: 2560,
	),
	new SplitPdfRange(
		name: "J-UPDATE",
		from_page: 2561,
		into_page: 2745,
	),
	new SplitPdfRange(
		name: "K-UPDATE",
		from_page: 2746,
		into_page: 3081,
	),
	new SplitPdfRange(
		name: "B-DELETE",
		from_page: 3082,
		into_page: 3193,
	),
	new SplitPdfRange(
		name: "C-DELETE",
		from_page: 3194,
		into_page: 3266,
	),
	new SplitPdfRange(
		name: "H-DELETE",
		from_page: 3267,
		into_page: 3267,
	),
	new SplitPdfRange(
		name: "I-DELETE",
		from_page: 3268,
		into_page: 3272,
	),
	new SplitPdfRange(
		name: "J-DELETE",
		from_page: 3273,
		into_page: 3276,
	),
	new SplitPdfRange(
		name: "K-DELETE",
		from_page: 3277,
		into_page: 3283,
	),
];

PdfUtility::split(
	file: $file,
	ranges: $ranges,
	output_directory: $output_directory,
	pad_length: 5,
	limit: 300,
);