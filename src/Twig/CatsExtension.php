<?php

namespace App\Twig;

use App\Entity\Inspiration;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CatsExtension extends AbstractExtension
{
    private $em;

    public function __construct( EntityManagerInterface $em )
    {
        $this->em = $em;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction( 'cats' , [$this, 'getStories'] )
        ];        
    }

    public function getStories()
    {
        return $this->em->getRepository( Inspiration::class )->findBy([], [
            'created_at' => 'ASC',
        ]);
    }
}