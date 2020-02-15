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

namespace ITOffers\Offers\Application\User;

use ITOffers\Offers\Application\Assertion;
use ITOffers\Offers\Application\Calendar;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class User
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $fbUserAppId;

    /**
     * @var string
     */
    private $linkedInUserAppId;

    /**
     * @var string
     */
    private $email;

    /**
     * @var \DateTimeImmutable
     */
    private $createdAt;

    /**
     * @var \DateTimeImmutable
     */
    private $blockedAt;

    private function __construct(\DateTimeImmutable $createdAt, string $email)
    {
        Assertion::email($email);

        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = $createdAt;
        $this->email = \mb_strtolower($email);
    }

    public static function fromFacebook(string $userAppId, string $email, Calendar $calendar) : self
    {
        Assertion::betweenLength($userAppId, 0, 255);

        $user = new self($calendar->currentTime(), $email);
        $user->fbUserAppId = $userAppId;

        return $user;
    }

    public static function fromLinkedIn(string $userAppId, string $email, Calendar $calendar) : self
    {
        Assertion::betweenLength($userAppId, 0, 255);

        $user = new self($calendar->currentTime(), $email);
        $user->linkedInUserAppId = $userAppId;

        return $user;
    }

    public function id() : UuidInterface
    {
        return Uuid::fromString($this->id);
    }

    public function block(Calendar $calendar) : void
    {
        $this->blockedAt = $calendar->currentTime();
    }
}