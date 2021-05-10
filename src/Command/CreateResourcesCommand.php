<?php
namespace App\Command;

use App\Entity\Item;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Config\Definition\Exception\Exception;
use Doctrine\ORM\EntityManagerInterface;


class CreateResourcesCommand extends Command
{
    protected static $defaultName = 'app:create-resources';

    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Create all dofus Resources')
            ->setName('app:create-resources')
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
            curl_setopt($ch, CURLOPT_URL, 'https://fr.dofus.dofapi.fr/resources');
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
            $this->em->flush();

            $listUrl = [
                'resources' => 'https://fr.dofus.dofapi.fr/resources',
            ];

            $itemRepository = $this->em->getRepository(Item::class);
            foreach ($listUrl as $key => $url) {
                $jsonUrlCatcher = file_get_contents($url);
                $convertedArray = json_decode($jsonUrlCatcher, true);

                for ($i = 0; $i < count($convertedArray); $i++) {
                    try {
                        $imgUrlGet = $convertedArray[$i]['imgUrl'];
                        $newUrl = str_replace("s.ankama.com/www/", "", $imgUrlGet);
                        $item = $itemRepository->findOneByName($convertedArray[$i]['name']);
                        $imgName = basename($imgUrlGet);
                        $item->setImageUrl("/assets/images/items/$key/$imgName");
                        $fileName = "public/assets/images/items/$key/$imgName";
                        
                        file_put_contents($fileName, file_get_contents($newUrl));
                        $this->em->persist($item);
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
            $this->em->flush();

            $io->success('Insertion réussit');
        } catch (Exception $e) {
            dd($e->getMessage());
        }

        return 0;
    }
}
