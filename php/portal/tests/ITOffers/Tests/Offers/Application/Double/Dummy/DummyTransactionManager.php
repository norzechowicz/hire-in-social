<?php

declare(strict_types=1);

/*
 * This file is part of the itoffers.online project.
 *
 * (c) Norbert Orzechowicz <norbert@orzechowicz.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ITOffers\Tests\Offers\Application\Double\Dummy;

use ITOffers\Component\CQRS\System\TransactionManager;

final class DummyTransactionManager implements TransactionManager
{
    public function begin() : void
    {
    }

    public function commit() : void
    {
    }

    public function rollback() : void
    {
    }
}
