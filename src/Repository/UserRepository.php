<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Craft;
use App\Entity\UpVote;
use App\Entity\Report;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function countUser()
    {
        $qb = $this->createQueryBuilder('u');
        $qb->select('COUNT(u)');
        
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function topUsers()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('user as u, count(craft) as nbVerified')
            ->from($this->_entityName, 'user')
            ->leftJoin('\App\Entity\Craft','craft',Join::WITH,'craft.creator = user.id')
            ->andWhere('craft.isVerified = 1')
            ->andWhere('craft.date = CURRENT_DATE()')
            ->orderBy('nbVerified', 'asc')
            ->setMaxResults(3)
            ->groupBy('user.id');
        
        $results = $qb->getQuery()->getResult();
        
        $serializedResult = [];
        foreach($results as $result) {
            $resultArray = array(
                'pseudo' => $result['u']->getPseudo(),
                'nbVerified' => $result['nbVerified'],
            );
            $serializedResult[] = $resultArray;
        }

        return $serializedResult;
    }

    /**
     * @param array|null $filters
     * @return array $result
     */
    public function getListUsers($filters = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('user as u, count(distinct(upvote)) AS nbVotes, count(distinct(report)) AS nbReports, count(craft) as nbVerified')
            ->from($this->_entityName, 'user');
            
        $qb->leftJoin('\App\Entity\UpVote','upvote',Join::WITH,'upvote.user = user.id');
        $qb->leftJoin('\App\Entity\Report','report',Join::WITH,'report.user = user.id');
        $qb->leftJoin('\App\Entity\Craft','craft',Join::WITH,'craft.creator = user.id');
        $qb->andWhere('craft.isVerified = 1');
        $qb->orderBy('nbVerified', 'desc');

        if(isset($filters['length'])) {
            $qb->setMaxResults($filters['length']);
        }

        $qb->groupBy('user.id');

        $results = $qb->getQuery()->getResult();

        $serializedResult = [];
        foreach($results as $result) {
            $resultArray = array(
                'pseudo' => $result['u']->getPseudo(),
                'nbVotes' => $result['nbVotes'],
                'nbReports' => $result['nbReports'],
                'nbVerified' => $result['nbVerified'],
            );
            $serializedResult[] = $resultArray;
        }

        $totalResult = count($serializedResult);

        $serializedList = array(
            'draw' => $filters['draw'],
            'recordsTotal' => $totalResult,
            'recordsFiltered' => $totalResult,
            'data' => $serializedResult
        );

        $serializedIndexList = [];
        foreach($serializedResult as $result) {
            $serializedIndexList[] = $result;
        }
        $serializedList['data'] = $serializedIndexList;

        return $serializedList;
    }
}
