<?php

namespace RendyRobbani\Kodefikasi\Pemda\Service;

use RendyRobbani\Kodefikasi\Pemda\Entity\SumberEntity;
use RendyRobbani\Kodefikasi\Pemda\Peraturan\Peraturan;
use RendyRobbani\Kodefikasi\Pemda\Repository\SumberLogRepository;
use RendyRobbani\Kodefikasi\Pemda\Repository\SumberRepository;
use RendyRobbani\PHP\Connection\Connection;

class SumberServiceImpl extends AbstractRekeningService implements SumberService
{
	public function __construct(Connection          $connection,
	                            SumberRepository    $repository,
	                            SumberLogRepository $logRepository)
	{
		$this->connection = $connection;
		$this->repository = $repository;
		$this->logRepository = $logRepository;
		$this->type = self::SUMBER;
	}

	/**
	 * @param array<string, SumberEntity> $fromEntities
	 * @param array<string, SumberEntity> $intoEntities
	 * @param SumberEntity $entity
	 * @return array
	 */
	protected function beforeCheckKepmendagriTahun2021(array $fromEntities, array $intoEntities, mixed $entity): array
	{
		switch ($entity->kode(Peraturan::KEPMENDAGRI_TAHUN_2021_NOMOR_050_5889)) {
			case "1.2.2.02": // Bantuan Keuangan
				$listFromID = explode("-", $entity->id());
				for ($i = 1; $i < sizeof($listFromID); $i++) {
					$fromID = implode("-", array_slice($listFromID, 0, $i));
					if (!isset($intoEntities[$fromID]) && isset($fromEntities[$fromID])) {
						$intoEntities[$fromID] = $fromEntities[$fromID];
						unset($fromEntities[$fromID]);
					}
				}
				break;
		}
		return $intoEntities;
	}
}