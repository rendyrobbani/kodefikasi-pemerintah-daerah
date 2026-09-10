<?php

namespace RendyRobbani\Kodefikasi\Pemda\Service;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use RendyRobbani\Kodefikasi\Pemda\Comparator\StringComparator;
use RendyRobbani\Kodefikasi\Pemda\Entity\SumberEntity;
use RendyRobbani\Kodefikasi\Pemda\Exception\RekeningExistsException;
use RendyRobbani\Kodefikasi\Pemda\Exception\RekeningNotFoundException;
use RendyRobbani\Kodefikasi\Pemda\Peraturan\Peraturan;
use RendyRobbani\Kodefikasi\Pemda\Repository\SumberLogRepository;
use RendyRobbani\Kodefikasi\Pemda\Repository\SumberRepository;
use RendyRobbani\Kodefikasi\Pemda\Utility\SpreadsheetUtility;
use RendyRobbani\PHP\Connection\Connection;
use RendyRobbani\PHP\Exception\FileNotFoundException;

class SumberServiceImpl implements SumberService
{
	public function __construct(protected Connection          $connection,
	                            protected SumberRepository    $repository,
	                            protected SumberLogRepository $logRepository)
	{
	}

	/**
	 * @inheritDoc
	 * @throws \Throwable
	 */
	function fromExcelFiles(Peraturan $peraturan, array $excel_files, bool $is_perubahan): void
	{
		try {
			$this->connection->beginTransaction();

			$fromEntities = $this->repository->findAll();
			$fromEntities = array_combine(array_map(fn($fromEntity) => $fromEntity->id(), $fromEntities), $fromEntities);

			/** @var array<string, SumberEntity> $intoEntities */
			$intoEntities = [];

			$updateIds = [];
			$lastId = null;

			foreach ($excel_files as $excel_file) {
				if (!file_exists($excel_file)) throw new FileNotFoundException($excel_file);

				$spreadsheet = new Xlsx()->load($excel_file);
				foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
					foreach ($worksheet->getRowIterator() as $row) {
						echo "Import from : " . pathinfo($excel_file, PATHINFO_BASENAME) . " | row : " . $row->getRowIndex() . PHP_EOL;

						$values = SpreadsheetUtility::getCellValuesAsStringFromRow($worksheet, $row->getRowIndex(), 1, 7);
						if ($values[0] !== null && preg_match("/^[a-wy-z].+/", strtolower($values[0]))) continue;

						$notNull = SpreadsheetUtility::countNotNullColumns($values);
						if ($notNull === 0) continue;

						$notNull = SpreadsheetUtility::countNotNullColumns(array_slice($values, 0, 6));
						if ($notNull === 0) {
							$value = trim($values[6]);
							if ($lastId !== null) {
								$intoEntity = $intoEntities[$lastId];
								if (preg_match("/^(digu)(.+)?$/", strtolower($value))) {
									$intoEntity->setKeterangan(ucfirst("Digunakan" . substr($value, strpos($value, " "))));
								} elseif ($intoEntity->keterangan() !== null) {
									$keterangan = $intoEntity->keterangan();
									while (str_ends_with($keterangan, ".")) $keterangan = substr($keterangan, 0, strrpos($keterangan, "."));
									$keterangan = SpreadsheetUtility::cleanValue(implode(" ", [$keterangan, $value]));
									$intoEntity->setKeterangan($keterangan);
								} else {
									$intoEntity->setNama(SpreadsheetUtility::cleanValue(implode(" ", [$intoEntity->nama(), $value])));
								}
								unset($intoEntities[$intoEntity->id()]);
							}
						} else {
							$intoEntity = new SumberEntity();

							for ($colNum = 1; $colNum <= sizeof($values); $colNum++) {
								$value = $values[$colNum - 1];

								switch ($colNum) {
									case 1:
										$intoEntity->setNomorRekening1($value === null ? null : intval($value));
										break;
									case 2:
										$intoEntity->setNomorRekening2($value === null ? null : intval($value));
										break;
									case 3:
										$intoEntity->setNomorRekening3($value === null ? null : intval($value));
										break;
									case 4:
										$intoEntity->setNomorRekening4($value === null ? null : intval($value));
										break;
									case 5:
										$intoEntity->setNomorRekening5($value === null ? null : intval($value));
										break;
									case 6:
										$intoEntity->setNomorRekening6($value === null ? null : intval($value));
										break;
									case 7:
										$intoEntity->setNama($value);
										break;
								}
							}

							$intoEntity->setCreatedAt($peraturan->penetapan());
							$intoEntity->setCreatedBy($peraturan->referensi());
							$intoEntity->setIsUpdated(true);
							$intoEntity->setIsDeleted(false);

							switch ($peraturan) {
								case Peraturan::PERMENDAGRI_TAHUN_2019_NOMOR_90:
									$this->handlePermendagriTahun2019($row, $excel_file, $intoEntity);
									break;
								case Peraturan::KEPMENDAGRI_TAHUN_2020_NOMOR_050_3708:
									$this->handleKepmendagriTahun2020($row, $excel_file, $intoEntity);
									break;
								case Peraturan::KEPMENDAGRI_TAHUN_2021_NOMOR_050_5889:
									$this->handleKepmendagriTahun2021($row, $excel_file, $intoEntity);
									break;
								case Peraturan::KEPMENDAGRI_TAHUN_2023_NOMOR_900_1_15_5_1317:
									$this->handleKepmendagriTahun2023($row, $excel_file, $intoEntity);
									break;
								case Peraturan::KEPMENDAGRI_TAHUN_2024_NOMOR_900_1_15_5_3406:
									$this->handleKepmendagriTahun2024($row, $excel_file, $intoEntity);
									break;
								case Peraturan::KEPMENDAGRI_TAHUN_2025_NOMOR_900_1_2850:
									$this->handleKepmendagriTahun2025($row, $excel_file, $intoEntity);
									break;
								case Peraturan::KEPMENDAGRI_TAHUN_2026_NOMOR_900_1_861:
									$this->handleKepmendagriTahun2026($row, $excel_file, $intoEntity);
									break;
							}
						}

						if (!isset($intoEntity)) continue;

						if ($is_perubahan) {
							if ($peraturan === Peraturan::KEPMENDAGRI_TAHUN_2021_NOMOR_050_5889) {
								switch ($intoEntity->kode($peraturan)) {
									case "1.2.2.02": // Bantuan Keuangan
										$listFromID = explode("-", $intoEntity->id());
										for ($i = 1; $i < sizeof($listFromID); $i++) {
											$fromID = implode("-", array_slice($listFromID, 0, $i));
											if (!isset($intoEntities[$fromID]) && isset($fromEntities[$fromID])) {
												$intoEntities[$fromID] = $fromEntities[$fromID];
												unset($fromEntities[$fromID]);
											}
										}
										break;
								}
							}
							$levelEntity = sizeof(explode("-", $intoEntity->id()));

							for ($level = 1; $level <= $levelEntity; $level++) {
								$ID = [];
								$kode = [];
								if ($level >= 1) {
									$ID[] = $intoEntity->nomorRekening1();
									$kode[] = $intoEntity->kodeRekening1();
								}
								if ($level >= 2) {
									$ID[] = $intoEntity->nomorRekening2();
									$kode[] = $intoEntity->kodeRekening2();
								}
								if ($level >= 3) {
									$ID[] = $intoEntity->nomorRekening3();
									$kode[] = $intoEntity->kodeRekening3($peraturan);
								}
								if ($level >= 4) {
									$ID[] = $intoEntity->nomorRekening4();
									$kode[] = $intoEntity->kodeRekening4();
								}
								if ($level >= 5) {
									$ID[] = $intoEntity->nomorRekening5();
									$kode[] = $intoEntity->kodeRekening5($peraturan);
								}
								if ($level >= 6) {
									$ID[] = $intoEntity->nomorRekening6();
									$kode[] = $intoEntity->kodeRekening6($peraturan);
								}

								$ID = implode("-", $ID);
								$kode = implode(".", $kode);

								if ($level < $levelEntity && !isset($intoEntities[$ID])) {
									switch ($level) {
										case 1:
											throw new RekeningNotFoundException(RekeningNotFoundException::SUMBER, RekeningNotFoundException::LEVEL_1, $kode);
										case 2:
											throw new RekeningNotFoundException(RekeningNotFoundException::SUMBER, RekeningNotFoundException::LEVEL_2, $kode);
										case 3:
											throw new RekeningNotFoundException(RekeningNotFoundException::SUMBER, RekeningNotFoundException::LEVEL_3, $kode);
										case 4:
											throw new RekeningNotFoundException(RekeningNotFoundException::SUMBER, RekeningNotFoundException::LEVEL_4, $kode);
										case 5:
											throw new RekeningNotFoundException(RekeningNotFoundException::SUMBER, RekeningNotFoundException::LEVEL_5, $kode);
										case 6:
											throw new RekeningNotFoundException(RekeningNotFoundException::SUMBER, RekeningNotFoundException::LEVEL_6, $kode);
									}
								}

								if ($level === $levelEntity && isset($intoEntities[$ID])) {
									switch ($level) {
										case 1:
											throw new RekeningExistsException(RekeningExistsException::SUMBER, RekeningExistsException::LEVEL_1, $kode);
										case 2:
											throw new RekeningExistsException(RekeningExistsException::SUMBER, RekeningExistsException::LEVEL_2, $kode);
										case 3:
											throw new RekeningExistsException(RekeningExistsException::SUMBER, RekeningExistsException::LEVEL_3, $kode);
										case 4:
											throw new RekeningExistsException(RekeningExistsException::SUMBER, RekeningExistsException::LEVEL_4, $kode);
										case 5:
											throw new RekeningExistsException(RekeningExistsException::SUMBER, RekeningExistsException::LEVEL_5, $kode);
										case 6:
											throw new RekeningExistsException(RekeningExistsException::SUMBER, RekeningExistsException::LEVEL_6, $kode);
									}
								}
							}
						}

						$lastId = $intoEntity->id();
						$intoEntities[$lastId] = $intoEntity;
						unset($intoEntity);
					}
				}
			}

			foreach ($intoEntities as $ID => $intoEntity) {
				if ($intoEntity->keterangan() !== null) {
					$intoEntity->setKeterangan(trim($intoEntity->keterangan()));
					if ($intoEntity->keterangan() !== "" &&
						!str_ends_with($intoEntity->keterangan(), "."))
						$intoEntity->setKeterangan(trim($intoEntity->keterangan()) . ".");
					if (str_ends_with($intoEntity->keterangan(), " .")) $intoEntity->setKeterangan(substr($intoEntity->keterangan(), 0, strlen($intoEntity->keterangan()) - 2) . ".");
				}
				if ($fromEntity = $fromEntities[$intoEntity->id()] ?? null) {
					$intoEntity->setCreatedAt($fromEntity->createdAt());
					$intoEntity->setCreatedBy($fromEntity->createdBy());
					$intoEntity->setIsUpdated(!$intoEntity->isEqual($fromEntity));
					if ($intoEntity->isUpdated()) {
						if (StringComparator::isEqual($fromEntity->nama(), $intoEntity->nama()) &&
							$intoEntity->keterangan() === null && $fromEntity->keterangan() !== null) {
							$intoEntity->setKeterangan($fromEntity->keterangan());
						}

						$intoEntity->setIsUpdated(!$intoEntity->isEqual($fromEntity));
						if ($intoEntity->isUpdated()) {
							$intoEntity->setUpdatedAt($peraturan->penetapan());
							$intoEntity->setUpdatedBy($peraturan->referensi());
						}
					}
					unset($fromEntities[$intoEntity->id()]);
				}
				if ($intoEntity->isUpdated()) $updateIds[] = $ID;
			}

			foreach ($fromEntities as $entity) {
				if ($entity->isDeleted()) continue;

				$entity->setIsDeleted(true);
				$entity->setDeletedAt($peraturan->penetapan());
				$entity->setDeletedBy($peraturan->referensi());

				$this->repository->save($entity);
				$this->logRepository->save($entity->log());
			}

			foreach ($intoEntities as $entity) $this->repository->save($entity);
			foreach ($updateIds as $id) $this->logRepository->save($intoEntities[$id]->log());

			$this->connection->commit();
		} catch (\Throwable $exception) {
			$this->connection->rollBack();

			$entity = $entity ?? $intoEntity ?? null;
			if ($entity !== null) {
				$len_id = $entity->id() === null ? 0 : strlen($entity->id());
				$len_id = str_pad($len_id, 4, " ", STR_PAD_LEFT);

				$len_kode = $entity->kode($peraturan) === null ? 0 : strlen($entity->kode($peraturan));
				$len_kode = str_pad($len_kode, 4, " ", STR_PAD_LEFT);

				$len_nama = $entity->nama() === null ? 0 : strlen($entity->nama());
				$len_nama = str_pad($len_nama, 4, " ", STR_PAD_LEFT);

				$len_keterangan = $entity->keterangan() === null ? 0 : strlen($entity->keterangan());
				$len_keterangan = str_pad($len_keterangan, 4, " ", STR_PAD_LEFT);

				echo PHP_EOL;
				echo "id         : " . $len_id . " : " . $entity->id() . PHP_EOL;
				echo "kode       : " . $len_kode . " : " . $entity->kode($peraturan) . PHP_EOL;
				echo "nama       : " . $len_nama . " : " . $entity->nama() . PHP_EOL;
				echo "keterangan : " . $len_keterangan . " : " . $entity->keterangan() . PHP_EOL;
				echo PHP_EOL;
			}

			throw $exception;
		}
	}

	private function handlePermendagriTahun2019(Row $row, mixed $excel_file, SumberEntity $entity): void
	{
	}

	private function handleKepmendagriTahun2020(Row $row, string $excel_file, SumberEntity $entity): void
	{
	}

	private function handleKepmendagriTahun2021(Row $row, string $excel_file, SumberEntity $entity): void
	{
	}

	private function handleKepmendagriTahun2023(Row $row, string $excel_file, SumberEntity $entity): void
	{
	}

	private function handleKepmendagriTahun2024(Row $row, string $excel_file, SumberEntity $entity): void
	{
	}

	private function handleKepmendagriTahun2025(Row $row, string $excel_file, SumberEntity $entity): void
	{
	}

	private function handleKepmendagriTahun2026(Row $row, string $excel_file, SumberEntity $entity): void
	{
	}
}