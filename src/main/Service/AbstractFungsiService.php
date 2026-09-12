<?php

namespace RendyRobbani\Kodefikasi\Pemda\Service;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use RendyRobbani\Kodefikasi\Pemda\Entity\FungsiEntity;
use RendyRobbani\Kodefikasi\Pemda\Entity\FungsiKabupatenEntity;
use RendyRobbani\Kodefikasi\Pemda\Entity\FungsiProvinsiEntity;
use RendyRobbani\Kodefikasi\Pemda\Exception\BidangExistsException;
use RendyRobbani\Kodefikasi\Pemda\Exception\BidangNotFoundException;
use RendyRobbani\Kodefikasi\Pemda\Exception\FungsiExistsException;
use RendyRobbani\Kodefikasi\Pemda\Exception\FungsiNotFoundException;
use RendyRobbani\Kodefikasi\Pemda\Exception\KegiatanExistsException;
use RendyRobbani\Kodefikasi\Pemda\Exception\KegiatanNotFoundException;
use RendyRobbani\Kodefikasi\Pemda\Exception\ProgramExistsException;
use RendyRobbani\Kodefikasi\Pemda\Exception\ProgramNotFoundException;
use RendyRobbani\Kodefikasi\Pemda\Exception\SubfungsiExistsException;
use RendyRobbani\Kodefikasi\Pemda\Exception\SubfungsiNotFoundException;
use RendyRobbani\Kodefikasi\Pemda\Exception\UrusanExistsException;
use RendyRobbani\Kodefikasi\Pemda\Exception\UrusanNotFoundException;
use RendyRobbani\Kodefikasi\Pemda\Peraturan\Peraturan;
use RendyRobbani\Kodefikasi\Pemda\Repository\FungsiKabupatenLogRepository;
use RendyRobbani\Kodefikasi\Pemda\Repository\FungsiKabupatenRepository;
use RendyRobbani\Kodefikasi\Pemda\Repository\FungsiLogRepository;
use RendyRobbani\Kodefikasi\Pemda\Repository\FungsiProvinsiLogRepository;
use RendyRobbani\Kodefikasi\Pemda\Repository\FungsiProvinsiRepository;
use RendyRobbani\Kodefikasi\Pemda\Repository\FungsiRepository;
use RendyRobbani\Kodefikasi\Pemda\Utility\SpreadsheetUtility;
use RendyRobbani\PHP\Connection\Connection;
use RendyRobbani\PHP\Exception\FileNotFoundException;

abstract class AbstractFungsiService implements DefaultService
{
	protected Connection $connection;

	protected FungsiRepository|FungsiProvinsiRepository|FungsiKabupatenRepository $repository;

	protected FungsiLogRepository|FungsiProvinsiLogRepository|FungsiKabupatenLogRepository $logRepository;

	function updateFromExcelFiles(Peraturan $peraturan, array $excel_files, bool $delete_if_not_exists): void
	{
		if (sizeof($excel_files) === 0) return;
		try {
			$this->connection->beginTransaction();

			$fromEntities = $this->repository->findAll();
			$fromEntities = array_combine(array_map(fn($fromEntity) => $fromEntity->id(), $fromEntities), $fromEntities);

			/** @var array<string, FungsiEntity|FungsiProvinsiEntity|FungsiKabupatenEntity> $intoEntities */
			$intoEntities = [];

			foreach ($excel_files as $excel_file) {
				if (!file_exists($excel_file)) throw new FileNotFoundException($excel_file);

				$spreadsheet = new Xlsx()->load($excel_file);
				foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
					foreach ($worksheet->getRowIterator() as $row) {
						echo "insert from : " . pathinfo($excel_file, PATHINFO_BASENAME) . " | row : " . $row->getRowIndex() . PHP_EOL;

						$values = SpreadsheetUtility::getCellValuesAsStringFromRow($worksheet, $row->getRowIndex(), 1, $this->repository instanceof FungsiRepository ? 3 : 7);
						if ($values[0] !== null && preg_match("/^[a-wy-z].+/", strtolower($values[0]))) continue;

						$notNull = SpreadsheetUtility::countNotNullColumnsAntNotBlank($values);
						if ($notNull === 0) continue;

						if ($this->repository instanceof FungsiRepository) $intoEntity = new FungsiEntity();
						if ($this->repository instanceof FungsiProvinsiRepository) $intoEntity = new FungsiProvinsiEntity();
						if ($this->repository instanceof FungsiKabupatenRepository) $intoEntity = new FungsiKabupatenEntity();

						for ($colNum = 1; $colNum <= sizeof($values); $colNum++) {
							$value = $values[$colNum - 1];

							switch ($colNum) {
								case 1:
									$intoEntity->setNomorFungsi($value === null ? null : intval($value));
									break;
								case 2:
									$intoEntity->setNomorSubfungsi($value === null ? null : intval($value));
									break;
								case 3:
									if ($this->repository instanceof FungsiRepository) $intoEntity->setNama($value);
									else $intoEntity->setNomorUrusan($value === null ? null : intval(str_ireplace("X", "0", $value)));
									break;
								case 4:
									$intoEntity->setNomorBidang($value === null ? null : intval(str_ireplace("X", "0", $value)));
									break;
								case 5:
									$intoEntity->setNomorProgram($value === null ? null : intval($value));
									break;
								case 6:
									if (is_float($value)) $value = round($value, 2);
									if ($value !== null && preg_match("/^(.)\.(.{2})$/", sprintf("%.2f", $value), $matches)) {
										$intoEntity->setNomorKegiatan1(intval($matches[1]));
										$intoEntity->setNomorKegiatan2(intval($matches[2]));
									}
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
						$levelEntity = min(6, $levelEntity);

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
								$ID[] = $intoEntity->nomorFungsi();
								$kode[] = $intoEntity->kodeFungsi();
							}
							if ($level >= 2) {
								$ID[] = $intoEntity->nomorSubfungsi();
								$kode[] = $intoEntity->kodeSubfungsi();
							}
							if ($level >= 3) {
								$ID[] = $intoEntity->nomorUrusan();
								$kode[] = $intoEntity->kodeUrusan();
							}
							if ($level >= 4) {
								$ID[] = $intoEntity->nomorBidang();
								$kode[] = $intoEntity->kodeBidang();
							}
							if ($level >= 5) {
								$ID[] = $intoEntity->nomorProgram();
								$kode[] = $intoEntity->kodeProgram();
							}
							if ($level >= 6) {
								$ID[] = $intoEntity->nomorKegiatan1();
								$ID[] = $intoEntity->nomorKegiatan2();
								$kode[] = $intoEntity->kodeKegiatan();
							}

							$ID = implode("-", $ID);
							$kode = implode(".", $kode);

							if (preg_match("/^01\.01\.X(\.XX)?$/", $kode)) break;

							if ($level < $levelEntity && !isset($intoEntities[$ID])) {
								if (isset($fromEntities[$ID])) {
									$intoEntities[$ID] = $fromEntities[$ID];
									unset($fromEntities[$ID]);
									continue;
								}

								$regex = "/^\d+-\d+-(\d+)$/";
								if (preg_match($regex, $ID, $matches)) {
									$tempEntity = array_filter(array_keys($intoEntities), fn($key) => preg_match($regex, $key));
									$tempEntity = array_first($tempEntity);
									if ($tempEntity !== null) {
										$tempEntity = $intoEntities[$tempEntity];
										$tempEntity->setNomorFungsi($intoEntity->nomorFungsi());
										$tempEntity->setNomorSubfungsi($intoEntity->nomorSubfungsi());
										$tempEntity->setNama($intoEntity->nama());
										$tempEntity->setCreatedAt($intoEntity->createdAt());
										$tempEntity->setCreatedBy($intoEntity->createdBy());
										$tempEntity->setIsUpdated($intoEntity->isUpdated());
										$tempEntity->setUpdatedAt($intoEntity->updatedAt());
										$tempEntity->setUpdatedBy($intoEntity->updatedBy());
										$tempEntity->setIsDeleted($intoEntity->isDeleted());
										$tempEntity->setDeletedAt($intoEntity->deletedAt());
										$tempEntity->setDeletedBy($intoEntity->deletedBy());
										$intoEntities[$tempEntity->id()] = $intoEntity;
										continue;
									}
								}

								switch ($level) {
									case 1:
										throw new FungsiNotFoundException($kode);
									case 2:
										throw new SubfungsiNotFoundException($kode);
									case 3:
										throw new UrusanNotFoundException($kode);
									case 4:
										throw new BidangNotFoundException($kode);
									case 5:
										throw new ProgramNotFoundException($kode);
									case 6:
										throw new KegiatanNotFoundException($kode);
								}
							}

							if ($level === $levelEntity && isset($intoEntities[$ID])) {
								switch ($level) {
									case 1:
										throw new FungsiExistsException($kode);
									case 2:
										throw new SubfungsiExistsException($kode);
									case 3:
										throw new UrusanExistsException($kode);
									case 4:
										throw new BidangExistsException($kode);
									case 5:
										throw new ProgramExistsException($kode);
									case 6:
										throw new KegiatanExistsException($kode);
								}
							}
						}

						$intoEntities[$intoEntity->id()] = $intoEntity;
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

				$len_kode = $entity->kode() === null ? 0 : strlen($entity->kode());
				$len_kode = str_pad($len_kode, 4, " ", STR_PAD_LEFT);

				$len_nama = $entity->nama() === null ? 0 : strlen($entity->nama());
				$len_nama = str_pad($len_nama, 4, " ", STR_PAD_LEFT);

				echo PHP_EOL;
				echo "id   : " . $len_id . " : " . $entity->id() . PHP_EOL;
				echo "kode : " . $len_kode . " : " . $entity->kode() . PHP_EOL;
				echo "nama : " . $len_nama . " : " . $entity->nama() . PHP_EOL;
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

			/** @var (FungsiEntity|FungsiProvinsiEntity|FungsiKabupatenEntity)[] $intoEntities */
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

						if (isset($intoEntities[$intoEntity->id()])) {
							switch (sizeof($ID)) {
								case 1:
									throw new FungsiNotFoundException($intoEntity->kode());
								case 2:
									throw new SubfungsiNotFoundException($intoEntity->kode());
								case 3:
									throw new UrusanNotFoundException($intoEntity->kode());
								case 4:
									throw new BidangNotFoundException($intoEntity->kode());
								case 5:
									throw new ProgramNotFoundException($intoEntity->kode());
								case 6:
								case 7:
									throw new KegiatanNotFoundException($intoEntity->kode());
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
	 * @param FungsiEntity|FungsiProvinsiEntity|FungsiKabupatenEntity $entity
	 * @return void
	 */
	protected function mappingPermendagriTahun2019(Row $row, string $excel_file, mixed $entity): void
	{
	}

	/**
	 * @param Row $row
	 * @param string $excel_file
	 * @param FungsiEntity|FungsiProvinsiEntity|FungsiKabupatenEntity $entity
	 * @return void
	 */
	protected function mappingKepmendagriTahun2020(Row $row, string $excel_file, mixed $entity): void
	{
	}

	/**
	 * @param Row $row
	 * @param string $excel_file
	 * @param FungsiEntity|FungsiProvinsiEntity|FungsiKabupatenEntity $entity
	 * @return void
	 */
	protected function mappingKepmendagriTahun2021(Row $row, string $excel_file, mixed $entity): void
	{
	}

	/**
	 * @param Row $row
	 * @param string $excel_file
	 * @param FungsiEntity|FungsiProvinsiEntity|FungsiKabupatenEntity $entity
	 * @return void
	 */
	protected function mappingKepmendagriTahun2023(Row $row, string $excel_file, mixed $entity): void
	{
	}

	/**
	 * @param Row $row
	 * @param string $excel_file
	 * @param FungsiEntity|FungsiProvinsiEntity|FungsiKabupatenEntity $entity
	 * @return void
	 */
	protected function mappingKepmendagriTahun2024(Row $row, string $excel_file, mixed $entity): void
	{
	}

	/**
	 * @param Row $row
	 * @param string $excel_file
	 * @param FungsiEntity|FungsiProvinsiEntity|FungsiKabupatenEntity $entity
	 * @return void
	 */
	protected function mappingKepmendagriTahun2025(Row $row, string $excel_file, mixed $entity): void
	{
	}

	/**
	 * @param Row $row
	 * @param string $excel_file
	 * @param FungsiEntity|FungsiProvinsiEntity|FungsiKabupatenEntity $entity
	 * @return void
	 */
	protected function mappingKepmendagriTahun2026(Row $row, string $excel_file, mixed $entity): void
	{
	}

	/**
	 * @param FungsiEntity|FungsiProvinsiEntity|FungsiKabupatenEntity $entity
	 * @return string
	 */
	protected function arrayIdPermendagriTahun2019(mixed $entity): string
	{
		return $entity->id();
	}

	/**
	 * @param FungsiEntity|FungsiProvinsiEntity|FungsiKabupatenEntity $entity
	 * @return string
	 */
	protected function arrayIdKepmendagriTahun2020(mixed $entity): string
	{
		return $entity->id();
	}

	/**
	 * @param FungsiEntity|FungsiProvinsiEntity|FungsiKabupatenEntity $entity
	 * @return string
	 */
	protected function arrayIdKepmendagriTahun2021(mixed $entity): string
	{
		return $entity->id();
	}

	/**
	 * @param FungsiEntity|FungsiProvinsiEntity|FungsiKabupatenEntity $entity
	 * @return string
	 */
	protected function arrayIdKepmendagriTahun2023(mixed $entity): string
	{
		return $entity->id();
	}

	/**
	 * @param FungsiEntity|FungsiProvinsiEntity|FungsiKabupatenEntity $entity
	 * @return string
	 */
	protected function arrayIdKepmendagriTahun2024(mixed $entity): string
	{
		return $entity->id();
	}

	/**
	 * @param FungsiEntity|FungsiProvinsiEntity|FungsiKabupatenEntity $entity
	 * @return string
	 */
	protected function arrayIdKepmendagriTahun2025(mixed $entity): string
	{
		return $entity->id();
	}

	/**
	 * @param FungsiEntity|FungsiProvinsiEntity|FungsiKabupatenEntity $entity
	 * @return string
	 */
	protected function arrayIdKepmendagriTahun2026(mixed $entity): string
	{
		return $entity->id();
	}

	/**
	 * @param FungsiEntity[]|FungsiProvinsiEntity[]|FungsiKabupatenEntity[] $fromEntities
	 * @param FungsiEntity[]|FungsiProvinsiEntity[]|FungsiKabupatenEntity[] $intoEntities
	 * @param FungsiEntity|FungsiProvinsiEntity|FungsiKabupatenEntity $entity
	 * @return array
	 */
	protected function beforeCheckUpdatePermendagriTahun2019(array $fromEntities, array $intoEntities, mixed $entity): array
	{
		return $intoEntities;
	}

	/**
	 * @param FungsiEntity[]|FungsiProvinsiEntity[]|FungsiKabupatenEntity[] $fromEntities
	 * @param FungsiEntity[]|FungsiProvinsiEntity[]|FungsiKabupatenEntity[] $intoEntities
	 * @param FungsiEntity|FungsiProvinsiEntity|FungsiKabupatenEntity $entity
	 * @return array
	 */
	protected function beforeCheckUpdateKepmendagriTahun2020(array $fromEntities, array $intoEntities, mixed $entity): array
	{
		return $intoEntities;
	}

	/**
	 * @param FungsiEntity[]|FungsiProvinsiEntity[]|FungsiKabupatenEntity[] $fromEntities
	 * @param FungsiEntity[]|FungsiProvinsiEntity[]|FungsiKabupatenEntity[] $intoEntities
	 * @param FungsiEntity|FungsiProvinsiEntity|FungsiKabupatenEntity $entity
	 * @return array
	 */
	protected function beforeCheckUpdateKepmendagriTahun2021(array $fromEntities, array $intoEntities, mixed $entity): array
	{
		return $intoEntities;
	}

	/**
	 * @param FungsiEntity[]|FungsiProvinsiEntity[]|FungsiKabupatenEntity[] $fromEntities
	 * @param FungsiEntity[]|FungsiProvinsiEntity[]|FungsiKabupatenEntity[] $intoEntities
	 * @param FungsiEntity|FungsiProvinsiEntity|FungsiKabupatenEntity $entity
	 * @return array
	 */
	protected function beforeCheckUpdateKepmendagriTahun2023(array $fromEntities, array $intoEntities, mixed $entity): array
	{
		return $intoEntities;
	}

	/**
	 * @param FungsiEntity[]|FungsiProvinsiEntity[]|FungsiKabupatenEntity[] $fromEntities
	 * @param FungsiEntity[]|FungsiProvinsiEntity[]|FungsiKabupatenEntity[] $intoEntities
	 * @param FungsiEntity|FungsiProvinsiEntity|FungsiKabupatenEntity $entity
	 * @return array
	 */
	protected function beforeCheckUpdateKepmendagriTahun2024(array $fromEntities, array $intoEntities, mixed $entity): array
	{
		return $intoEntities;
	}

	/**
	 * @param FungsiEntity[]|FungsiProvinsiEntity[]|FungsiKabupatenEntity[] $fromEntities
	 * @param FungsiEntity[]|FungsiProvinsiEntity[]|FungsiKabupatenEntity[] $intoEntities
	 * @param FungsiEntity|FungsiProvinsiEntity|FungsiKabupatenEntity $entity
	 * @return array
	 */
	protected function beforeCheckUpdateKepmendagriTahun2025(array $fromEntities, array $intoEntities, mixed $entity): array
	{
		return $intoEntities;
	}

	/**
	 * @param FungsiEntity[]|FungsiProvinsiEntity[]|FungsiKabupatenEntity[] $fromEntities
	 * @param FungsiEntity[]|FungsiProvinsiEntity[]|FungsiKabupatenEntity[] $intoEntities
	 * @param FungsiEntity|FungsiProvinsiEntity|FungsiKabupatenEntity $entity
	 * @return array
	 */
	protected function beforeCheckUpdateKepmendagriTahun2026(array $fromEntities, array $intoEntities, mixed $entity): array
	{
		return $intoEntities;
	}
}