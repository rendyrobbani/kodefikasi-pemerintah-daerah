<?php

namespace RendyRobbani\Kodefikasi\Pemda\Repository;

use RendyRobbani\Kodefikasi\Pemda\Entity\LoLogEntity;
use RendyRobbani\PHP\Component\Component;
use RendyRobbani\PHP\Persistence\Repository;

#[Component]
#[Repository(LoLogEntity::class)]
interface LoLogRepository
{
	/**
	 * @return LoLogEntity[]
	 */
	function findAll(): array;

	/**
	 * @param LoLogEntity $entity
	 * @return LoLogEntity
	 */
	function save(LoLogEntity $entity): LoLogEntity;
}