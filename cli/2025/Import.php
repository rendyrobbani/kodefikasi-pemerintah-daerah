<?php

use RendyRobbani\Kodefikasi\Pemda\Entity\FungsiKabupatenEntity;
use RendyRobbani\Kodefikasi\Pemda\Entity\FungsiProvinsiEntity;
use RendyRobbani\Kodefikasi\Pemda\Entity\LoEntity;
use RendyRobbani\Kodefikasi\Pemda\Entity\LraEntity;
use RendyRobbani\Kodefikasi\Pemda\Entity\NeracaEntity;
use RendyRobbani\Kodefikasi\Pemda\Entity\SumberEntity;
use RendyRobbani\Kodefikasi\Pemda\Entity\UrusanKabupatenEntity;
use RendyRobbani\Kodefikasi\Pemda\Entity\UrusanProvinsiEntity;
use RendyRobbani\Kodefikasi\Pemda\Peraturan\Peraturan;
use RendyRobbani\Kodefikasi\Pemda\Service\FungsiKabupatenService;
use RendyRobbani\Kodefikasi\Pemda\Service\FungsiProvinsiService;
use RendyRobbani\Kodefikasi\Pemda\Service\LoService;
use RendyRobbani\Kodefikasi\Pemda\Service\LraService;
use RendyRobbani\Kodefikasi\Pemda\Service\NeracaService;
use RendyRobbani\Kodefikasi\Pemda\Service\SumberService;
use RendyRobbani\Kodefikasi\Pemda\Service\UrusanKabupatenService;
use RendyRobbani\Kodefikasi\Pemda\Service\UrusanProvinsiService;
use RendyRobbani\PHP\Application;
use RendyRobbani\PHP\Connection\Connection;

ini_set("memory_limit", "-1");

require_once __DIR__ . "/../../vendor/autoload.php";

$reference = "kodefikasi_pemda_2024";

Application::setConfig(__DIR__ . "/application.json");
$connection = Application::getComponent(Connection::class);

for ($i = 4; $i < 8; $i++) {
	$info = match ($i) {
		0 => Application::getEntityInfo(UrusanProvinsiEntity::class),
		1 => Application::getEntityInfo(UrusanKabupatenEntity::class),
		2 => Application::getEntityInfo(FungsiProvinsiEntity::class),
		3 => Application::getEntityInfo(FungsiKabupatenEntity::class),
		4 => Application::getEntityInfo(SumberEntity::class),
		5 => Application::getEntityInfo(NeracaEntity::class),
		6 => Application::getEntityInfo(LraEntity::class),
		7 => Application::getEntityInfo(LoEntity::class),
	};

	for ($j = 0; $j < 2; $j++) {
		$tableName = $info->table;
		if ($j === 0) $tableName .= "_log";

		$sql = "delete from $connection->database.$tableName";
		echo $sql . ";";
		echo PHP_EOL;
		$connection->exec($sql);

		$sql = "alter table $connection->database.$tableName modify column nama varchar(3000)";
		echo $sql . ";";
		echo PHP_EOL;
		$connection->exec($sql);

		if (!str_contains($info->table, "fungsi")) {
			$sql = "alter table $connection->database.$tableName modify column keterangan varchar(3000)";
			echo $sql . ";";
			echo PHP_EOL;
			$connection->exec($sql);
		}

		if (str_contains($info->table, "urusan")) {
			$sql = "alter table $connection->database.$tableName modify column kinerja varchar(3000)";
			echo $sql . ";";
			echo PHP_EOL;
			$connection->exec($sql);

			$sql = "alter table $connection->database.$tableName modify column indikator varchar(3000)";
			echo $sql . ";";
			echo PHP_EOL;
			$connection->exec($sql);

			$sql = "alter table $connection->database.$tableName modify column satuan varchar(3000)";
			echo $sql . ";";
			echo PHP_EOL;
			$connection->exec($sql);
		}

		if ($j === 0) {
			$sql = "alter table $connection->database.$tableName auto_increment = 0";
			echo $sql . ";";
			echo PHP_EOL;
			$connection->exec($sql);
		}

		echo PHP_EOL;
	}

	for ($j = 0; $j < 2; $j++) {
		$tableName = $info->table;
		if ($j !== 0) $tableName .= "_log";

		$sql = "insert into $connection->database.$tableName select * from $reference.$tableName";
		echo $sql . ";";
		echo PHP_EOL;
		$connection->exec($sql);

		echo PHP_EOL;
	}

	$excel_files = __DIR__ . "/xlsx"
			|> scandir(...)
			|> (fn($x) => array_map(fn($excel_file) => __DIR__ . "/xlsx/" . $excel_file, $x))
			|> (fn($x) => array_filter($x, fn($excel_file) => is_file($excel_file) && pathinfo($excel_file, PATHINFO_EXTENSION) === "xlsx"))
			|> array_values(...);
	switch ($i) {
		case 0:
			$service = Application::getComponent(UrusanProvinsiService::class);
			$service->deleteFromExcelFiles(Peraturan::KEPMENDAGRI_TAHUN_2025_NOMOR_900_1_2850, array_values(array_filter($excel_files, fn($excel_file) => str_starts_with(pathinfo($excel_file, PATHINFO_FILENAME), "B-DELETE"))));
			$service->updateFromExcelFiles(Peraturan::KEPMENDAGRI_TAHUN_2025_NOMOR_900_1_2850, array_values(array_filter($excel_files, fn($excel_file) => str_starts_with(pathinfo($excel_file, PATHINFO_FILENAME), "B-UPDATE"))), false);
			break;
		case 1:
			$service = Application::getComponent(UrusanKabupatenService::class);
			$service->deleteFromExcelFiles(Peraturan::KEPMENDAGRI_TAHUN_2025_NOMOR_900_1_2850, array_values(array_filter($excel_files, fn($excel_file) => str_starts_with(pathinfo($excel_file, PATHINFO_FILENAME), "C-DELETE"))));
			$service->updateFromExcelFiles(Peraturan::KEPMENDAGRI_TAHUN_2025_NOMOR_900_1_2850, array_values(array_filter($excel_files, fn($excel_file) => str_starts_with(pathinfo($excel_file, PATHINFO_FILENAME), "C-UPDATE"))), false);
			break;
		case 2:
			$service = Application::getComponent(FungsiProvinsiService::class);
			$service->deleteFromExcelFiles(Peraturan::KEPMENDAGRI_TAHUN_2025_NOMOR_900_1_2850, array_values(array_filter($excel_files, fn($excel_file) => str_starts_with(pathinfo($excel_file, PATHINFO_FILENAME), "D-DELETE"))));
			$service->updateFromExcelFiles(Peraturan::KEPMENDAGRI_TAHUN_2025_NOMOR_900_1_2850, array_values(array_filter($excel_files, fn($excel_file) => str_starts_with(pathinfo($excel_file, PATHINFO_FILENAME), "D-UPDATE"))), false);
			break;
		case 3:
			$service = Application::getComponent(FungsiKabupatenService::class);
			$service->deleteFromExcelFiles(Peraturan::KEPMENDAGRI_TAHUN_2025_NOMOR_900_1_2850, array_values(array_filter($excel_files, fn($excel_file) => str_starts_with(pathinfo($excel_file, PATHINFO_FILENAME), "E-DELETE"))));
			$service->updateFromExcelFiles(Peraturan::KEPMENDAGRI_TAHUN_2025_NOMOR_900_1_2850, array_values(array_filter($excel_files, fn($excel_file) => str_starts_with(pathinfo($excel_file, PATHINFO_FILENAME), "E-UPDATE"))), false);
			break;
		case 4:
			$service = Application::getComponent(SumberService::class);
			$service->deleteFromExcelFiles(Peraturan::KEPMENDAGRI_TAHUN_2025_NOMOR_900_1_2850, array_values(array_filter($excel_files, fn($excel_file) => str_starts_with(pathinfo($excel_file, PATHINFO_FILENAME), "H-DELETE"))));
			$service->updateFromExcelFiles(Peraturan::KEPMENDAGRI_TAHUN_2025_NOMOR_900_1_2850, array_values(array_filter($excel_files, fn($excel_file) => str_starts_with(pathinfo($excel_file, PATHINFO_FILENAME), "H-UPDATE"))), false);
			break;
		case 5:
			$service = Application::getComponent(NeracaService::class);
			$service->deleteFromExcelFiles(Peraturan::KEPMENDAGRI_TAHUN_2025_NOMOR_900_1_2850, array_values(array_filter($excel_files, fn($excel_file) => str_starts_with(pathinfo($excel_file, PATHINFO_FILENAME), "I-DELETE"))));
			$service->updateFromExcelFiles(Peraturan::KEPMENDAGRI_TAHUN_2025_NOMOR_900_1_2850, array_values(array_filter($excel_files, fn($excel_file) => str_starts_with(pathinfo($excel_file, PATHINFO_FILENAME), "I-UPDATE"))), false);
			break;
		case 6:
			$service = Application::getComponent(LraService::class);
			$service->deleteFromExcelFiles(Peraturan::KEPMENDAGRI_TAHUN_2025_NOMOR_900_1_2850, array_values(array_filter($excel_files, fn($excel_file) => str_starts_with(pathinfo($excel_file, PATHINFO_FILENAME), "J-DELETE"))));
			$service->updateFromExcelFiles(Peraturan::KEPMENDAGRI_TAHUN_2025_NOMOR_900_1_2850, array_values(array_filter($excel_files, fn($excel_file) => str_starts_with(pathinfo($excel_file, PATHINFO_FILENAME), "J-UPDATE"))), false);
			break;
		case 7:
			$service = Application::getComponent(LoService::class);
			$service->deleteFromExcelFiles(Peraturan::KEPMENDAGRI_TAHUN_2025_NOMOR_900_1_2850, array_values(array_filter($excel_files, fn($excel_file) => str_starts_with(pathinfo($excel_file, PATHINFO_FILENAME), "K-DELETE"))));
			$service->updateFromExcelFiles(Peraturan::KEPMENDAGRI_TAHUN_2025_NOMOR_900_1_2850, array_values(array_filter($excel_files, fn($excel_file) => str_starts_with(pathinfo($excel_file, PATHINFO_FILENAME), "K-UPDATE"))), false);
			break;
	}

	$sql = [];
	$sql[] = "select max(length(nama))       as nama";

	if (!str_contains($info->table, "fungsi")) {
		$sql[] = "     , max(length(keterangan)) as keterangan";
	}

	if (str_contains($info->table, "urusan")) {
		$sql[] = "     , max(length(kinerja))    as kinerja";
		$sql[] = "     , max(length(indikator))  as indikator";
		$sql[] = "     , max(length(satuan))     as satuan";
	}

	$sql[] = "from {$info->table}_log";

	if ($fetch_row = $connection->query(implode(PHP_EOL, $sql))->fetch(\PDO::FETCH_NAMED)) {
		for ($j = 0; $j < 2; $j++) {
			$tableName = $info->table;
			if ($j === 0) $tableName .= "_log";

			echo PHP_EOL;

			$len = max(255, $fetch_row["nama"]);
			$sql = "alter table $connection->database.$tableName modify column nama varchar($len)";
			echo $sql . ";";
			echo PHP_EOL;
			$connection->exec($sql);

			if (!str_contains($info->table, "fungsi")) {
				$len = max(255, $fetch_row["keterangan"]);
				$sql = "alter table $connection->database.$tableName modify column keterangan varchar($len)";
				echo $sql . ";";
				echo PHP_EOL;
				$connection->exec($sql);
			}

			if (str_contains($info->table, "urusan")) {
				$len = max(255, $fetch_row["kinerja"]);
				$sql = "alter table $connection->database.$tableName modify column kinerja varchar($len)";
				echo $sql . ";";
				echo PHP_EOL;
				$connection->exec($sql);

				$len = max(255, $fetch_row["indikator"]);
				$sql = "alter table $connection->database.$tableName modify column indikator varchar($len)";
				echo $sql . ";";
				echo PHP_EOL;
				$connection->exec($sql);

				$len = max(255, $fetch_row["satuan"]);
				$sql = "alter table $connection->database.$tableName modify column satuan varchar($len)";
				echo $sql . ";";
				echo PHP_EOL;
				$connection->exec($sql);
			}
		}
	}
}