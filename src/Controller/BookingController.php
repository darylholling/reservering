<?php
/**
 * Created by PhpStorm.
 * User: gebruiker
 * Date: 20-1-2019
 * Time: 19:45
 */

namespace App\Controller;

use App\Entity\Reservering;
use App\Entity\Tafel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BookingController
 * @package App\Controller
 */
class BookingController extends AbstractController
{
    /**
     * @Route("/", name="booking_index")
     */
    public function index()
    {
        $session = $this->get('request_stack')->getCurrentRequest()->getSession();
        $boeking = $session->get('booking', array());

        if (empty($boeking)) {
            $datumtijd = new \DateTime();
            $boeking = ['aantal' => 5, 'datumtijd' => $datumtijd];
        }

        return $this->render('booking/index.html.twig', [
            'booking' => $boeking,
            'form' => null
        ]);
    }

    /**
     * @Route("new", name="booking_new")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function new(Request $request)
    {
        $session = $this->get('session');
        //        $session = $this->get('request_stack')->getCurrentRequest()->getSession();

        $boeking = $session->get('booking', array());


        if (empty($boeking)) {
            $datumtijd = new \DateTime();
            $boeking = ['aantal' => 5, 'datumtijd' => $datumtijd];
            $form = $this->createFormBuilder($boeking)
                ->add('aantal', IntegerType::class)
                ->add('datumtijd', DateTimeType::class)
                ->add('save', SubmitType::class, ['label' => 'Zoek tafels'])
                ->getForm();
        } else {

            $form = $this->createFormBuilder($boeking)
                ->add('aantal', IntegerType::class)
                ->add('datumtijd', DateTimeType::class)
                ->add('save', SubmitType::class, ['label' => 'Reserveren?'])
                ->getForm();
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $boeking = $form->getData();
            $em = $this->getDoctrine()->getManager();

            $repository = $this->getDoctrine()->getRepository(Reservering::class);

            $start = date('Y-m-d H:m:s', strtotime('-4 hours', $boeking['datumtijd']->getTimestamp()));
            $eind = date('Y-m-d H:m:s', strtotime('+4 hours', $boeking['datumtijd']->getTimestamp()));

            $query = $repository->createQueryBuilder('p')
                ->where('p.datum BETWEEN :startDateTime AND :endDateTime')
                ->setParameter('startDateTime', $start)
                ->setParameter('endDateTime', $eind)
                ->getQuery();

            $reservaties = $query->getResult();

            $tafels = $em->getRepository(Tafel::class)->findAll();

            $rtafel = [];

            foreach ($reservaties as $reservatie) {
                foreach ($reservatie->getTafel() as $tafel) {
                    array_push($rtafel, $tafel->getId());
                }
            }

            foreach ($tafels as $key => $tafel) {
                foreach ($rtafel as $rt) {
                    if ($tafel->getId() == $rt) {
                        unset($tafels[$key]); //delete gereserveerde tafel.
                    }
                }
            }

            $boeking['tafels'] = $tafels;
            $session->set('booking', $boeking);
            return $this->redirectToRoute('booking_checkout');
        }


        return $this->render('booking/index.html.twig', [
                'booking' => $boeking,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/checkout", name="booking_checkout")
     */
    public function checkout(Request $request)
    {
        $session = $this->get('session');

//        $session = $this->get('request_stack')->getCurrentRequest()->getSession();
        $boeking = $session->get('booking', array());

        if (!empty($boeking['tafels'])) {
            $tafels = [];
            foreach ($boeking['tafels'] as $key => $personen) {
                $tafels[$personen . ' personen '] = $key;
            }
            $form = $this->createFormBuilder($boeking)
                ->add('aantal', IntegerType::class)
                ->add('datumtijd', DateTimeType::class)
                ->add('tafels', ChoiceType::class, [
                    'choices' => $tafels,
                    'multiple' => true,
                ])
                ->add('save', SubmitType::class, array('label' => 'Maak Boeking'))
                ->getForm();
        } else {
//            Volgeboekt voor datum.
            dump($boeking);
            die();
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $boeking = $form->getData();
//            dump($boeking);
            // afhandelen van reservatie en in dbase plaatsen.
            $em = $this->getDoctrine()->getManager();
            $reservatie = new Reservering();
            $reservatie->setAantalPersonen($boeking['aantal']);
            $reservatie->setDatum($boeking['datumtijd']);
            $reservatie->setUser($this->getUser());  // dit zou nog een invoer veld kunnen worden.
            foreach ($boeking['tafels'] as $tfl) {
                $tafel = $this->getDoctrine()->getRepository(Tafel::class)->findOneBy(['id' => ($tfl + 1)]);
                $reservatie->addTafel($tafel);
            }
            $em->persist($reservatie);
            $em->flush();
            $session->remove('boeking');
            return $this->redirectToRoute('booking_index');
        }
        return $this->render('booking/index.html.twig', [
            'boeking' => $boeking,
            'form' => $form->createView(),
        ]);
    }
}