<?php

namespace App\Repository;

use App\Entity\Level;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Level|null find($id, $lockMode = null, $lockVersion = null)
 * @method Level|null findOneBy(array $criteria, array $orderBy = null)
 * @method Level[]    findAll()
 * @method Level[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LevelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Level::class);
    }

    /**
     * @param array|null $filters
     * @return array $result
     */
    public function getListLevels($filters = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('level')
            ->from($this->_entityName, 'level');

        if (isset($filters['search']) && $filters['search'] !== '') { 
            $searchedValue = strtolower($filters['search']);
            $searchedColumns = array('level.dofusLevel');
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

        $columnSort = 'level.dofusLevel';
        $directionSort = 'asc';
        if(isset($filters['order'])) {
            $columnSort = $filters['order'][0]['column'];
            if(is_numeric($filters['order'][0]['column'])) {
                $orderColumn = $filters['order'][0]['column'];
                $columnName = $filters['columns'][$orderColumn]['data'];
                $columnSort = 'level.' . $columnName;
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
        foreach($result as $level) {
            $cardsArray = [];
            foreach ($level->getCards() as $card) {
                $cardsArray[] = array(
                    'id' => $card->getId(),
                    'name' => $card->getName(),
                    'image' => $card->getImage(),
                );
            }
            $levelArray = array(
                'dofusLevel' => $level->getDofusLevel(),
                'cards' => $cardsArray,
            );

            $serializedResult[] = $levelArray;
        }

        $totalLevels = $this->countLevelsFiltered($filters);

        $serializedList = array(
            'draw' => $filters['draw'],
            'recordsTotal' => $totalLevels,
            'recordsFiltered' => $totalLevels,
            'data' => $serializedResult
        );

        $serializedIndexList = array();
        foreach($serializedResult as $level) {
            $serializedIndexList[] = $level;
        }
        $serializedList['data'] = $serializedIndexList;

        return $serializedList;
    }

    /**
     * @param array|null $filters
     * @return array $result
     */
    public function countLevelsFiltered($filters = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('count(level.id)')
            ->from($this->_entityName, 'level');

        if (isset($filters['search']) && $filters['search'] !== '') { 
            $searchedValue = strtolower($filters['search']);
            $searchedColumns = array('level.dofusLevel');
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
