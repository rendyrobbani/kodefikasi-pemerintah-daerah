<?php

namespace RendyRobbani\Kodefikasi\Pemda\Service;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use RendyRobbani\Kodefikasi\Pemda\Entity\FungsiEntity;
use RendyRobbani\Kodefikasi\Pemda\Exception\FungsiExistsException;
use RendyRobbani\Kodefikasi\Pemda\Exception\FungsiNotFoundException;
use RendyRobbani\Kodefikasi\Pemda\Exception\SubfungsiExistsException;
use RendyRobbani\Kodefikasi\Pemda\Exception\SubfungsiNotFoundException;
use RendyRobbani\Kodefikasi\Pemda\Peraturan\Peraturan;
use RendyRobbani\Kodefikasi\Pemda\Repository\FungsiLogRepository;
use RendyRobbani\Kodefikasi\Pemda\Repository\FungsiRepository;
use RendyRobbani\Kodefikasi\Pemda\Utility\SpreadsheetUtility;
use RendyRobbani\PHP\Connection\Connection;
use RendyRobbani\PHP\Exception\FileNotFoundException;

class FungsiServiceImpl implements FungsiService
{
	public function __construct(protected Connection          $connection,
	                            protected FungsiRepository    $repository,
	                            protected FungsiLogRepository $logRepository)
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

			/** @var array<string, FungsiEntity> $intoEntities */
			$intoEntities = [];

			$updateIds = [];

			foreach ($excel_files as $excel_file) {
				if (!file_exists($excel_file)) throw new FileNotFoundException($excel_file);

				$spreadsheet = new Xlsx()->load($excel_file);
				foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
					foreach ($worksheet->getRowIterator() as $row) {
						echo "Import from : " . pathinfo($excel_file, PATHINFO_BASENAME) . " | row : " . $row->getRowIndex() . PHP_EOL;

						$values = SpreadsheetUtility::getCellValuesAsStringFromRow($worksheet, $row->getRowIndex(), 1, 3);
						if ($values[0] !== null && preg_match("/^[a-wy-z].+/", strtolower($values[0]))) continue;

						$notNull = SpreadsheetUtility::countNotNullColumns($values);
						if ($notNull === 0) continue;

						$intoEntity = new FungsiEntity();

						for ($colNum = 1; $colNum <= sizeof($values); $colNum++) {
							$value = $values[$colNum - 1];
							if ($value !== null && $colNum < 3) $value = strtoupper($value);

							switch ($colNum) {
								case 1:
									$intoEntity->setNomorFungsi($value === null ? null : intval($value));
									break;
								case 2:
									$intoEntity->setNomorSubfungsi($value === null ? null : intval($value));
									break;
								case 3:
									$intoEntity->setNama($value);
									break;
							}
						}

						$intoEntity->setCreatedAt($peraturan->penetapan());
						$intoEntity->setCreatedBy($peraturan->referensi());
						$intoEntity->setIsDeleted(false);

						if ($is_perubahan) {
							$levelEntity = sizeof(explode("-", $intoEntity->id()));

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

								$ID = implode("-", $ID);
								$kode = implode(".", $kode);

								if ($level < $levelEntity && !isset($intoEntities[$ID])) {
									switch ($level) {
										case 1:
											throw new FungsiNotFoundException($kode);
										case 2:
											throw new SubfungsiNotFoundException($kode);
									}
								}

								if ($level === $levelEntity && isset($intoEntities[$ID])) {
									switch ($level) {
										case 1:
											throw new FungsiExistsException($kode);
										case 2:
											throw new SubfungsiExistsException($kode);
									}
								}
							}
						}

						if ($fromEntity = $fromEntities[$intoEntity->id()] ?? null) {
							if (!$intoEntity->isEqual($fromEntity)) {
								$intoEntity->setCreatedAt($fromEntity->createdAt());
								$intoEntity->setCreatedBy($fromEntity->createdBy());
								$intoEntity->setUpdatedAt($peraturan->penetapan());
								$intoEntity->setUpdatedBy($peraturan->referensi());

								$updateIds[] = $intoEntity->id();
							}

							unset($fromEntities[$intoEntity->id()]);
						} else {
							$updateIds[] = $intoEntity->id();
						}

						$intoEntities[$intoEntity->id()] = $intoEntity;

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
}