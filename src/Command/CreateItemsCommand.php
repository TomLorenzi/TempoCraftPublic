<?php
namespace App\Command;

use App\Entity\Item;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Config\Definition\Exception\Exception;
use Doctrine\ORM\EntityManagerInterface;


class CreateItemsCommand extends Command
{
    protected static $defaultName = 'app:create-items';

    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Create all dofus Equipements')
            ->setName('app:create-items')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            ini_set('memory_limit','1G');
            ini_set('max_execution_time', 7200);
            $ch = curl_init();
            if ($ch === false) {
                throw new Exception('failed to initialize');
            }
            curl_setopt($ch, CURLOPT_URL, 'https://fr.dofus.dofapi.fr/equipments');
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            $response = curl_exec($ch);
            if ($response === false) {
                throw new Exception(curl_error($ch), curl_errno($ch));
            }

            $data = json_decode($response, true);
            $itemRepository = $this->em->getRepository(Item::class);
            foreach ($data as $i => $item) {
                $newItem = new Item();
                if (isset($item['name'])) {
                    $newItem->setName($item['name']);
                }
                if (isset($item['description'])) {
                    $newItem->setDescription($item['description']);
                }
                if (isset($item['imgUrl'])) {
                    $newItem->setImageUrl($item['imgUrl']);
                }
                if (isset($item['url'])) {
                    $newItem->setWikiUrl($item['url']);
                }
                if (isset($item['level'])) {
                    $newItem->setLevel($item['level']);
                }
                if (isset($item['ankamaId'])) {
                    $newItem->setAnkamaId($item['ankamaId']);
                }
                $this->em->persist($newItem);
            }

            $ch2 = curl_init();
            if ($ch2 === false) {
                throw new Exception('failed to initialize');
            }
            curl_setopt($ch2, CURLOPT_URL, 'https://fr.dofus.dofapi.fr/weapons');
            curl_setopt($ch2, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
            curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, 0);

            $response = curl_exec($ch2);
            if ($response === false) {
                throw new Exception(curl_error($ch2), curl_errno($ch2));
            }

            $data = json_decode($response, true);
            $itemRepository = $this->em->getRepository(Item::class);
            foreach ($data as $i => $item) {
                $newItem = new Item();
                if (isset($item['name'])) {
                    $newItem->setName($item['name']);
                }
                if (isset($item['description'])) {
                    $newItem->setDescription($item['description']);
                }
                if (isset($item['imgUrl'])) {
                    $newItem->setImageUrl($item['imgUrl']);
                }
                if (isset($item['url'])) {
                    $newItem->setWikiUrl($item['url']);
                }
                if (isset($item['level'])) {
                    $newItem->setLevel($item['level']);
                }
                if (isset($item['ankamaId'])) {
                    $newItem->setAnkamaId($item['ankamaId']);
                }
                $this->em->persist($newItem);
            }

            $ch3 = curl_init();
            if ($ch3 === false) {
                throw new Exception('failed to initialize');
            }
            curl_setopt($ch3, CURLOPT_URL, 'https://fr.dofus.dofapi.fr/idols');
            curl_setopt($ch3, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
            curl_setopt($ch3, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch3, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch3, CURLOPT_SSL_VERIFYPEER, 0);

            $response = curl_exec($ch3);
            if ($response === false) {
                throw new Exception(curl_error($ch3), curl_errno($ch3));
            }

            $data = json_decode($response, true);
            $itemRepository = $this->em->getRepository(Item::class);
            foreach ($data as $i => $item) {
                $newItem = new Item();
                if (isset($item['name'])) {
                    $newItem->setName($item['name']);
                }
                if (isset($item['description'])) {
                    $newItem->setDescription($item['description']);
                }
                if (isset($item['imgUrl'])) {
                    $newItem->setImageUrl($item['imgUrl']);
                }
                if (isset($item['url'])) {
                    $newItem->setWikiUrl($item['url']);
                }
                if (isset($item['level'])) {
                    $newItem->setLevel($item['level']);
                }
                if (isset($item['ankamaId'])) {
                    $newItem->setAnkamaId($item['ankamaId']);
                }
                $this->em->persist($newItem);
            }

            $ch4 = curl_init();
            if ($ch4 === false) {
                throw new Exception('failed to initialize');
            }
            curl_setopt($ch4, CURLOPT_URL, 'https://fr.dofus.dofapi.fr/consumables');
            curl_setopt($ch4, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
            curl_setopt($ch4, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch4, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch4, CURLOPT_SSL_VERIFYPEER, 0);

            $response = curl_exec($ch4);
            if ($response === false) {
                throw new Exception(curl_error($ch4), curl_errno($ch4));
            }

            $data = json_decode($response, true);
            $itemRepository = $this->em->getRepository(Item::class);
            foreach ($data as $i => $item) {
                $newItem = new Item();
                if (isset($item['name'])) {
                    $newItem->setName($item['name']);
                }
                if (isset($item['description'])) {
                    $newItem->setDescription($item['description']);
                }
                if (isset($item['imgUrl'])) {
                    $newItem->setImageUrl($item['imgUrl']);
                }
                if (isset($item['url'])) {
                    $newItem->setWikiUrl($item['url']);
                }
                if (isset($item['level'])) {
                    $newItem->setLevel($item['level']);
                }
                if (isset($item['ankamaId'])) {
                    $newItem->setAnkamaId($item['ankamaId']);
                }
                $this->em->persist($newItem);
            }


            $this->em->flush();

            $io->success('Insertion réussit');
        } catch (Exception $e) {
            dd($e->getMessage());
        }

        return 0;
    }
}
