<?php

use RendyRobbani\Kodefikasi\Pemda\Model\SplitPdfRange;
use RendyRobbani\Kodefikasi\Pemda\Utility\PdfUtility;

ini_set("memory_limit", "-1");

require_once __DIR__ . "/../../vendor/autoload.php";

$file = __DIR__ . "/pdf/Kepmendagri No. 900.1-2850 Tahun 2025.pdf";
$output_directory = __DIR__ . "/pdf";
$ranges = [
	new SplitPdfRange(
		name: "B-UPDATE",
		from_page: 0006,
		into_page: 1108,
	),
	new SplitPdfRange(
		name: "D-UPDATE",
		from_page: 1109,
		into_page: 2093,
	),
	new SplitPdfRange(
		name: "H-UPDATE",
		from_page: 2094,
		into_page: 2097,
	),
	new SplitPdfRange(
		name: "I-UPDATE",
		from_page: 2098,
		into_page: 2881,
	),
	new SplitPdfRange(
		name: "J-UPDATE",
		from_page: 2882,
		into_page: 3105,
	),
	new SplitPdfRange(
		name: "K-UPDATE",
		from_page: 3106,
		into_page: 3406,
	),
	new SplitPdfRange(
		name: "B-DELETE",
		from_page: 3407,
		into_page: 3550,
	),
	new SplitPdfRange(
		name: "D-DELETE",
		from_page: 3551,
		into_page: 3660,
	),
	new SplitPdfRange(
		name: "H-DELETE",
		from_page: 3661,
		into_page: 3661,
	),
	new SplitPdfRange(
		name: "I-DELETE",
		from_page: 3662,
		into_page: 3664,
	),
	new SplitPdfRange(
		name: "J-DELETE",
		from_page: 3664,
		into_page: 3673,
	),
	new SplitPdfRange(
		name: "K-DELETE",
		from_page: 3673,
		into_page: 3674,
	),
];

PdfUtility::split(
	file: $file,
	ranges: $ranges,
	output_directory: $output_directory,
	pad_length: 5,
	limit: 300,
);