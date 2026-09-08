<?php

namespace RendyRobbani\Kodefikasi\Pemda\Repository;

use RendyRobbani\Kodefikasi\Pemda\Entity\UrusanProvinsiEntity;
use RendyRobbani\PHP\Component\Component;
use RendyRobbani\PHP\Persistence\Repository;

#[Component]
#[Repository(UrusanProvinsiEntity::class)]
interface UrusanProvinsiRepository
{
	/**
	 * @return UrusanProvinsiEntity[]
	 */
	function findAll(): array;

	/**
	 * @param UrusanProvinsiEntity $entity
	 * @return UrusanProvinsiEntity
	 */
	function save(UrusanProvinsiEntity $entity): UrusanProvinsiEntity;
}