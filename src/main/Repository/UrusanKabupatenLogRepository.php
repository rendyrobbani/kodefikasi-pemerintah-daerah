<?php

namespace RendyRobbani\Kodefikasi\Pemda\Repository;

use RendyRobbani\Kodefikasi\Pemda\Entity\UrusanKabupatenLogEntity;
use RendyRobbani\PHP\Component\Component;
use RendyRobbani\PHP\Persistence\Repository;

#[Component]
#[Repository(UrusanKabupatenLogEntity::class)]
interface UrusanKabupatenLogRepository
{
	/**
	 * @return UrusanKabupatenLogEntity[]
	 */
	function findAll(): array;

	/**
	 * @param UrusanKabupatenLogEntity $entity
	 * @return UrusanKabupatenLogEntity
	 */
	function save(UrusanKabupatenLogEntity $entity): UrusanKabupatenLogEntity;
}