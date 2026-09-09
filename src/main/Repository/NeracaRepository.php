<?php

namespace RendyRobbani\Kodefikasi\Pemda\Repository;

use RendyRobbani\Kodefikasi\Pemda\Entity\NeracaEntity;
use RendyRobbani\PHP\Component\Component;
use RendyRobbani\PHP\Persistence\Repository;

#[Component]
#[Repository(NeracaEntity::class)]
interface NeracaRepository
{
	/**
	 * @return NeracaEntity[]
	 */
	function findAll(): array;

	/**
	 * @param NeracaEntity $entity
	 * @return NeracaEntity
	 */
	function save(NeracaEntity $entity): NeracaEntity;
}