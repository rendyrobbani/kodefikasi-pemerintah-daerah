<?php

namespace RendyRobbani\Kodefikasi\Pemda\Repository;

use RendyRobbani\Kodefikasi\Pemda\Entity\NeracaLogEntity;
use RendyRobbani\PHP\Component\Component;
use RendyRobbani\PHP\Persistence\Repository;

#[Component]
#[Repository(NeracaLogEntity::class)]
interface NeracaLogRepository
{
	/**
	 * @return NeracaLogEntity[]
	 */
	function findAll(): array;

	/**
	 * @param NeracaLogEntity $entity
	 * @return NeracaLogEntity
	 */
	function save(NeracaLogEntity $entity): NeracaLogEntity;
}