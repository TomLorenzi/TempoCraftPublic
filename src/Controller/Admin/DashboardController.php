<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Card;
use App\Entity\Craft;
use App\Entity\CardType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return parent::index();
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('TempoCraft');
    }

    public function configureMenuItems(): iterable
    {
        //yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Users', 'fa fa-home', User::class);
        yield MenuItem::linkToCrud('Carte', 'fa fa-home', Card::class);
        yield MenuItem::linkToCrud('Craft', 'fa fa-home', Craft::class);
        //yield MenuItem::linkToCrud('Type de carte', 'fa fa-home', CardType::class);
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }
}
