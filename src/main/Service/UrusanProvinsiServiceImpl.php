<?php

namespace RendyRobbani\Kodefikasi\Pemda\Service;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use RendyRobbani\Kodefikasi\Pemda\Comparator\StringComparator;
use RendyRobbani\Kodefikasi\Pemda\Entity\UrusanProvinsiEntity;
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
use RendyRobbani\Kodefikasi\Pemda\Repository\UrusanProvinsiLogRepository;
use RendyRobbani\Kodefikasi\Pemda\Repository\UrusanProvinsiRepository;
use RendyRobbani\Kodefikasi\Pemda\Utility\SpreadsheetUtility;
use RendyRobbani\PHP\Connection\Connection;
use RendyRobbani\PHP\Exception\FileNotFoundException;

class UrusanProvinsiServiceImpl implements UrusanProvinsiService
{
	public function __construct(protected Connection                  $connection,
	                            protected UrusanProvinsiRepository    $repository,
	                            protected UrusanProvinsiLogRepository $logRepository)
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

			/** @var array<string, UrusanProvinsiEntity> $intoEntities */
			$intoEntities = [];

			$updateIds = [];
			$lastId = null;

			foreach ($excel_files as $excel_file) {
				if (!file_exists($excel_file)) throw new FileNotFoundException($excel_file);

				$spreadsheet = new Xlsx()->load($excel_file);
				foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
					foreach ($worksheet->getRowIterator() as $row) {
						echo "Import from : " . pathinfo($excel_file, PATHINFO_BASENAME) . " | row : " . $row->getRowIndex() . PHP_EOL;

						$values = SpreadsheetUtility::getCellValuesAsStringFromRow($worksheet, $row->getRowIndex(), 1, 10);
						if ($values[0] !== null && preg_match("/^[a-wy-z].+/", strtolower($values[0]))) continue;

						$notNull = SpreadsheetUtility::countNotNullColumns($values);
						if ($notNull === 0) continue;

						$notNull = SpreadsheetUtility::countNotNullColumns(array_slice($values, 0, 5));
						if ($notNull === 0) {
							$value = trim($values[5]);
							if ($lastId !== null) {
								$intoEntity = $intoEntities[$lastId];
								if (preg_match("/^(tidak ada kewenangan|\(kegiatan|\(sub kegiatan)(.+)?$/", strtolower($value))) {
									$intoEntity->setKeterangan(ucfirst(strtolower($value)));
								} else {
									$intoEntity->setNama(SpreadsheetUtility::cleanValue(implode(" ", [$intoEntity->nama(), $value])));
								}
								unset($intoEntities[$intoEntity->id()]);
							}
						} else {
							$intoEntity = new UrusanProvinsiEntity();

							for ($colNum = 1; $colNum <= sizeof($values); $colNum++) {
								$value = $values[$colNum - 1];
								if ($value !== null && $colNum < 3) $value = strtoupper($value);

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
										$intoEntity->setNama($value);
										break;
									case 7:
										$intoEntity->setKeterangan($value);
										break;
									case 8:
										$intoEntity->setKinerja($value);
										break;
									case 9:
										$intoEntity->setIndikator($value);
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
								if (preg_match("/^(\d+).(\d+).(\d+).([3-9]).(\d+).(\d+)$/", $intoEntity->id(), $matches)) {
									$kegiatanListID = array_values(array_slice($matches, 1, 5));
									$kegiatanID = implode("-", $kegiatanListID);
									if (!isset($intoEntities[$kegiatanID]) && !isset($fromEntities[$kegiatanID])) {
										$kegiatanListID[3] = 1;
										$kegiatanID = implode("-", $kegiatanListID);
										if (isset($intoEntities[$kegiatanID])) {
											$tempEntity = $intoEntities[$kegiatanID];
											$tempEntity->setNomorKegiatan1($intoEntity->nomorKegiatan1());
											$intoEntities[$tempEntity->id()] = $tempEntity;
										}
									}
								}

								switch ($intoEntity->kode($peraturan)) {
									case "X.XX.01": // PROGRAM PENUNJANG URUSAN PEMERINTAHAN DAERAH PROVINSI
									case "1.05.02": // PROGRAM PENINGKATAN KETENTERAMAN DAN KETERTIBAN UMUM
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
							$levelEntity = match ($levelEntity) {
								5, 6 => $levelEntity - 1,
								default => $levelEntity,
							};

							for ($level = 1; $level <= $levelEntity; $level++) {
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
						}

						$lastId = $intoEntity->id();
						$intoEntities[$lastId] = $intoEntity;
						unset($intoEntity);
					}
				}
			}

			foreach ($intoEntities as $ID => $intoEntity) {
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

	private function handlePermendagriTahun2019(Row $row, string $excel_file, UrusanProvinsiEntity $entity): void
	{
	}

	private function handleKepmendagriTahun2020(Row $row, string $excel_file, UrusanProvinsiEntity $entity): void
	{
		if (pathinfo($excel_file, PATHINFO_BASENAME) === "B-00030-00207.xlsx") {
			switch ($row->getRowIndex()) {
				case 129:
				case 131:
				case 132:
				case 133:
				case 134:
				case 136:
				case 137:
				case 138:
				case 139:
					$entity->setNomorProgram(1);
					break;
				case 394:
					$entity->setNomorUrusan(1);
					break;
				case 948:
					$entity->setNomorSubkegiatan(4);
					break;
				case 3095:
					$entity->setNomorKegiatan2(3);
					break;
			}
		}
	}

	private function handleKepmendagriTahun2021(Row $row, string $excel_file, UrusanProvinsiEntity $entity): void
	{
		if (pathinfo($excel_file, PATHINFO_BASENAME) === "B-00037-00336.xlsx") {
			switch ($row->getRowIndex()) {
				case 416:
					$entity->setNomorUrusan(1);
					break;
				case 2586:
					$entity->setNomorProgram(4);
					break;
				case 2686:
					$entity->setNomorKegiatan1(1);
					break;
				case 2921:
					$entity->setNomorUrusan(3);
					$entity->setNomorBidang(28);
					break;
				case 3395:
				case 3523:
					$entity->setNomorKegiatan2(3);
					break;
			}
		}
	}

	private function handleKepmendagriTahun2023(Row $row, string $excel_file, UrusanProvinsiEntity $entity): void
	{
	}

	private function handleKepmendagriTahun2024(Row $row, string $excel_file, UrusanProvinsiEntity $entity): void
	{
	}

	private function handleKepmendagriTahun2025(Row $row, string $excel_file, UrusanProvinsiEntity $entity): void
	{
	}

	private function handleKepmendagriTahun2026(Row $row, string $excel_file, UrusanProvinsiEntity $entity): void
	{
	}
}