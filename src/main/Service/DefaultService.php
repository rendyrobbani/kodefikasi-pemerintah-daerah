<?php

namespace RendyRobbani\Kodefikasi\Pemda\Service;

use RendyRobbani\Kodefikasi\Pemda\Peraturan\Peraturan;

interface DefaultService
{
	/**
	 * @param Peraturan $peraturan
	 * @param string[] $excel_files
	 * @param bool $is_perubahan
	 * @return void
	 */
	function fromExcelFiles(Peraturan $peraturan, array $excel_files, bool $is_perubahan): void;
}