<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RendyRobbani\Kodefikasi\Pemda\Peraturan\Peraturan;
use RendyRobbani\Kodefikasi\Pemda\Service\DefaultService;
use RendyRobbani\Kodefikasi\Pemda\Service\UrusanProvinsiService;
use RendyRobbani\PHP\Application;

ini_set("memory_limit", "-1");

require_once __DIR__ . "/../../vendor/autoload.php";

Application::setConfig(__DIR__ . "/application.json");

/** @var DefaultService[] $services */
$services = [];
$services[] = Application::getComponent(UrusanProvinsiService::class);

$spreadsheet = new Spreadsheet();
$spreadsheet->getDefaultStyle()->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
$spreadsheet->getDefaultStyle()->getFont()->setName("Bookman Old Style")->setSize(12);


for ($i = 0; $i < sizeof($services); $i++) {
	$services[$i]->exportToWorksheet($i === 0 ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet(), Peraturan::PERMENDAGRI_TAHUN_2019_NOMOR_90);
}

new Xlsx($spreadsheet)->save(__DIR__ . "/test.xlsx");