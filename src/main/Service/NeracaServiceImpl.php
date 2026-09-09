<?php

namespace RendyRobbani\Kodefikasi\Pemda\Service;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use RendyRobbani\Kodefikasi\Pemda\Entity\NeracaEntity;
use RendyRobbani\Kodefikasi\Pemda\Exception\RekeningExistsException;
use RendyRobbani\Kodefikasi\Pemda\Exception\RekeningNotFoundException;
use RendyRobbani\Kodefikasi\Pemda\Peraturan\Peraturan;
use RendyRobbani\Kodefikasi\Pemda\Repository\NeracaLogRepository;
use RendyRobbani\Kodefikasi\Pemda\Repository\NeracaRepository;
use RendyRobbani\Kodefikasi\Pemda\Utility\SpreadsheetUtility;
use RendyRobbani\PHP\Connection\Connection;
use RendyRobbani\PHP\Exception\FileNotFoundException;

class NeracaServiceImpl implements NeracaService
{
	public function __construct(protected Connection          $connection,
	                            protected NeracaRepository    $repository,
	                            protected NeracaLogRepository $logRepository)
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

			/** @var array<string, NeracaEntity> $intoEntities */
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
									$intoEntity->setKeterangan($value);
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
							$intoEntity = new NeracaEntity();

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

							if ($peraturan === Peraturan::PERMENDAGRI_TAHUN_2019_NOMOR_90) {
								if (pathinfo($excel_file, PATHINFO_BASENAME) === "H-00393-00692.xlsx") {
									switch ($row->getRowIndex()) {
										case 672:
										case 674:
											$intoEntity->setNomorRekening4(12);
											$intoEntity->setNomorRekening5(1);
											break;
										case 782:
											$intoEntity->setNomorRekening5(19);
											break;
										case 2278:
											$intoEntity->setNomorRekening4(1);
											break;
										case 2582:
										case 2654:
										case 3176:
											$intoEntity->setNomorRekening6(2);
											break;
										case 3132:
										case 3155:
										case 3158:
											$intoEntity->setNomorRekening5(2);
											break;
										case 3137:
											$intoEntity->setNomorRekening5(3);
											break;
									}
								}
								if (pathinfo($excel_file, PATHINFO_BASENAME) === "H-00693-00992.xlsx") {
									if ($row->getRowIndex() >= 1409 && $row->getRowIndex() < 1548) {
										$intoEntity->setNomorRekening1(1);
										$intoEntity->setNomorRekening2(3);
										$intoEntity->setNomorRekening3(7);
										$intoEntity->setNomorRekening4(3);
										$intoEntity->setNomorRekening5(3);
									}
									if ($row->getRowIndex() >= 3185 && $row->getRowIndex() < 3803) {
										$intoEntity->setNomorRekening1(2);
										$intoEntity->setNomorRekening2(1);
										$intoEntity->setNomorRekening3(6);
										$intoEntity->setNomorRekening4(2);
										$intoEntity->setNomorRekening5(2);
									}
									if ($row->getRowIndex() >= 3803) {
										$intoEntity->setNomorRekening1(2);
										$intoEntity->setNomorRekening2(1);
										$intoEntity->setNomorRekening3(6);
										$intoEntity->setNomorRekening4(2);
										$intoEntity->setNomorRekening5(3);
									}

									switch ($row->getRowIndex()) {
										case 1124:
										case 1127:
										case 1130:
											$intoEntity->setNomorRekening5(2);
											break;
										case 1429:
											$intoEntity->setNomorRekening2(3);
											break;
										case 1696:
										case 1699:
											$intoEntity->setNomorRekening4(1);
											break;
										case 1730:
											$intoEntity->setNomorRekening6(3);
											break;
										case 1764:
										case 1768:
										case 1769:
										case 1771:
											$intoEntity->setNomorRekening3(6);
											break;
									}
								}
								if (pathinfo($excel_file, PATHINFO_BASENAME) === "H-00993-01226.xlsx") {
									switch ($row->getRowIndex()) {
										case 465:
										case 1962:
										case 1965:
										case 1968:
										case 1971:
											$intoEntity->setNomorRekening3(6);
											break;
										case 4028:
										case 4061:
										case 4078:
										case 4094:
										case 4102:
											$intoEntity->setNomorRekening2(1);
											break;
										case 4179:
										case 4384:
											$intoEntity->setNomorRekening6(2);
											break;
										case 4183:
											$intoEntity->setNomorRekening3(7);
											break;
										case 4480:
										case 4483:
											$intoEntity->setNomorRekening5(2);
											break;
									}
								}
							}
						}

						if (!isset($intoEntity)) continue;

						if ($is_perubahan) {
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
											throw new RekeningNotFoundException(RekeningNotFoundException::NERACA, RekeningNotFoundException::LEVEL_1, $kode);
										case 2:
											throw new RekeningNotFoundException(RekeningNotFoundException::NERACA, RekeningNotFoundException::LEVEL_2, $kode);
										case 3:
											throw new RekeningNotFoundException(RekeningNotFoundException::NERACA, RekeningNotFoundException::LEVEL_3, $kode);
										case 4:
											throw new RekeningNotFoundException(RekeningNotFoundException::NERACA, RekeningNotFoundException::LEVEL_4, $kode);
										case 5:
											throw new RekeningNotFoundException(RekeningNotFoundException::NERACA, RekeningNotFoundException::LEVEL_5, $kode);
										case 6:
											throw new RekeningNotFoundException(RekeningNotFoundException::NERACA, RekeningNotFoundException::LEVEL_6, $kode);
									}
								}

								if ($level === $levelEntity && isset($intoEntities[$ID])) {
									switch ($level) {
										case 1:
											throw new RekeningExistsException(RekeningExistsException::NERACA, RekeningExistsException::LEVEL_1, $kode);
										case 2:
											throw new RekeningExistsException(RekeningExistsException::NERACA, RekeningExistsException::LEVEL_2, $kode);
										case 3:
											throw new RekeningExistsException(RekeningExistsException::NERACA, RekeningExistsException::LEVEL_3, $kode);
										case 4:
											throw new RekeningExistsException(RekeningExistsException::NERACA, RekeningExistsException::LEVEL_4, $kode);
										case 5:
											throw new RekeningExistsException(RekeningExistsException::NERACA, RekeningExistsException::LEVEL_5, $kode);
										case 6:
											throw new RekeningExistsException(RekeningExistsException::NERACA, RekeningExistsException::LEVEL_6, $kode);
									}
								}
							}
						}

						if ($fromEntity = $fromEntities[$intoEntity->id()] ?? null) {
							$intoEntity->setIsUpdated(!$intoEntity->isEqual($fromEntity));
							if ($intoEntity->isUpdated()) {
								$intoEntity->setCreatedAt($fromEntity->createdAt());
								$intoEntity->setCreatedBy($fromEntity->createdBy());
								$intoEntity->setUpdatedAt($peraturan->penetapan());
								$intoEntity->setUpdatedBy($peraturan->referensi());

								if ($intoEntity->keterangan() === null && $fromEntity->keterangan() !== null) {
									$intoEntity->setKeterangan($fromEntity->keterangan());
								}
							}
							unset($fromEntities[$intoEntity->id()]);
						}

						if ($peraturan === Peraturan::PERMENDAGRI_TAHUN_2019_NOMOR_90 && in_array($intoEntity->id(), ["2-1-6-2-3-377", "2-1-6-2-3-724"])) {
							$lastId = $intoEntity->id() . "|" . $intoEntity->nama();
						} else {
							$lastId = $intoEntity->id();
						}
						if ($intoEntity->isUpdated() && !in_array($lastId, $updateIds)) $updateIds[] = $lastId;
						$intoEntities[$lastId] = $intoEntity;

						unset($intoEntity);
					}
				}
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
}