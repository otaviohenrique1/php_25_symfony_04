<?php

namespace App\Controller;

use App\Entity\Series;
use DateInterval;
// use App\Repository\SeasonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Doctrine\ORM\PersistentCollection;

class SeasonsController extends AbstractController
{
    public function __construct(
        private CacheInterface $cache
    ) {
    }

    #[Route('/series/{series}/seasons', name: 'app_seasons')]
    public function index(Series $series): Response
    {
        $seasons = $this->cache->get("seasons_{$series->getId()}_seasons", function(ItemInterface $item) use ($series) {
            // PT10S => 10 segundos
            $item->expiresAfter(new DateInterval(duration: 'PT10S'));
            
            /** @var PersistentCollection $seasons */
            $seasons = $series->getSeasons();
            $seasons->initialize();
            
            return $seasons;
        });
        // $seasons = $series->getSeasons();

        return $this->render('seasons/index.html.twig', [
            'series' => $series,
            'seasons' => $seasons,
        ]);
    }

    /*
    public function index(int $seriesId): Response
    {
        $seasons = $this->repository->findBy([
            'series'=> $seriesId,
        ]);

        return $this->render('seasons/index.html.twig', [
            'seasons' => $seasons,
        ]);
    }
    */
}
