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

namespace HireInSocial\Offers\Infrastructure\Doctrine\ORM\Application\Facebook;

use Doctrine\ORM\EntityManager;
use HireInSocial\Offers\Application\Facebook\Post;
use HireInSocial\Offers\Application\Facebook\Posts;
use HireInSocial\Offers\Application\Offer\Offer;

final class ORMPosts implements Posts
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function add(Post $post) : void
    {
        $this->entityManager->persist($post);
    }

    public function findFor(Offer $offer) : ?Post
    {
        return $this->entityManager->getRepository(Post::class)->findOneBy(['jobOfferId' => $offer->id()]);
    }
}
