<?php
declare(strict_types=1);

namespace SGFP\Application\Ports;

use SGFP\Application\Backup\RestorationImportPlan;

/** Atomic, user-scoped persistence boundary for an already validated import. */
interface RestorationPersistence
{
    public function replace(RestorationImportPlan $plan): void;

    /** @return array{counts:array<string,int>,references:int,owner:int,theme:string,invariants:bool} */
    public function verify(RestorationImportPlan $plan): array;
}
