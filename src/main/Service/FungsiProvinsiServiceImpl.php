<?php

namespace RendyRobbani\Kodefikasi\Pemda\Service;

use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use RendyRobbani\Kodefikasi\Pemda\Entity\FungsiProvinsiEntity;
use RendyRobbani\Kodefikasi\Pemda\Peraturan\Peraturan;
use RendyRobbani\Kodefikasi\Pemda\Repository\FungsiProvinsiLogRepository;
use RendyRobbani\Kodefikasi\Pemda\Repository\FungsiProvinsiRepository;
use RendyRobbani\PHP\Connection\Connection;

class FungsiProvinsiServiceImpl extends AbstractFungsiService implements FungsiProvinsiService
{
	public function __construct(Connection                  $connection,
	                            FungsiProvinsiRepository    $repository,
	                            FungsiProvinsiLogRepository $logRepository)
	{
		$this->connection = $connection;
		$this->repository = $repository;
		$this->logRepository = $logRepository;
	}

	protected function mappingKepmendagriTahun2020(Row $row, string $excel_file, mixed $entity): void
	{
		if (pathinfo($excel_file, PATHINFO_BASENAME) === "D-00354-00369.xlsx") {
			if ($row->getRowIndex() === 397) {
				$entity->setNomorFungsi(8);
				$entity->setNomorSubfungsi(1);
			}
		}
	}

	protected function beforeCheckUpdateKepmendagriTahun2020(array $fromEntities, array $intoEntities, mixed $entity): array
	{
		if (preg_match("/^10\.04(.+)$/", $entity->kode()) && !isset($intoEntities["10-4"])) {
			$tempEntity = new FungsiProvinsiEntity();
			$tempEntity->setNomorFungsi($entity->nomorFungsi());
			$tempEntity->setNomorSubfungsi($entity->nomorSubfungsi());
			$tempEntity->setNama("Pendidikan Nonformal dan Informal");
			$tempEntity->setCreatedAt(Peraturan::KEPMENDAGRI_TAHUN_2020_NOMOR_050_3708->penetapan());
			$tempEntity->setCreatedBy(Peraturan::KEPMENDAGRI_TAHUN_2020_NOMOR_050_3708->referensi());
			$tempEntity->setIsUpdated(true);
			$tempEntity->setIsDeleted(false);
			$intoEntities[$tempEntity->id()] = $tempEntity;
		}
		return $intoEntities;
	}
}