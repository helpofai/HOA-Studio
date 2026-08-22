<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| This file is part of the HelpOfAi Professional Software Suite.
| Unauthorized copying, modification, redistribution, reverse engineering,
| decompilation, or commercial use of this source code, in whole or in part,
| is strictly prohibited without prior written permission from the copyright owner.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
| This source code contains proprietary and confidential information.
| Any unauthorized access or distribution may violate applicable copyright laws.
|
|--------------------------------------------------------------------------
*/

namespace App\Core\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * Optimistic Locking Trait
 * 
 * Prevents concurrent updates by checking a 'lock_version' column.
 */
trait OptimisticLocking
{
    public static function bootOptimisticLocking(): void
    {
        static::saving(function (Model $model) {
            if ($model->exists && $model->isDirty()) {
                $originalVersion = $model->getOriginal('lock_version');
                $currentVersion = $model->lock_version;

                if ($originalVersion !== $currentVersion) {
                    throw new RuntimeException('Stale object detected. Document has been modified by another process.');
                }

                $model->lock_version++;
            }
        });
    }

    /**
     * Set the version check for queries.
     */
    protected function setKeysForSaveQuery($query)
    {
        $query = parent::setKeysForSaveQuery($query);

        if ($this->exists && isset($this->lock_version)) {
            $query->where('lock_version', $this->getOriginal('lock_version'));
        }

        return $query;
    }
}
