<?php

namespace RendyRobbani\Kodefikasi\Pemda\Service;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use RendyRobbani\Kodefikasi\Pemda\Comparator\StringComparator;
use RendyRobbani\Kodefikasi\Pemda\Entity\LoEntity;
use RendyRobbani\Kodefikasi\Pemda\Entity\LraEntity;
use RendyRobbani\Kodefikasi\Pemda\Entity\NeracaEntity;
use RendyRobbani\Kodefikasi\Pemda\Entity\SumberEntity;
use RendyRobbani\Kodefikasi\Pemda\Exception\RekeningExistsException;
use RendyRobbani\Kodefikasi\Pemda\Exception\RekeningNotFoundException;
use RendyRobbani\Kodefikasi\Pemda\Peraturan\Peraturan;
use RendyRobbani\Kodefikasi\Pemda\Repository\LoLogRepository;
use RendyRobbani\Kodefikasi\Pemda\Repository\LoRepository;
use RendyRobbani\Kodefikasi\Pemda\Repository\LraLogRepository;
use RendyRobbani\Kodefikasi\Pemda\Repository\LraRepository;
use RendyRobbani\Kodefikasi\Pemda\Repository\NeracaLogRepository;
use RendyRobbani\Kodefikasi\Pemda\Repository\NeracaRepository;
use RendyRobbani\Kodefikasi\Pemda\Repository\SumberLogRepository;
use RendyRobbani\Kodefikasi\Pemda\Repository\SumberRepository;
use RendyRobbani\Kodefikasi\Pemda\Utility\SpreadsheetUtility;
use RendyRobbani\PHP\Connection\Connection;
use RendyRobbani\PHP\Exception\FileNotFoundException;

abstract class AbstractRekeningService implements DefaultService
{
	const string SUMBER = "Sumber Dana";
	const string NERACA = "Neraca";
	const string LRA = "LRA";
	const string LO = "LO";

	protected Connection $connection;

	protected SumberRepository|NeracaRepository|LraRepository|LoRepository $repository;

	protected SumberLogRepository|NeracaLogRepository|LraLogRepository|LoLogRepository $logRepository;

	protected string $type;

	function updateFromExcelFiles(Peraturan $peraturan, array $excel_files, bool $delete_if_not_exists): void
	{
		if (sizeof($excel_files) === 0) return;
		try {
			$this->connection->beginTransaction();

			$fromEntities = $this->repository->findAll();
			$fromEntities = array_combine(array_map(fn($fromEntity) => $fromEntity->id(), $fromEntities), $fromEntities);

			/** @var array<string, SumberEntity|NeracaEntity|LraEntity|LoEntity> $intoEntities */
			$intoEntities = [];

			$lastID = null;

			foreach ($excel_files as $excel_file) {
				if (!file_exists($excel_file)) throw new FileNotFoundException($excel_file);

				$spreadsheet = new Xlsx()->load($excel_file);
				foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
					foreach ($worksheet->getRowIterator() as $row) {
						echo "insert from : " . pathinfo($excel_file, PATHINFO_BASENAME) . " | row : " . $row->getRowIndex() . PHP_EOL;

						$values = SpreadsheetUtility::getCellValuesAsStringFromRow($worksheet, $row->getRowIndex(), 1, 7);
						if ($values[0] !== null && preg_match("/^[a-wy-z].+/", strtolower($values[0]))) continue;

						$notNull = SpreadsheetUtility::countNotNullColumnsAntNotBlank($values);
						if ($notNull === 0) continue;

						$notNull = SpreadsheetUtility::countNotNullColumnsAntNotBlank(array_slice($values, 0, 6));
						if ($notNull === 0) {
							$value = trim($values[6]);
							if ($lastID !== null) {
								$intoEntity = $intoEntities[$lastID];
								if ($intoEntity->keterangan() === null && preg_match("/^(digu)(.+)?$/", strtolower($value))) {
									$intoEntity->setKeterangan(ucfirst("Digunakan" . substr($value, strpos($value, " "))));
								} elseif ($intoEntity->keterangan() !== null) {
									$intoEntity->setKeterangan(SpreadsheetUtility::cleanValue(implode(" ", [$intoEntity->keterangan(), $value])));
								} else {
									$intoEntity->setNama(SpreadsheetUtility::cleanValue(implode(" ", [$intoEntity->nama(), $value])));
								}
								$intoEntities[$lastID] = $intoEntity;
							}
						} else {
							if ($this->repository instanceof SumberRepository) $intoEntity = new SumberEntity();
							if ($this->repository instanceof NeracaRepository) $intoEntity = new NeracaEntity();
							if ($this->repository instanceof LraRepository) $intoEntity = new LraEntity();
							if ($this->repository instanceof LoRepository) $intoEntity = new LoEntity();

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
									$this->mappingPermendagriTahun2019($row, $excel_file, $intoEntity);
									break;
								case Peraturan::KEPMENDAGRI_TAHUN_2020_NOMOR_050_3708:
									$this->mappingKepmendagriTahun2020($row, $excel_file, $intoEntity);
									break;
								case Peraturan::KEPMENDAGRI_TAHUN_2021_NOMOR_050_5889:
									$this->mappingKepmendagriTahun2021($row, $excel_file, $intoEntity);
									break;
								case Peraturan::KEPMENDAGRI_TAHUN_2023_NOMOR_900_1_15_5_1317:
									$this->mappingKepmendagriTahun2023($row, $excel_file, $intoEntity);
									break;
								case Peraturan::KEPMENDAGRI_TAHUN_2024_NOMOR_900_1_15_5_3406:
									$this->mappingKepmendagriTahun2024($row, $excel_file, $intoEntity);
									break;
								case Peraturan::KEPMENDAGRI_TAHUN_2025_NOMOR_900_1_2850:
									$this->mappingKepmendagriTahun2025($row, $excel_file, $intoEntity);
									break;
								case Peraturan::KEPMENDAGRI_TAHUN_2026_NOMOR_900_1_861:
									$this->mappingKepmendagriTahun2026($row, $excel_file, $intoEntity);
									break;
							}

							$levelEntity = sizeof(explode("-", $intoEntity->id()));

							for ($level = 1; $level <= $levelEntity; $level++) {
								switch ($peraturan) {
									case Peraturan::PERMENDAGRI_TAHUN_2019_NOMOR_90:
										$intoEntities = $this->beforeCheckUpdatePermendagriTahun2019($fromEntities, $intoEntities, $intoEntity);
										break;
									case Peraturan::KEPMENDAGRI_TAHUN_2020_NOMOR_050_3708:
										$intoEntities = $this->beforeCheckUpdateKepmendagriTahun2020($fromEntities, $intoEntities, $intoEntity);
										break;
									case Peraturan::KEPMENDAGRI_TAHUN_2021_NOMOR_050_5889:
										$intoEntities = $this->beforeCheckUpdateKepmendagriTahun2021($fromEntities, $intoEntities, $intoEntity);
										break;
									case Peraturan::KEPMENDAGRI_TAHUN_2023_NOMOR_900_1_15_5_1317:
										$intoEntities = $this->beforeCheckUpdateKepmendagriTahun2023($fromEntities, $intoEntities, $intoEntity);
										break;
									case Peraturan::KEPMENDAGRI_TAHUN_2024_NOMOR_900_1_15_5_3406:
										$intoEntities = $this->beforeCheckUpdateKepmendagriTahun2024($fromEntities, $intoEntities, $intoEntity);
										break;
									case Peraturan::KEPMENDAGRI_TAHUN_2025_NOMOR_900_1_2850:
										$intoEntities = $this->beforeCheckUpdateKepmendagriTahun2025($fromEntities, $intoEntities, $intoEntity);
										break;
									case Peraturan::KEPMENDAGRI_TAHUN_2026_NOMOR_900_1_861:
										$intoEntities = $this->beforeCheckUpdateKepmendagriTahun2026($fromEntities, $intoEntities, $intoEntity);
										break;
								}

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
											throw new RekeningNotFoundException($this->type, RekeningNotFoundException::LEVEL_1, $kode);
										case 2:
											throw new RekeningNotFoundException($this->type, RekeningNotFoundException::LEVEL_2, $kode);
										case 3:
											throw new RekeningNotFoundException($this->type, RekeningNotFoundException::LEVEL_3, $kode);
										case 4:
											throw new RekeningNotFoundException($this->type, RekeningNotFoundException::LEVEL_4, $kode);
										case 5:
											throw new RekeningNotFoundException($this->type, RekeningNotFoundException::LEVEL_5, $kode);
										case 6:
											throw new RekeningNotFoundException($this->type, RekeningNotFoundException::LEVEL_6, $kode);
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

							$lastID = match ($peraturan) {
								Peraturan::PERMENDAGRI_TAHUN_2019_NOMOR_90 => $this->arrayIdPermendagriTahun2019($intoEntity),
								Peraturan::KEPMENDAGRI_TAHUN_2020_NOMOR_050_3708 => $this->arrayIdKepmendagriTahun2020($intoEntity),
								Peraturan::KEPMENDAGRI_TAHUN_2021_NOMOR_050_5889 => $this->arrayIdKepmendagriTahun2021($intoEntity),
								Peraturan::KEPMENDAGRI_TAHUN_2023_NOMOR_900_1_15_5_1317 => $this->arrayIdKepmendagriTahun2023($intoEntity),
								Peraturan::KEPMENDAGRI_TAHUN_2024_NOMOR_900_1_15_5_3406 => $this->arrayIdKepmendagriTahun2024($intoEntity),
								Peraturan::KEPMENDAGRI_TAHUN_2025_NOMOR_900_1_2850 => $this->arrayIdKepmendagriTahun2025($intoEntity),
								Peraturan::KEPMENDAGRI_TAHUN_2026_NOMOR_900_1_861 => $this->arrayIdKepmendagriTahun2026($intoEntity),
							};
							$intoEntities[$lastID] = $intoEntity;
						}
					}
				}
			}

			foreach ($intoEntities as $ID => $intoEntity) {
				if ($intoEntity->keterangan() !== null) {
					$keterangan = trim($intoEntity->keterangan());
					if (!str_ends_with($keterangan, ".")) $keterangan .= ".";
					if (str_ends_with($keterangan, " .")) $keterangan = substr($keterangan, 0, -2) . ".";
					$intoEntity->setKeterangan($keterangan);
				}

				if ($fromEntity = $fromEntities[$intoEntity->id()] ?? null) {
					$intoEntity->setCreatedAt($fromEntity->createdAt());
					$intoEntity->setCreatedBy($fromEntity->createdBy());
					$intoEntity->setUpdatedAt($fromEntity->updatedAt());
					$intoEntity->setUpdatedBy($fromEntity->updatedBy());
					$intoEntity->setIsUpdated(!$intoEntity->isEqual($fromEntity));
					if ($intoEntity->isUpdated()) {
						if (StringComparator::isEqual($fromEntity->nama(), $intoEntity->nama()) && $intoEntity->keterangan() === null && $fromEntity->keterangan() !== null) {
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

				$intoEntities[$ID] = $intoEntity;
			}

			if ($delete_if_not_exists) {
				foreach ($fromEntities as $entity) {
					if ($entity->isDeleted()) continue;

					$entity->setIsDeleted(true);
					$entity->setDeletedAt($peraturan->penetapan());
					$entity->setDeletedBy($peraturan->referensi());

					$this->repository->save($entity);
					$this->logRepository->save($entity->log());
				}
			}

			foreach ($intoEntities as $entity) {
				$this->repository->save($entity);
				if ($entity->isUpdated()) $this->logRepository->save($entity->log());
			}

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

	function deleteFromExcelFiles(Peraturan $peraturan, array $excel_files): void
	{
		if (sizeof($excel_files) === 0) return;
		try {
			$this->connection->beginTransaction();

			$fromEntities = $this->repository->findAll();
			$fromEntities = array_combine(array_map(fn($fromEntity) => $fromEntity->id(), $fromEntities), $fromEntities);

			/** @var (SumberEntity|NeracaEntity|LraEntity|LoEntity)[] $intoEntities */
			$intoEntities = [];

			foreach ($excel_files as $excel_file) {
				if (!file_exists($excel_file)) throw new FileNotFoundException($excel_file);

				$spreadsheet = new Xlsx()->load($excel_file);
				foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
					foreach ($worksheet->getRowIterator() as $row) {
						echo "delete from : " . pathinfo($excel_file, PATHINFO_BASENAME) . " | row : " . $row->getRowIndex() . PHP_EOL;

						$ID = [];
						$values = SpreadsheetUtility::getCellValuesAsStringFromRow($worksheet, $row->getRowIndex(), 1, 6);
						foreach ($values as $value) {
							if ($value === null || $value === "") break;
							$ID[] = $value;
						}

						if (sizeof($ID) === 0) continue;

						$intoEntity = $fromEntities[implode("-", $ID)] ?? null;

						if ($intoEntity === null) continue;
						if ($intoEntity->isDeleted()) continue;

						switch ($peraturan) {
							case Peraturan::PERMENDAGRI_TAHUN_2019_NOMOR_90:
								$intoEntities = $this->beforeCheckDeletePermendagriTahun2019($fromEntities, $intoEntities, $intoEntity);
								break;
							case Peraturan::KEPMENDAGRI_TAHUN_2020_NOMOR_050_3708:
								$intoEntities = $this->beforeCheckDeleteKepmendagriTahun2020($fromEntities, $intoEntities, $intoEntity);
								break;
							case Peraturan::KEPMENDAGRI_TAHUN_2021_NOMOR_050_5889:
								$intoEntities = $this->beforeCheckDeleteKepmendagriTahun2021($fromEntities, $intoEntities, $intoEntity);
								break;
							case Peraturan::KEPMENDAGRI_TAHUN_2023_NOMOR_900_1_15_5_1317:
								$intoEntities = $this->beforeCheckDeleteKepmendagriTahun2023($fromEntities, $intoEntities, $intoEntity);
								break;
							case Peraturan::KEPMENDAGRI_TAHUN_2024_NOMOR_900_1_15_5_3406:
								$intoEntities = $this->beforeCheckDeleteKepmendagriTahun2024($fromEntities, $intoEntities, $intoEntity);
								break;
							case Peraturan::KEPMENDAGRI_TAHUN_2025_NOMOR_900_1_2850:
								$intoEntities = $this->beforeCheckDeleteKepmendagriTahun2025($fromEntities, $intoEntities, $intoEntity);
								break;
							case Peraturan::KEPMENDAGRI_TAHUN_2026_NOMOR_900_1_861:
								$intoEntities = $this->beforeCheckDeleteKepmendagriTahun2026($fromEntities, $intoEntities, $intoEntity);
								break;
						}

						if (isset($intoEntities[$intoEntity->id()])) {
							switch (sizeof($ID)) {
								case 1:
									throw new RekeningExistsException($this->type, RekeningExistsException::LEVEL_1, $intoEntity->kode($peraturan));
								case 2:
									throw new RekeningExistsException($this->type, RekeningExistsException::LEVEL_2, $intoEntity->kode($peraturan));
								case 3:
									throw new RekeningExistsException($this->type, RekeningExistsException::LEVEL_3, $intoEntity->kode($peraturan));
								case 4:
									throw new RekeningExistsException($this->type, RekeningExistsException::LEVEL_4, $intoEntity->kode($peraturan));
								case 5:
									throw new RekeningExistsException($this->type, RekeningExistsException::LEVEL_5, $intoEntity->kode($peraturan));
								case 6:
									throw new RekeningExistsException($this->type, RekeningExistsException::LEVEL_6, $intoEntity->kode($peraturan));
							}
						}

						$intoEntities[$intoEntity->id()] = $intoEntity;
					}
				}
			}

			foreach ($intoEntities as $intoEntity) {
				$intoEntity->setIsDeleted(true);
				$intoEntity->setDeletedAt($peraturan->penetapan());
				$intoEntity->setDeletedBy($peraturan->referensi());
				$this->repository->save($intoEntity);
				$this->logRepository->save($intoEntity->log());
			}

			$this->connection->commit();
		} catch (\Throwable $exception) {
			$this->connection->rollBack();
			throw $exception;
		}
	}

	/**
	 * @param Row $row
	 * @param string $excel_file
	 * @param SumberEntity|NeracaEntity|LraEntity|LoEntity $entity
	 * @return void
	 */
	protected function mappingPermendagriTahun2019(Row $row, string $excel_file, mixed $entity): void
	{
	}

	/**
	 * @param Row $row
	 * @param string $excel_file
	 * @param SumberEntity|NeracaEntity|LraEntity|LoEntity $entity
	 * @return void
	 */
	protected function mappingKepmendagriTahun2020(Row $row, string $excel_file, mixed $entity): void
	{
	}

	/**
	 * @param Row $row
	 * @param string $excel_file
	 * @param SumberEntity|NeracaEntity|LraEntity|LoEntity $entity
	 * @return void
	 */
	protected function mappingKepmendagriTahun2021(Row $row, string $excel_file, mixed $entity): void
	{
	}

	/**
	 * @param Row $row
	 * @param string $excel_file
	 * @param SumberEntity|NeracaEntity|LraEntity|LoEntity $entity
	 * @return void
	 */
	protected function mappingKepmendagriTahun2023(Row $row, string $excel_file, mixed $entity): void
	{
	}

	/**
	 * @param Row $row
	 * @param string $excel_file
	 * @param SumberEntity|NeracaEntity|LraEntity|LoEntity $entity
	 * @return void
	 */
	protected function mappingKepmendagriTahun2024(Row $row, string $excel_file, mixed $entity): void
	{
	}

	/**
	 * @param Row $row
	 * @param string $excel_file
	 * @param SumberEntity|NeracaEntity|LraEntity|LoEntity $entity
	 * @return void
	 */
	protected function mappingKepmendagriTahun2025(Row $row, string $excel_file, mixed $entity): void
	{
	}

	/**
	 * @param Row $row
	 * @param string $excel_file
	 * @param SumberEntity|NeracaEntity|LraEntity|LoEntity $entity
	 * @return void
	 */
	protected function mappingKepmendagriTahun2026(Row $row, string $excel_file, mixed $entity): void
	{
	}

	/**
	 * @param SumberEntity|NeracaEntity|LraEntity|LoEntity $entity
	 * @return string
	 */
	protected function arrayIdPermendagriTahun2019(mixed $entity): string
	{
		return $entity->id();
	}

	/**
	 * @param SumberEntity|NeracaEntity|LraEntity|LoEntity $entity
	 * @return string
	 */
	protected function arrayIdKepmendagriTahun2020(mixed $entity): string
	{
		return $entity->id();
	}

	/**
	 * @param SumberEntity|NeracaEntity|LraEntity|LoEntity $entity
	 * @return string
	 */
	protected function arrayIdKepmendagriTahun2021(mixed $entity): string
	{
		return $entity->id();
	}

	/**
	 * @param SumberEntity|NeracaEntity|LraEntity|LoEntity $entity
	 * @return string
	 */
	protected function arrayIdKepmendagriTahun2023(mixed $entity): string
	{
		return $entity->id();
	}

	/**
	 * @param SumberEntity|NeracaEntity|LraEntity|LoEntity $entity
	 * @return string
	 */
	protected function arrayIdKepmendagriTahun2024(mixed $entity): string
	{
		return $entity->id();
	}

	/**
	 * @param SumberEntity|NeracaEntity|LraEntity|LoEntity $entity
	 * @return string
	 */
	protected function arrayIdKepmendagriTahun2025(mixed $entity): string
	{
		return $entity->id();
	}

	/**
	 * @param SumberEntity|NeracaEntity|LraEntity|LoEntity $entity
	 * @return string
	 */
	protected function arrayIdKepmendagriTahun2026(mixed $entity): string
	{
		return $entity->id();
	}

	/**
	 * @param SumberEntity[]|NeracaEntity[]|LraEntity[]|LoEntity[] $fromEntities
	 * @param SumberEntity[]|NeracaEntity[]|LraEntity[]|LoEntity[] $intoEntities
	 * @param SumberEntity|NeracaEntity|LraEntity|LoEntity $entity
	 * @return array
	 */
	protected function beforeCheckUpdatePermendagriTahun2019(array $fromEntities, array $intoEntities, mixed $entity): array
	{
		return $intoEntities;
	}

	/**
	 * @param SumberEntity[]|NeracaEntity[]|LraEntity[]|LoEntity[] $fromEntities
	 * @param SumberEntity[]|NeracaEntity[]|LraEntity[]|LoEntity[] $intoEntities
	 * @param SumberEntity|NeracaEntity|LraEntity|LoEntity $entity
	 * @return array
	 */
	protected function beforeCheckUpdateKepmendagriTahun2020(array $fromEntities, array $intoEntities, mixed $entity): array
	{
		return $intoEntities;
	}

	/**
	 * @param SumberEntity[]|NeracaEntity[]|LraEntity[]|LoEntity[] $fromEntities
	 * @param SumberEntity[]|NeracaEntity[]|LraEntity[]|LoEntity[] $intoEntities
	 * @param SumberEntity|NeracaEntity|LraEntity|LoEntity $entity
	 * @return array
	 */
	protected function beforeCheckUpdateKepmendagriTahun2021(array $fromEntities, array $intoEntities, mixed $entity): array
	{
		return $intoEntities;
	}

	/**
	 * @param SumberEntity[]|NeracaEntity[]|LraEntity[]|LoEntity[] $fromEntities
	 * @param SumberEntity[]|NeracaEntity[]|LraEntity[]|LoEntity[] $intoEntities
	 * @param SumberEntity|NeracaEntity|LraEntity|LoEntity $entity
	 * @return array
	 */
	protected function beforeCheckUpdateKepmendagriTahun2023(array $fromEntities, array $intoEntities, mixed $entity): array
	{
		return $intoEntities;
	}

	/**
	 * @param SumberEntity[]|NeracaEntity[]|LraEntity[]|LoEntity[] $fromEntities
	 * @param SumberEntity[]|NeracaEntity[]|LraEntity[]|LoEntity[] $intoEntities
	 * @param SumberEntity|NeracaEntity|LraEntity|LoEntity $entity
	 * @return array
	 */
	protected function beforeCheckUpdateKepmendagriTahun2024(array $fromEntities, array $intoEntities, mixed $entity): array
	{
		$listID = explode("-", $entity->id());
		for ($i = 1; $i < sizeof($listID); $i++) {
			$ID = implode("-", array_slice($listID, 0, $i));
			if (!isset($intoEntities[$ID]) && isset($fromEntities[$ID])) {
				$intoEntities[$ID] = $fromEntities[$ID];
				unset($fromEntities[$ID]);
			}
		}
		return $intoEntities;
	}

	/**
	 * @param SumberEntity[]|NeracaEntity[]|LraEntity[]|LoEntity[] $fromEntities
	 * @param SumberEntity[]|NeracaEntity[]|LraEntity[]|LoEntity[] $intoEntities
	 * @param SumberEntity|NeracaEntity|LraEntity|LoEntity $entity
	 * @return array
	 */
	protected function beforeCheckUpdateKepmendagriTahun2025(array $fromEntities, array $intoEntities, mixed $entity): array
	{
		$listFromID = explode("-", $entity->id());
		for ($i = 1; $i < sizeof($listFromID); $i++) {
			$fromID = implode("-", array_slice($listFromID, 0, $i));
			if (!isset($intoEntities[$fromID]) && isset($fromEntities[$fromID])) {
				$intoEntities[$fromID] = $fromEntities[$fromID];
				unset($fromEntities[$fromID]);
			}
		}
		return $intoEntities;
	}

	/**
	 * @param SumberEntity[]|NeracaEntity[]|LraEntity[]|LoEntity[] $fromEntities
	 * @param SumberEntity[]|NeracaEntity[]|LraEntity[]|LoEntity[] $intoEntities
	 * @param SumberEntity|NeracaEntity|LraEntity|LoEntity $entity
	 * @return array
	 */
	protected function beforeCheckUpdateKepmendagriTahun2026(array $fromEntities, array $intoEntities, mixed $entity): array
	{
		return $intoEntities;
	}

	protected function beforeCheckDeletePermendagriTahun2019(array $fromEntities, array $intoEntities, mixed $entity): array
	{
		return $intoEntities;
	}

	/**
	 * @param SumberEntity[]|NeracaEntity[]|LraEntity[]|LoEntity[] $fromEntities
	 * @param SumberEntity[]|NeracaEntity[]|LraEntity[]|LoEntity[] $intoEntities
	 * @param SumberEntity|NeracaEntity|LraEntity|LoEntity $entity
	 * @return array
	 */
	protected function beforeCheckDeleteKepmendagriTahun2020(array $fromEntities, array $intoEntities, mixed $entity): array
	{
		return $intoEntities;
	}

	/**
	 * @param SumberEntity[]|NeracaEntity[]|LraEntity[]|LoEntity[] $fromEntities
	 * @param SumberEntity[]|NeracaEntity[]|LraEntity[]|LoEntity[] $intoEntities
	 * @param SumberEntity|NeracaEntity|LraEntity|LoEntity $entity
	 * @return array
	 */
	protected function beforeCheckDeleteKepmendagriTahun2021(array $fromEntities, array $intoEntities, mixed $entity): array
	{
		return $intoEntities;
	}

	/**
	 * @param SumberEntity[]|NeracaEntity[]|LraEntity[]|LoEntity[] $fromEntities
	 * @param SumberEntity[]|NeracaEntity[]|LraEntity[]|LoEntity[] $intoEntities
	 * @param SumberEntity|NeracaEntity|LraEntity|LoEntity $entity
	 * @return array
	 */
	protected function beforeCheckDeleteKepmendagriTahun2023(array $fromEntities, array $intoEntities, mixed $entity): array
	{
		return $intoEntities;
	}

	/**
	 * @param SumberEntity[]|NeracaEntity[]|LraEntity[]|LoEntity[] $fromEntities
	 * @param SumberEntity[]|NeracaEntity[]|LraEntity[]|LoEntity[] $intoEntities
	 * @param SumberEntity|NeracaEntity|LraEntity|LoEntity $entity
	 * @return array
	 */
	protected function beforeCheckDeleteKepmendagriTahun2024(array $fromEntities, array $intoEntities, mixed $entity): array
	{
		if (isset($intoEntities[$entity->id()])) {
			unset($intoEntities[$entity->id()]);
		}
		return $intoEntities;
	}

	/**
	 * @param SumberEntity[]|NeracaEntity[]|LraEntity[]|LoEntity[] $fromEntities
	 * @param SumberEntity[]|NeracaEntity[]|LraEntity[]|LoEntity[] $intoEntities
	 * @param SumberEntity|NeracaEntity|LraEntity|LoEntity $entity
	 * @return array
	 */
	protected function beforeCheckDeleteKepmendagriTahun2025(array $fromEntities, array $intoEntities, mixed $entity): array
	{
		return $intoEntities;
	}

	/**
	 * @param SumberEntity[]|NeracaEntity[]|LraEntity[]|LoEntity[] $fromEntities
	 * @param SumberEntity[]|NeracaEntity[]|LraEntity[]|LoEntity[] $intoEntities
	 * @param SumberEntity|NeracaEntity|LraEntity|LoEntity $entity
	 * @return array
	 */
	protected function beforeCheckDeleteKepmendagriTahun2026(array $fromEntities, array $intoEntities, mixed $entity): array
	{
		return $intoEntities;
	}
}