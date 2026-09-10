<?php

namespace RendyRobbani\Kodefikasi\Pemda\Service;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
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
use RendyRobbani\Kodefikasi\Pemda\Repository\FungsiProvinsiLogRepository;
use RendyRobbani\Kodefikasi\Pemda\Repository\FungsiProvinsiRepository;
use RendyRobbani\Kodefikasi\Pemda\Utility\SpreadsheetUtility;
use RendyRobbani\PHP\Connection\Connection;
use RendyRobbani\PHP\Exception\FileNotFoundException;

class FungsiProvinsiServiceImpl implements FungsiProvinsiService
{
	public function __construct(protected Connection                  $connection,
	                            protected FungsiProvinsiRepository    $repository,
	                            protected FungsiProvinsiLogRepository $logRepository)
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

			/** @var array<string, FungsiProvinsiEntity> $intoEntities */
			$intoEntities = [];

			$updateIds = [];

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

						$intoEntity = new FungsiProvinsiEntity();

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
									$intoEntity->setNomorUrusan($value === null ? null : intval(str_ireplace("X", "0", $value)));
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

						if ($is_perubahan) {
							$levelEntity = sizeof(explode("-", $intoEntity->id()));
							$levelEntity = min(6, $levelEntity);

							for ($level = 1; $level <= $levelEntity; $level++) {
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
									if ($peraturan === Peraturan::KEPMENDAGRI_TAHUN_2020_NOMOR_050_3708 && $kode === "10.04") {
										$tempEntity = new FungsiProvinsiEntity();
										$tempEntity->setNomorFungsi($intoEntity->nomorFungsi());
										$tempEntity->setNomorSubfungsi($intoEntity->nomorSubfungsi());
										$tempEntity->setNama("Pendidikan Nonformal dan Informal");
										$tempEntity->setCreatedAt($peraturan->penetapan());
										$tempEntity->setCreatedBy($peraturan->referensi());
										$tempEntity->setIsUpdated(true);
										$tempEntity->setIsDeleted(false);
										$intoEntities[$tempEntity->id()] = $intoEntity;
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
						}

						$lastId = $intoEntity->id();
						$intoEntities[$lastId] = $intoEntity;
						unset($intoEntity);
					}
				}
			}

			foreach ($intoEntities as $intoEntity) {
				if ($fromEntity = $fromEntities[$intoEntity->id()] ?? null) {
					$intoEntity->setCreatedAt($fromEntity->createdAt());
					$intoEntity->setCreatedBy($fromEntity->createdBy());
					$intoEntity->setIsUpdated(!$intoEntity->isEqual($fromEntity));
					if ($intoEntity->isUpdated()) {
						$intoEntity->setUpdatedAt($peraturan->penetapan());
						$intoEntity->setUpdatedBy($peraturan->referensi());
					}
					unset($fromEntities[$intoEntity->id()]);
				}
				if ($intoEntity->isUpdated()) $updateIds[] = $intoEntity->id();
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

	private function handlePermendagriTahun2019(Row $row, string $excel_file, FungsiProvinsiEntity $entity): void
	{
	}

	private function handleKepmendagriTahun2020(Row $row, string $excel_file, FungsiProvinsiEntity $entity): void
	{
		if (pathinfo($excel_file, PATHINFO_BASENAME) === "D-00354-00369.xlsx") {
			if ($row->getRowIndex() === 397) {
				$entity->setNomorFungsi(8);
				$entity->setNomorSubfungsi(1);
			}
		}
	}

	private function handleKepmendagriTahun2021(Row $row, string $excel_file, FungsiProvinsiEntity $entity): void
	{
	}

	private function handleKepmendagriTahun2023(Row $row, string $excel_file, FungsiProvinsiEntity $entity): void
	{
	}

	private function handleKepmendagriTahun2024(Row $row, string $excel_file, FungsiProvinsiEntity $entity): void
	{
	}

	private function handleKepmendagriTahun2025(Row $row, string $excel_file, FungsiProvinsiEntity $entity): void
	{
	}

	private function handleKepmendagriTahun2026(Row $row, string $excel_file, FungsiProvinsiEntity $entity): void
	{
	}
}