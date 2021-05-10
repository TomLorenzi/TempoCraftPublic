<?php

namespace App\Repository;

use App\Entity\Craft;
use App\Entity\Item;
use App\Entity\User;
use App\Entity\Card;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Craft|null find($id, $lockMode = null, $lockVersion = null)
 * @method Craft|null findOneBy(array $criteria, array $orderBy = null)
 * @method Craft[]    findAll()
 * @method Craft[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CraftRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Craft::class);
    }

    public function countCraft()
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select('COUNT(c)');
        
        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param array|null $filters
     * @return array $result
     */
    public function getListCrafts($filters = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('craft, count(distinct(upvote)) AS HIDDEN nbVotes, count(distinct(report)) AS HIDDEN nbReports')
            ->from($this->_entityName, 'craft');

        $qb->leftJoin('\App\Entity\Item','item',Join::WITH,'craft.item = item.id');
        $qb->leftJoin('\App\Entity\UpVote','upvote',Join::WITH,'upvote.craft = craft.id');
        $qb->leftJoin('\App\Entity\Report','report',Join::WITH,'report.craft = craft.id');

        if (isset($filters['search']) && $filters['search'] !== '') { 
            $searchedValue = strtolower($filters['search']);
            $searchedColumns = array('craft.id', 'item.name', 'item.id');
            $first = true;
            foreach ($searchedColumns as $column) {
                if ($first === true) {
                    $condition = $column . " LIKE '%$searchedValue%'";
                } else {
                    $condition .= " OR " . $column . " LIKE '%$searchedValue%'";
                }
                $first = false;
            }
            $qb->andWhere($condition);
        }
        $qb->andWhere('craft.isFalse = 0');

        $columnSort = 'craft.id';
        $directionSort = 'asc';
        if(isset($filters['order'])) {
            $columnSort = $filters['order'][0]['column'];
            if(is_numeric($filters['order'][0]['column'])) {
                $orderColumn = $filters['order'][0]['column'];
                $columnName = $filters['columns'][$orderColumn]['data'];
                if ('level' === $columnName) {
                    $columnSort = 'item.' . $columnName;
                } else {
                    $columnSort = 'craft.' . $columnName;
                }
            }
            $directionSort = $filters['order'][0]['dir'];
        }

        $qb->groupBy('craft.id');

        if ('upvote' === $columnName) {
            $qb->orderBy('nbVotes', $directionSort);
        } else if ('report' === $columnName) {
            $qb->orderBy('nbReports', $directionSort);
        } else {
            $qb->orderBy($columnSort, $directionSort);
        }

        if (isset($filters['card'])) {
            $qb->join('craft.cards', 'card')
                ->where('card.id = :cardId')
                ->setParameter('cardId', $filters['card']);
        }

        if (isset($filters['item'])) {
            $qb->andWhere("item.id = :itemId")
               ->setParameter('itemId', $filters['item']);
        }

        if (isset($filters['fromLvl']) && isset($filters['toLvl'])) {
            $qb->andWhere('item.level BETWEEN :fromLvl AND :toLvl')
                ->setParameter('fromLvl', $filters['fromLvl'])
                ->setParameter('toLvl', $filters['toLvl']);
        }

        if (isset($filters['start']) && $filters['start'] !== '') {
            $qb->setFirstResult($filters['start']);
        }

        if(isset($filters['length'])) {
            $qb->setMaxResults($filters['length']);
        }

        $result = $qb->getQuery()->getResult();

        $serializedResult = [];

        foreach($result as $craft) {
            $user = $craft->getCreator();
            $creator = array(
                'id' => $user->getId(),
                'pseudo' => $user->getPseudo(),
            );

            $item = $craft->getItem();
            $givenItem = array(
                'id' => $item->getId(),
                'name' => $item->getName(),
                'imageUrl' => $item->getImageUrl(),
            );

            $cardsArray = [];

            foreach ($craft->getCards() as $card) {
                $cardsArray[] = array(
                    'id' => $card->getId(),
                    'name' => $card->getName(),
                    'image' => $card->getImage(),
                );
            }

            $craftArray = array(
                'id' => $craft->getId(),
                'item' => $givenItem,
                'level' => $item->getLevel(),
                'cards' => $cardsArray,
                'report' => count($craft->getReports()),
                'upvote' => count($craft->getUpvotes()),
                'isVerified' => $craft->getIsVerified(),
                'creator' => $creator,
            );
            $serializedResult[] = $craftArray;
        }

        $totalCrafts = $this->countCraftsFiltered($filters);
        if (isset($filters['card'])) {
            $totalCrafts = count($serializedResult);
        }

        $serializedList = array(
            'draw' => $filters['draw'],
            'recordsTotal' => $totalCrafts,
            'recordsFiltered' => $totalCrafts,
            'data' => $serializedResult
        );

        $serializedIndexList = array();
        foreach($serializedResult as $craft) {
            $serializedIndexList[] = $craft;
        }
        $serializedList['data'] = $serializedIndexList;

        return $serializedList;
    }

    /**
     * @param array|null $filters
     * @return array $result
     */
    public function countCraftsFiltered($filters = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('count(craft.id)')
            ->from($this->_entityName, 'craft');

        $qb->leftJoin('\App\Entity\Item','item',Join::WITH,'craft.item = item.id');

        if (isset($filters['search']) && $filters['search'] !== '') { 
            $searchedValue = strtolower($filters['search']);
            $searchedColumns = array('craft.id', 'item.name', 'item.id');
            $first = true;
            foreach ($searchedColumns as $column) {
                if ($first === true){
                    $condition = $column . " LIKE '%$searchedValue%'";
                } else {
                    $condition .= " OR " . $column . " LIKE '%$searchedValue%'";
                }
                $first = false;
            }
            $qb->andWhere($condition);
        }

        if (isset($filters['item'])) {
            $qb->andWhere("item.id = " . $filters['item']);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }
}
