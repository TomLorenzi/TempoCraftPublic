<?php
namespace App\Command;

use App\Entity\Item;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Config\Definition\Exception\Exception;
use Doctrine\ORM\EntityManagerInterface;


class DownloadItemsCommand extends Command
{
    protected static $defaultName = 'app:download-items';

    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Download all dofus image equipements')
            ->setName('app:download-items')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            ini_set('memory_limit','1G');
            ini_set('max_execution_time', 7200);

            $listUrl = [
                'equipements' => 'https://fr.dofus.dofapi.fr/equipments',
                'weapons' => 'https://fr.dofus.dofapi.fr/weapons',
                'consumables' => 'https://fr.dofus.dofapi.fr/consumables',
                'idols' => 'https://fr.dofus.dofapi.fr/idols',
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
            

            $io->success('Téléchargement réussit');
        } catch (Exception $e) {
            dd($e->getMessage());
        }

        return 0;
    }
}
