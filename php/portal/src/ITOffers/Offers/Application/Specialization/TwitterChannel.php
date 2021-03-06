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

namespace ITOffers\Offers\Application\Specialization;

use ITOffers\Offers\Application\Assertion;

final class TwitterChannel
{
    private string $accountId;

    private string $screenName;

    private string $oauthToken;

    private string $oauthSecret;

    public function __construct(string $accountId, string $screenName, string $oauthToken, string $oauthSecret)
    {
        Assertion::betweenLength($accountId, 3, 255);
        Assertion::betweenLength($screenName, 3, 255);
        Assertion::betweenLength($oauthToken, 3, 255);
        Assertion::betweenLength($oauthSecret, 3, 255);

        $this->accountId = $accountId;
        $this->screenName = \mb_strtolower($screenName);
        $this->oauthToken = $oauthToken;
        $this->oauthSecret = $oauthSecret;
    }

    /**
     * @return string
     */
    public function accountId() : string
    {
        return $this->accountId;
    }

    /**
     * @return string
     */
    public function screenName() : string
    {
        return $this->screenName;
    }

    /**
     * @return string
     */
    public function oauthToken() : string
    {
        return $this->oauthToken;
    }

    /**
     * @return string
     */
    public function oauthTokenSecret() : string
    {
        return $this->oauthSecret;
    }
}
