<?php

namespace App\Controller;

use App\DTO\SeriesCreateationInputDTO;
use App\Entity\Episode;
use App\Entity\Season;
use App\Entity\Series;
use App\Form\SeriesType;
use App\Message\SeriesWasCreated;
use App\Repository\SeriesRepository;
use Doctrine\ORM\EntityManagerInterface;
// use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// use Symfony\Component\Form\Extension\Core\Type\SubmitType;
// use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
// use Symfony\Component\Mailer\MailerInterface;
// use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class SeriesController extends AbstractController
{
    public function __construct(
        private SeriesRepository $seriesRepository,
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $messenger,
        // private MailerInterface $mailer,
    ) {
    }

    // public function index(Request $request): Response
    #[Route('/series', name: 'app_series', methods: ['GET'])]
    public function seriesList(Request $request): Response
    {
        $seriesList = $this->seriesRepository->findAll();
        // $session = $request->getSession();
        // $successMessage = $session->get('success');
        // $session->remove('success');

        return $this->render('series/index.html.twig', [
            'seriesList' => $seriesList,
            // 'successMessage' => $successMessage,
        ]);
    }

    #[Route('/series/create', name: 'app_series_form', methods: ['GET'])]
    public function addSeriesForm(): Response
    {
        // $seriesForm = $this->createFormBuilder(new Series(''))
        //     ->add('name', TextType::class, ['label' => 'Nome:'])
        //     ->add('save', SubmitType::class, ['label' => 'Adicionar'])
        //     ->getForm();

        $series = new SeriesCreateationInputDTO();
        $seriesForm = $this->createForm(SeriesType::class, $series);
        return $this->renderForm('series/form.html.twig', compact(var_name: 'seriesForm'));
        // return $this->render('series/form.html.twig');
    }

    #[Route('/series/create', name: 'app_add_series', methods: ['POST'])]
    public function addSeries(Request $request): Response
    {
        $input = new SeriesCreateationInputDTO();
        $seriesForm = $this
            ->createForm(SeriesType::class, $input)
            ->handleRequest($request);
        if (!$seriesForm->isValid()) {
            return $this->renderForm('series/form.html.twig', compact(var_name: 'seriesForm'));
        }

        // $user = $this->getUser();

        $series = new Series($input->seriesName);
        
        // $email = (new TemplatedEmail())
        //     ->from('sistema@example.com')
        //     ->to($user->getUserIdentifier())
        //     //->cc('cc@example.com')
        //     //->bcc('bcc@example.com')
        //     //->replyTo('fabien@example.com')
        //     //->priority(Email::PRIORITY_HIGH)
        //     ->subject('Nova série criada')
        //     ->text("Série {$series->getName()} foi criada")
        //     ->htmlTemplate('emails/series-created.html.twig')
        //     ->context(compact('series'));
        // $this->mailer->send($email);
        
        // $seriesName = $request->request->get(key: 'name');
        // $series = new Series($seriesName);

        // $request->getSession()->set('success', "Série \"{$seriesName}\" adicionada com sucesso");
        
        for ($i=1; $i <= $input->seasonsQuantity; $i++) { 
            $season = new Season($i);
            for ($j=1; $j <= $input->episodesPerSeason; $j++) { 
                $season->addEpisode(new Episode($j));
            }
            $series->addSeason($season);
        }

        $this->messenger->dispatch(new SeriesWasCreated($series));
        
        $this->addFlash('success', "Série \"{$series->getName()}\" adicionada com sucesso");

        $this->seriesRepository->add($series, flush: true);
        return new RedirectResponse(url: '/series');
    }

    #[Route(
        '/series/delete/{id}',
        name: 'app_delete_series',
        methods: ['DELETE'],
        requirements: ['id' => '[0-9]+']
    )]
    public function deleteSeries(int $id, Request $request): Response
    {
        // $id = $request->attributes->get(key:'id');
        // $series = $this->entityManager->getReference(Series::class, $id);
        // $this->seriesRepository->remove($series, flush: true);
        $this->seriesRepository->removeById($id);
        $this->addFlash('success', 'Série removida com sucesso');
        // $session = $request->getSession();
        // $session->set('success', 'Série removida com sucesso');
        return new RedirectResponse(url: '/series');
    }

    #[Route('/series/edit/{series}', name: 'app_edit_series_form', methods: ['GET'])]
    public function editSeriesForm(Series $series): Response
    {
        $seriesForm = $this->createForm(SeriesType::class, $series, ['is_edit'=> true,]);
            
        return $this->renderForm('series/form.html.twig', compact('seriesForm', 'series'));
        // return $this->render('series/form.html.twig', compact(var_name:'series'));
    }

    #[Route('/series/edit/{series}', name: 'app_store_series_changes', methods: ['PATCH'])]
    public function storeSeriesChanges(Series $series, Request $request): Response
    {
        $seriesForm = $this->createForm(SeriesType::class, $series, ['is_edit'=> true,]);
        $seriesForm->handleRequest($request);

        if (! $seriesForm->isValid()) {
            return $this->renderForm('series/form.html.twig', compact('seriesForm', 'series'));
        }

        // $series->setName($request->request->get(key: 'name'));

        // $request->getSession()->set('success', "Série \"{$series->getName()}\" editada com sucesso");
        $this->addFlash('success', "Série \"{$series->getName()}\" editada com sucesso");
        $this->entityManager->flush();
        return new RedirectResponse(url: '/series');
    }

}
