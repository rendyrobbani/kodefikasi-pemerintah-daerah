<?php

namespace RendyRobbani\Kodefikasi\Pemda\Repository;

use RendyRobbani\Kodefikasi\Pemda\Entity\LraEntity;
use RendyRobbani\PHP\Component\Component;
use RendyRobbani\PHP\Persistence\Repository;

#[Component]
#[Repository(LraEntity::class)]
interface LraRepository
{
	/**
	 * @return LraEntity[]
	 */
	function findAll(): array;

	/**
	 * @param LraEntity $entity
	 * @return LraEntity
	 */
	function save(LraEntity $entity): LraEntity;
}