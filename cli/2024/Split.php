<?php

use RendyRobbani\Kodefikasi\Pemda\Model\SplitPdfRange;
use RendyRobbani\Kodefikasi\Pemda\Utility\PdfUtility;

ini_set("memory_limit", "-1");

require_once __DIR__ . "/../../vendor/autoload.php";

$file = __DIR__ . "/pdf/Kepmendagri No. 900.1.15.5-3406 Tahun 2024.pdf";
$output_directory = __DIR__ . "/pdf";
$ranges = [
	new SplitPdfRange(
		name: "B-UPDATE",
		from_page: 38,
		into_page: 1125,
	),
	new SplitPdfRange(
		name: "C-UPDATE",
		from_page: 1126,
		into_page: 1897,
	),
	new SplitPdfRange(
		name: "D-UPDATE",
		from_page: 1898,
		into_page: 1907,
	),
	new SplitPdfRange(
		name: "E-UPDATE",
		from_page: 1908,
		into_page: 1916,
	),
	new SplitPdfRange(
		name: "H-UPDATE",
		from_page: 1950,
		into_page: 1960,
	),
	new SplitPdfRange(
		name: "I-UPDATE",
		from_page: 1961,
		into_page: 2296,
	),
	new SplitPdfRange(
		name: "J-UPDATE",
		from_page: 2297,
		into_page: 2416,
	),
	new SplitPdfRange(
		name: "K-UPDATE",
		from_page: 2417,
		into_page: 2603,
	),
	new SplitPdfRange(
		name: "B-DELETE",
		from_page: 2604,
		into_page: 2633,
	),
	new SplitPdfRange(
		name: "C-DELETE",
		from_page: 2634,
		into_page: 2658,
	),
	new SplitPdfRange(
		name: "H-DELETE",
		from_page: 2659,
		into_page: 2662,
	),
	new SplitPdfRange(
		name: "I-DELETE",
		from_page: 2663,
		into_page: 2670,
	),
	new SplitPdfRange(
		name: "J-DELETE",
		from_page: 2670,
		into_page: 2675,
	),
	new SplitPdfRange(
		name: "K-DELETE",
		from_page: 2675,
		into_page: 2680,
	),
	new SplitPdfRange(
		name: "I-DELETE",
		from_page: 2681,
		into_page: 2713,
	),
	new SplitPdfRange(
		name: "J-DELETE",
		from_page: 2713,
		into_page: 2728,
	),
	new SplitPdfRange(
		name: "K-DELETE",
		from_page: 2728,
		into_page: 2743,
	),
];

PdfUtility::split(
	file: $file,
	ranges: $ranges,
	output_directory: $output_directory,
	pad_length: 5,
	limit: 300,
);