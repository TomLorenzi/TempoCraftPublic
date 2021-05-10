<?php

namespace App\Repository;

use App\Entity\Item;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Item|null find($id, $lockMode = null, $lockVersion = null)
 * @method Item|null findOneBy(array $criteria, array $orderBy = null)
 * @method Item[]    findAll()
 * @method Item[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

    /**
     * @param array|null $filters
     * @return array $result
     */
    public function getListItems($filters = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('item')
            ->from($this->_entityName, 'item');

        if (isset($filters['search']) && $filters['search'] !== '') { 
            $searchedValue = strtolower($filters['search']);
            $searchedColumns = array('item.id', 'item.name', 'item.level');
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

        $columnSort = 'item.id';
        $directionSort = 'asc';
        if(isset($filters['order'])) {
            $columnSort = $filters['order'][0]['column'];
            if(is_numeric($filters['order'][0]['column'])) {
                $orderColumn = $filters['order'][0]['column'];
                $columnName = $filters['columns'][$orderColumn]['data'];
                $columnSort = 'item.' . $columnName;
            }
            $directionSort = $filters['order'][0]['dir'];
        }

        $qb->orderBy($columnSort, $directionSort);

        if (isset($filters['start']) && $filters['start'] !== '') {
            $qb->setFirstResult($filters['start']);
        }

        if(isset($filters['length'])) {
            $qb->setMaxResults($filters['length']);
        }

        $result = $qb->getQuery()->getResult();

        $serializedResult = [];
        foreach($result as $item) {
            $itemArray = array(
                'id' => $item->getId(),
                'name' => $item->getName(),
                'level' => $item->getLevel(),
                'imageUrl' => $item->getImageUrl(),
                'wikiUrl' => $item->getWikiUrl(),
            );
            $serializedResult[] = $itemArray;
        }

        $totalItems = $this->countItemsFiltered($filters);

        $serializedList = array(
            'draw' => $filters['draw'],
            'recordsTotal' => $totalItems,
            'recordsFiltered' => $totalItems,
            'data' => $serializedResult
        );

        $serializedIndexList = array();
        foreach($serializedResult as $item) {
            $serializedIndexList[] = $item;
        }
        $serializedList['data'] = $serializedIndexList;

        return $serializedList;
    }

    /**
     * @param array|null $filters
     * @return array $result
     */
    public function countItemsFiltered($filters = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('count(item.id)')
            ->from($this->_entityName, 'item');

        if (isset($filters['search']) && $filters['search'] !== '') { 
            $searchedValue = strtolower($filters['search']);
            $searchedColumns = array('item.id', 'item.name', 'item.level');
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

        return $qb->getQuery()->getSingleScalarResult();
    }
}
