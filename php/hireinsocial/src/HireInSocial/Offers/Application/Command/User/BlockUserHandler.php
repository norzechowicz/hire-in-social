<?php

declare(strict_types=1);

/*
 * This file is part of the Hire in Social project.
 *
 * (c) Norbert Orzechowicz <norbert@orzechowicz.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HireInSocial\Offers\Application\Command\User;

use HireInSocial\Offers\Application\System\Calendar;
use HireInSocial\Offers\Application\System\Handler;
use HireInSocial\Offers\Application\User\Users;
use Ramsey\Uuid\Uuid;

class BlockUserHandler implements Handler
{
    /**
     * @var Users
     */
    private $users;

    /**
     * @var Calendar
     */
    private $calendar;

    public function __construct(Users $users, Calendar $calendar)
    {
        $this->users = $users;
        $this->calendar = $calendar;
    }

    public function handles() : string
    {
        return BlockUser::class;
    }

    public function __invoke(BlockUser $command) : void
    {
        $this->users->getById(Uuid::fromString($command->getUserId()))
            ->block($this->calendar);
    }
}