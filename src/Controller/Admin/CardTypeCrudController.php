<?php

namespace App\Controller\Admin;

use App\Entity\CardType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CardTypeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CardType::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
