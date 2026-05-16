<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Profile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Profile>
 */
class ProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Profile::class);
    }

    /**
     * Получить топ-5 профилей, чьи посты имеют максимальное суммарное количество комментариев
     */
    public function getTopProfilesWithTotalCommentInTheirPosts(int $topMax = 5)
    {
        $qb = $this->createQueryBuilder('pr');
        $qb->select('pr');
        $qb->addSelect('COUNT(c.id) AS HIDDEN totalComments');
        $qb->innerJoin('pr.posts', 'p');
        $qb->leftJoin('p.comments', 'c');
        $qb->groupBy('pr.id');
        $qb->orderBy('totalComments', 'DESC');
        $qb->setMaxResults($topMax);

        return $qb->getQuery()->getResult();
    }

    /**
     * Профили с хотя бы одним постом, которые ни разу не оставляли комментарий
     * (в т.ч. комментарий к чужому посту учитывается через author, а не через p.comments).
     */
    public function getProfilesWithPostsAndWithoutComments(): array
    {
        $qb = $this->createQueryBuilder('pr');
        $qb->innerJoin('pr.posts', 'p');
        $qb->leftJoin(Comment::class, 'c', 'WITH', 'c.author = pr');
        $qb->groupBy('pr.id');
        $qb->having('COUNT(DISTINCT c.id) = 0');

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Profile[] Returns an array of Profile objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Profile
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
