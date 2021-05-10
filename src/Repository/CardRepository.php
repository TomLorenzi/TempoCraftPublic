<?php

namespace App\Repository;

use App\Entity\Card;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @method Card|null find($id, $lockMode = null, $lockVersion = null)
 * @method Card|null findOneBy(array $criteria, array $orderBy = null)
 * @method Card[]    findAll()
 * @method Card[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Card::class);
    }

    /**
     * @param array|null $filters
     * @return array $result
     */
    public function getListCards($filters = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('card')
            ->from($this->_entityName, 'card');

        //$qb->leftJoin('\App\Entity\User','user',Join::WITH,'card.creator = user.id');

        if (isset($filters['search']) && $filters['search'] !== '') { 
            $searchedValue = strtolower($filters['search']);
            $searchedColumns = array('card.id', 'card.name', 'card.lvl', 'card.category');
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

        if ('all' !== $filters['cat']) {
            $qb->andWhere("card.category LIKE '%" . $filters['cat'] . "%'");
        }

        if ('all' !== $filters['type']) {
            $qb->andWhere("card.type = :typeId")
                ->setParameter('typeId', $filters['type']);
        }

        if ('1' === $filters['onlyGold']) {
            $qb->andWhere("card.golden = 1");
        }

        if ('1' === $filters['craftAvailable']) {
            $qb->addSelect('count(distinct(craft)) AS HIDDEN nbCrafts')
                ->join('card.crafts', 'craft')
                ->having('nbCrafts > 0')
                ->groupBy('card.id');
        }

        $columnSort = 'card.id';
        $directionSort = 'asc';
        if(isset($filters['order'])) {
            $columnSort = $filters['order'][0]['column'];
            if(is_numeric($filters['order'][0]['column'])) {
                $orderColumn = $filters['order'][0]['column'];
                $columnName = $filters['columns'][$orderColumn]['data'];
                $columnSort = 'card.' . $columnName;
            }
            $directionSort = $filters['order'][0]['dir'];
        }

        $qb->orderBy($columnSort, $directionSort);

        $qb->andWhere('card.lvl BETWEEN :fromLvl AND :toLvl')
            ->setParameter('fromLvl', $filters['fromLvl'])
            ->setParameter('toLvl', $filters['toLvl']);

        if (isset($filters['start']) && $filters['start'] !== '') {
            $qb->setFirstResult($filters['start']);
        }

        if(isset($filters['length'])) {
            $qb->setMaxResults($filters['length']);
        }

        $result = $qb->getQuery()->getResult();

        $serializedResult = [];
        foreach($result as $card) {
            $cardArray = array(
                'id' => $card->getId(),
                'name' => $card->getName(),
                'type' => $card->getType()->getName(),
                'image' => $card->getImage(),
                'imageId' => $card->getImageId(),
                'lvl' => $card->getLvl(),
                'category' => $card->getCategory()
            );
            $serializedResult[] = $cardArray;
        }

        $totalCards = $this->countCardsFiltered($filters);

        $serializedList = array(
            'draw' => $filters['draw'],
            'recordsTotal' => $totalCards,
            'recordsFiltered' => $totalCards,
            'data' => $serializedResult
        );

        $serializedIndexList = array();
        foreach($serializedResult as $card) {
            $serializedIndexList[] = $card;
        }
        $serializedList['data'] = $serializedIndexList;

        return $serializedList;
    }

    /**
     * @param array|null $filters
     * @return array $result
     */
    public function countCardsFiltered($filters = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('count(card.id)')
            ->from($this->_entityName, 'card');

        if (isset($filters['search']) && $filters['search'] !== '') { 
            $searchedValue = strtolower($filters['search']);
            $searchedColumns = array('card.id', 'card.name', 'card.lvl', 'card.category');
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

        if ('all' !== $filters['cat']) {
            $qb->andWhere("card.category LIKE '%" . $filters['cat'] . "%'");
        }

        if ('all' !== $filters['type']) {
            $qb->andWhere("card.type = :typeId")
                ->setParameter('typeId', $filters['type']);
        }

        if ('1' === $filters['onlyGold']) {
            $qb->andWhere("card.golden = 1");
        }

        $qb->andWhere('card.lvl BETWEEN :fromLvl AND :toLvl')
            ->setParameter('fromLvl', $filters['fromLvl'])
            ->setParameter('toLvl', $filters['toLvl']);

        if ('1' === $filters['craftAvailable']) {
            $qb->addSelect('count(distinct(craft)) AS HIDDEN nbCrafts')
                ->join('card.crafts', 'craft')
                ->having('nbCrafts > 0');
            return $qb->getQuery()->getScalarResult()[0][1];
        }

        return $qb->getQuery()->getSingleScalarResult();
    }
}
