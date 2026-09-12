<?php

namespace RendyRobbani\Kodefikasi\Pemda\Service;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use RendyRobbani\Kodefikasi\Pemda\Peraturan\Peraturan;

interface DefaultService
{
	/**
	 * @param Peraturan $peraturan
	 * @param string[] $excel_files
	 * @param bool $delete_if_not_exists
	 * @return void
	 */
	function updateFromExcelFiles(Peraturan $peraturan, array $excel_files, bool $delete_if_not_exists): void;

	/**
	 * @param Peraturan $peraturan
	 * @param string[] $excel_files
	 * @return void
	 */
	function deleteFromExcelFiles(Peraturan $peraturan, array $excel_files): void;

	/**
	 * @param Worksheet $worksheet
	 * @param Peraturan $peraturan
	 * @return Worksheet
	 */
	function exportToWorksheet(Worksheet $worksheet, Peraturan $peraturan): Worksheet;
}