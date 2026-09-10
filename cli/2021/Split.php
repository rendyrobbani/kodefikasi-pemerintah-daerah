<?php

use RendyRobbani\Kodefikasi\Pemda\Model\SplitPdfRange;
use RendyRobbani\Kodefikasi\Pemda\Utility\PdfUtility;

ini_set("memory_limit", "-1");

require_once __DIR__ . "/../../vendor/autoload.php";

$file = __DIR__ . "/pdf/Kepmendagri No. 050-5889 Tahun 2021.pdf";
$output_directory = __DIR__ . "/pdf";
$ranges = [
	new SplitPdfRange(
		name: "B",
		from_page: 37,
		into_page: 340,
	),
	new SplitPdfRange(
		name: "C",
		from_page: 341,
		into_page: 601,
	),
	new SplitPdfRange(
		name: "D",
		from_page: 602,
		into_page: 608,
	),
	new SplitPdfRange(
		name: "E",
		from_page: 609,
		into_page: 615,
	),
	new SplitPdfRange(
		name: "H",
		from_page: 643,
		into_page: 660,
	),
	new SplitPdfRange(
		name: "I",
		from_page: 661,
		into_page: 4316,
	),
	new SplitPdfRange(
		name: "J",
		from_page: 4317,
		into_page: 5258,
	),
	new SplitPdfRange(
		name: "K",
		from_page: 5259,
		into_page: 8113,
	),
];

PdfUtility::split(
	file: $file,
	ranges: $ranges,
	output_directory: $output_directory,
	pad_length: 5,
	limit: 300,
);