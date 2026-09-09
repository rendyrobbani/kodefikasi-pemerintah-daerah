<?php

namespace RendyRobbani\Kodefikasi\Pemda\Repository;

use RendyRobbani\Kodefikasi\Pemda\Entity\LoEntity;
use RendyRobbani\PHP\Component\Component;
use RendyRobbani\PHP\Persistence\Repository;

#[Component]
#[Repository(LoEntity::class)]
interface LoRepository
{
	/**
	 * @return LoEntity[]
	 */
	function findAll(): array;

	/**
	 * @param LoEntity $entity
	 * @return LoEntity
	 */
	function save(LoEntity $entity): LoEntity;
}