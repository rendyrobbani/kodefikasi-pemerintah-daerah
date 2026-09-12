<?php

namespace RendyRobbani\Kodefikasi\Pemda\Service;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use RendyRobbani\Kodefikasi\Pemda\Entity\UrusanKabupatenEntity;
use RendyRobbani\Kodefikasi\Pemda\Entity\UrusanProvinsiEntity;
use RendyRobbani\Kodefikasi\Pemda\Entity\UrusanProvinsiLogEntity;
use RendyRobbani\Kodefikasi\Pemda\Exception\BidangExistsException;
use RendyRobbani\Kodefikasi\Pemda\Exception\BidangNotFoundException;
use RendyRobbani\Kodefikasi\Pemda\Exception\KegiatanExistsException;
use RendyRobbani\Kodefikasi\Pemda\Exception\KegiatanNotFoundException;
use RendyRobbani\Kodefikasi\Pemda\Exception\ProgramExistsException;
use RendyRobbani\Kodefikasi\Pemda\Exception\ProgramNotFoundException;
use RendyRobbani\Kodefikasi\Pemda\Exception\SubkegiatanExistsException;
use RendyRobbani\Kodefikasi\Pemda\Exception\SubkegiatanNotFoundException;
use RendyRobbani\Kodefikasi\Pemda\Exception\UrusanExistsException;
use RendyRobbani\Kodefikasi\Pemda\Exception\UrusanNotFoundException;
use RendyRobbani\Kodefikasi\Pemda\Peraturan\Peraturan;
use RendyRobbani\Kodefikasi\Pemda\Repository\UrusanKabupatenLogRepository;
use RendyRobbani\Kodefikasi\Pemda\Repository\UrusanKabupatenRepository;
use RendyRobbani\Kodefikasi\Pemda\Repository\UrusanProvinsiLogRepository;
use RendyRobbani\Kodefikasi\Pemda\Repository\UrusanProvinsiRepository;
use RendyRobbani\Kodefikasi\Pemda\Utility\SpreadsheetUtility;
use RendyRobbani\PHP\Connection\Connection;
use RendyRobbani\PHP\Exception\FileNotFoundException;

abstract class AbstractUrusanService implements DefaultService
{
	protected Connection $connection;

	protected UrusanProvinsiRepository|UrusanKabupatenRepository $repository;

	protected UrusanProvinsiLogRepository|UrusanKabupatenLogRepository $logRepository;

	function updateFromExcelFiles(Peraturan $peraturan, array $excel_files, bool $delete_if_not_exists): void
	{
		if (sizeof($excel_files) === 0) return;
		try {
			$this->connection->beginTransaction();

			$fromEntities = $this->repository->findAll();
			$fromEntities = array_combine(array_map(fn($fromEntity) => $fromEntity->id(), $fromEntities), $fromEntities);

			/** @var array<string, UrusanProvinsiEntity|UrusanKabupatenEntity> $intoEntities */
			$intoEntities = [];

			$lastID = null;

			foreach ($excel_files as $excel_file) {
				if (!file_exists($excel_file)) throw new FileNotFoundException($excel_file);

				$spreadsheet = new Xlsx()->load($excel_file);
				foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
					foreach ($worksheet->getRowIterator() as $row) {
						echo "insert from : " . pathinfo($excel_file, PATHINFO_BASENAME) . " | row : " . $row->getRowIndex() . PHP_EOL;

						$values = SpreadsheetUtility::getCellValuesAsStringFromRow($worksheet, $row->getRowIndex(), 1, 9);
						if ($values[0] !== null && preg_match("/^[a-wy-z].+/", strtolower($values[0]))) continue;

						$notNull = SpreadsheetUtility::countNotNullColumnsAntNotBlank($values);
						if ($notNull === 0) continue;

						$notNull = SpreadsheetUtility::countNotNullColumnsAntNotBlank(array_slice($values, 0, 5));
						if ($notNull === 0) {
							$value = trim($values[5]);
							if ($lastID !== null) {
								$intoEntity = $intoEntities[$lastID];
								if (preg_match("/^(tidak ada kewenangan|\(kegiatan|\(sub kegiatan)(.+)?$/", strtolower($value))) {
									$intoEntity->setKeterangan(ucfirst(strtolower($value)));
								} else {
									$intoEntity->setNama(SpreadsheetUtility::cleanValue(implode(" ", [$intoEntity->nama(), $value])));
								}
								$intoEntities[$lastID] = $intoEntity;
							}
						} else {
							if ($this->repository instanceof UrusanProvinsiRepository) $intoEntity = new UrusanProvinsiEntity();
							if ($this->repository instanceof UrusanKabupatenRepository) $intoEntity = new UrusanKabupatenEntity();

							for ($colNum = 1; $colNum <= sizeof($values); $colNum++) {
								$value = $values[$colNum - 1];

								switch ($colNum) {
									case 1:
										$intoEntity->setNomorUrusan($value === null ? null : intval(str_ireplace("X", "0", $value)));
										break;
									case 2:
										$intoEntity->setNomorBidang($value === null ? null : intval(str_ireplace("X", "0", $value)));
										break;
									case 3:
										$intoEntity->setNomorProgram($value === null ? null : intval($value));
										break;
									case 4:
										if (is_float($value)) $value = round($value, 2);
										if ($value !== null && preg_match("/^(.)\.(.{2})$/", sprintf("%.2f", $value), $matches)) {
											$intoEntity->setNomorKegiatan1(intval($matches[1]));
											$intoEntity->setNomorKegiatan2(intval($matches[2]));
										}
										break;
									case 5:
										$intoEntity->setNomorSubkegiatan($value === null ? null : intval($value));
										break;
									case 6:
										$intoEntity->setNama(in_array($intoEntity->kode($peraturan), ["X", "X.XX"]) ? null : $value);
										break;
									case 7:
										$intoEntity->setKinerja($value);
										break;
									case 8:
										$intoEntity->setIndikator($value);
										break;
									case 9:
										$intoEntity->setSatuan($value);
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
							$levelEntity = $levelEntity < 5 ? $levelEntity : $levelEntity - 1;

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
									$ID[] = $intoEntity->nomorUrusan();
									$kode[] = $intoEntity->kodeUrusan();
								}
								if ($level >= 2) {
									$ID[] = $intoEntity->nomorBidang();
									$kode[] = $intoEntity->kodeBidang();
								}
								if ($level >= 3) {
									$ID[] = $intoEntity->nomorProgram();
									$kode[] = $intoEntity->kodeProgram();
								}
								if ($level >= 4) {
									$ID[] = $intoEntity->nomorKegiatan1();
									$ID[] = $intoEntity->nomorKegiatan2();
									$kode[] = $intoEntity->kodeKegiatan();
								}
								if ($level >= 5) {
									$ID[] = $intoEntity->nomorSubkegiatan();
									$kode[] = $intoEntity->kodeSubkegiatan($peraturan);
								}

								$ID = implode("-", $ID);
								$kode = implode(".", $kode);

								if ($level < $levelEntity && !isset($intoEntities[$ID])) {
									switch ($level) {
										case 1:
											throw new UrusanNotFoundException($kode);
										case 2:
											throw new BidangNotFoundException($kode);
										case 3:
											throw new ProgramNotFoundException($kode);
										case 4:
											throw new KegiatanNotFoundException($kode);
										case 5:
											throw new SubkegiatanNotFoundException($kode);
									}
								}

								if ($level === $levelEntity && isset($intoEntities[$ID])) {
									switch ($level) {
										case 1:
											throw new UrusanExistsException($kode);
										case 2:
											throw new BidangExistsException($kode);
										case 3:
											throw new ProgramExistsException($kode);
										case 4:
											throw new KegiatanExistsException($kode);
										case 5:
											throw new SubkegiatanExistsException($kode);
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
				if ($fromEntity = $fromEntities[$intoEntity->id()] ?? null) {
					$intoEntity->setCreatedAt($fromEntity->createdAt());
					$intoEntity->setCreatedBy($fromEntity->createdBy());
					$intoEntity->setUpdatedAt($fromEntity->updatedAt());
					$intoEntity->setUpdatedBy($fromEntity->updatedBy());
					$intoEntity->setIsUpdated(!$intoEntity->isEqual($fromEntity));
					$intoEntity->setIsUpdated(!$intoEntity->isEqual($fromEntity));
					if ($intoEntity->isUpdated()) {
						$intoEntity->setUpdatedAt($peraturan->penetapan());
						$intoEntity->setUpdatedBy($peraturan->referensi());
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

				$len_kinerja = $entity->kinerja() === null ? 0 : strlen($entity->kinerja());
				$len_kinerja = str_pad($len_kinerja, 4, " ", STR_PAD_LEFT);

				$len_indikator = $entity->indikator() === null ? 0 : strlen($entity->indikator());
				$len_indikator = str_pad($len_indikator, 4, " ", STR_PAD_LEFT);

				$len_satuan = $entity->satuan() === null ? 0 : strlen($entity->satuan());
				$len_satuan = str_pad($len_satuan, 4, " ", STR_PAD_LEFT);

				echo PHP_EOL;
				echo "id         : " . $len_id . " : " . $entity->id() . PHP_EOL;
				echo "kode       : " . $len_kode . " : " . $entity->kode($peraturan) . PHP_EOL;
				echo "nama       : " . $len_nama . " : " . $entity->nama() . PHP_EOL;
				echo "keterangan : " . $len_keterangan . " : " . $entity->keterangan() . PHP_EOL;
				echo "kinerja    : " . $len_kinerja . " : " . $entity->kinerja() . PHP_EOL;
				echo "indikator  : " . $len_indikator . " : " . $entity->indikator() . PHP_EOL;
				echo "satuan     : " . $len_satuan . " : " . $entity->satuan() . PHP_EOL;
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

			/** @var (UrusanProvinsiEntity|UrusanProvinsiLogEntity)[] $intoEntities */
			$intoEntities = [];

			foreach ($excel_files as $excel_file) {
				if (!file_exists($excel_file)) throw new FileNotFoundException($excel_file);

				$spreadsheet = new Xlsx()->load($excel_file);
				foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
					foreach ($worksheet->getRowIterator() as $row) {
						echo "delete from : " . pathinfo($excel_file, PATHINFO_BASENAME) . " | row : " . $row->getRowIndex() . PHP_EOL;

						$ID = [];
						$values = SpreadsheetUtility::getCellValuesAsStringFromRow($worksheet, $row->getRowIndex(), 1, $peraturan->tahun() < 2025 ? 5 : 1);
						foreach ($values as $value) {
							if ($value === null || $value === "") break;
							if (is_float($value)) $value = round($value, 2);
							$value = str_replace(".", "-", $value);
							$value = str_replace(",", "-", $value);
							if (str_contains($value, "-")) {
								$ID = array_merge($ID, array_map("intval", explode("-", $value)));
							} else {
								$ID[] = $value;
							}
						}

						if (sizeof($ID) === 0) continue;

						$intoEntity = $fromEntities[implode("-", $ID)] ?? null;

						if ($intoEntity === null) continue;
						if ($intoEntity->isDeleted()) continue;

						if (isset($intoEntities[$intoEntity->id()])) {
							switch (sizeof($ID)) {
								case 1:
									throw new UrusanNotFoundException($intoEntity->kode($peraturan));
								case 2:
									throw new BidangNotFoundException($intoEntity->kode($peraturan));
								case 3:
									throw new ProgramNotFoundException($intoEntity->kode($peraturan));
								case 4:
									throw new KegiatanNotFoundException($intoEntity->kode($peraturan));
								case 5:
									throw new SubkegiatanNotFoundException($intoEntity->kode($peraturan));
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
	 * @param UrusanProvinsiEntity|UrusanKabupatenEntity $entity
	 * @return void
	 */
	protected function mappingPermendagriTahun2019(Row $row, string $excel_file, mixed $entity): void
	{
	}

	/**
	 * @param Row $row
	 * @param string $excel_file
	 * @param UrusanProvinsiEntity|UrusanKabupatenEntity $entity
	 * @return void
	 */
	protected function mappingKepmendagriTahun2020(Row $row, string $excel_file, mixed $entity): void
	{
	}

	/**
	 * @param Row $row
	 * @param string $excel_file
	 * @param UrusanProvinsiEntity|UrusanKabupatenEntity $entity
	 * @return void
	 */
	protected function mappingKepmendagriTahun2021(Row $row, string $excel_file, mixed $entity): void
	{
	}

	/**
	 * @param Row $row
	 * @param string $excel_file
	 * @param UrusanProvinsiEntity|UrusanKabupatenEntity $entity
	 * @return void
	 */
	protected function mappingKepmendagriTahun2023(Row $row, string $excel_file, mixed $entity): void
	{
	}

	/**
	 * @param Row $row
	 * @param string $excel_file
	 * @param UrusanProvinsiEntity|UrusanKabupatenEntity $entity
	 * @return void
	 */
	protected function mappingKepmendagriTahun2024(Row $row, string $excel_file, mixed $entity): void
	{
	}

	/**
	 * @param Row $row
	 * @param string $excel_file
	 * @param UrusanProvinsiEntity|UrusanKabupatenEntity $entity
	 * @return void
	 */
	protected function mappingKepmendagriTahun2025(Row $row, string $excel_file, mixed $entity): void
	{
	}

	/**
	 * @param Row $row
	 * @param string $excel_file
	 * @param UrusanProvinsiEntity|UrusanKabupatenEntity $entity
	 * @return void
	 */
	protected function mappingKepmendagriTahun2026(Row $row, string $excel_file, mixed $entity): void
	{
	}

	/**
	 * @param UrusanProvinsiEntity|UrusanKabupatenEntity $entity
	 * @return string
	 */
	protected function arrayIdPermendagriTahun2019(mixed $entity): string
	{
		return $entity->id();
	}

	/**
	 * @param UrusanProvinsiEntity|UrusanKabupatenEntity $entity
	 * @return string
	 */
	protected function arrayIdKepmendagriTahun2020(mixed $entity): string
	{
		return $entity->id();
	}

	/**
	 * @param UrusanProvinsiEntity|UrusanKabupatenEntity $entity
	 * @return string
	 */
	protected function arrayIdKepmendagriTahun2021(mixed $entity): string
	{
		return $entity->id();
	}

	/**
	 * @param UrusanProvinsiEntity|UrusanKabupatenEntity $entity
	 * @return string
	 */
	protected function arrayIdKepmendagriTahun2023(mixed $entity): string
	{
		return $entity->id();
	}

	/**
	 * @param UrusanProvinsiEntity|UrusanKabupatenEntity $entity
	 * @return string
	 */
	protected function arrayIdKepmendagriTahun2024(mixed $entity): string
	{
		return $entity->id();
	}

	/**
	 * @param UrusanProvinsiEntity|UrusanKabupatenEntity $entity
	 * @return string
	 */
	protected function arrayIdKepmendagriTahun2025(mixed $entity): string
	{
		return $entity->id();
	}

	/**
	 * @param UrusanProvinsiEntity|UrusanKabupatenEntity $entity
	 * @return string
	 */
	protected function arrayIdKepmendagriTahun2026(mixed $entity): string
	{
		return $entity->id();
	}

	/**
	 * @param UrusanProvinsiEntity[]|UrusanKabupatenEntity[] $fromEntities
	 * @param UrusanProvinsiEntity[]|UrusanKabupatenEntity[] $intoEntities
	 * @param UrusanProvinsiEntity|UrusanKabupatenEntity $entity
	 * @return array
	 */
	protected function beforeCheckUpdatePermendagriTahun2019(array $fromEntities, array $intoEntities, mixed $entity): array
	{
		return $intoEntities;
	}

	/**
	 * @param UrusanProvinsiEntity[]|UrusanKabupatenEntity[] $fromEntities
	 * @param UrusanProvinsiEntity[]|UrusanKabupatenEntity[] $intoEntities
	 * @param UrusanProvinsiEntity|UrusanKabupatenEntity $entity
	 * @return array
	 */
	protected function beforeCheckUpdateKepmendagriTahun2020(array $fromEntities, array $intoEntities, mixed $entity): array
	{
		return $intoEntities;
	}

	/**
	 * @param UrusanProvinsiEntity[]|UrusanKabupatenEntity[] $fromEntities
	 * @param UrusanProvinsiEntity[]|UrusanKabupatenEntity[] $intoEntities
	 * @param UrusanProvinsiEntity|UrusanKabupatenEntity $entity
	 * @return array
	 */
	protected function beforeCheckUpdateKepmendagriTahun2021(array $fromEntities, array $intoEntities, mixed $entity): array
	{
		return $intoEntities;
	}

	/**
	 * @param UrusanProvinsiEntity[]|UrusanKabupatenEntity[] $fromEntities
	 * @param UrusanProvinsiEntity[]|UrusanKabupatenEntity[] $intoEntities
	 * @param UrusanProvinsiEntity|UrusanKabupatenEntity $entity
	 * @return array
	 */
	protected function beforeCheckUpdateKepmendagriTahun2023(array $fromEntities, array $intoEntities, mixed $entity): array
	{
		return $intoEntities;
	}

	/**
	 * @param UrusanProvinsiEntity[]|UrusanKabupatenEntity[] $fromEntities
	 * @param UrusanProvinsiEntity[]|UrusanKabupatenEntity[] $intoEntities
	 * @param UrusanProvinsiEntity|UrusanKabupatenEntity $entity
	 * @return array
	 */
	protected function beforeCheckUpdateKepmendagriTahun2024(array $fromEntities, array $intoEntities, mixed $entity): array
	{
		return $intoEntities;
	}

	/**
	 * @param UrusanProvinsiEntity[]|UrusanKabupatenEntity[] $fromEntities
	 * @param UrusanProvinsiEntity[]|UrusanKabupatenEntity[] $intoEntities
	 * @param UrusanProvinsiEntity|UrusanKabupatenEntity $entity
	 * @return array
	 */
	protected function beforeCheckUpdateKepmendagriTahun2025(array $fromEntities, array $intoEntities, mixed $entity): array
	{
		return $intoEntities;
	}

	/**
	 * @param UrusanProvinsiEntity[]|UrusanKabupatenEntity[] $fromEntities
	 * @param UrusanProvinsiEntity[]|UrusanKabupatenEntity[] $intoEntities
	 * @param UrusanProvinsiEntity|UrusanKabupatenEntity $entity
	 * @return array
	 */
	protected function beforeCheckUpdateKepmendagriTahun2026(array $fromEntities, array $intoEntities, mixed $entity): array
	{
		return $intoEntities;
	}
}