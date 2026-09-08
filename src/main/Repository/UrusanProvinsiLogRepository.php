<?php

namespace RendyRobbani\Kodefikasi\Pemda\Repository;

use RendyRobbani\Kodefikasi\Pemda\Entity\UrusanProvinsiLogEntity;
use RendyRobbani\PHP\Component\Component;
use RendyRobbani\PHP\Persistence\Repository;

#[Component]
#[Repository(UrusanProvinsiLogEntity::class)]
interface UrusanProvinsiLogRepository
{
	/**
	 * @return UrusanProvinsiLogEntity[]
	 */
	function findAll(): array;

	/**
	 * @param UrusanProvinsiLogEntity $entity
	 * @return UrusanProvinsiLogEntity
	 */
	function save(UrusanProvinsiLogEntity $entity): UrusanProvinsiLogEntity;
}