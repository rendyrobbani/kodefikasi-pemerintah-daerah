<?php

namespace RendyRobbani\Kodefikasi\Pemda\Service;

use RendyRobbani\Kodefikasi\Pemda\Repository\FungsiLogRepository;
use RendyRobbani\Kodefikasi\Pemda\Repository\FungsiRepository;
use RendyRobbani\PHP\Connection\Connection;

class FungsiServiceImpl extends AbstractFungsiService implements FungsiService
{
	public function __construct(Connection          $connection,
	                            FungsiRepository    $repository,
	                            FungsiLogRepository $logRepository)
	{
		$this->connection = $connection;
		$this->repository = $repository;
		$this->logRepository = $logRepository;
	}
}