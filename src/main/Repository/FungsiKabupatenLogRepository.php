<?php

namespace RendyRobbani\Kodefikasi\Pemda\Repository;

use RendyRobbani\Kodefikasi\Pemda\Entity\FungsiKabupatenLogEntity;
use RendyRobbani\PHP\Component\Component;
use RendyRobbani\PHP\Persistence\Repository;

#[Component]
#[Repository(FungsiKabupatenLogEntity::class)]
interface FungsiKabupatenLogRepository
{
	/**
	 * @return FungsiKabupatenLogEntity[]
	 */
	function findAll(): array;

	/**
	 * @param FungsiKabupatenLogEntity $entity
	 * @return FungsiKabupatenLogEntity
	 */
	function save(FungsiKabupatenLogEntity $entity): FungsiKabupatenLogEntity;
}