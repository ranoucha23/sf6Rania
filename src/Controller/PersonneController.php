<?php

namespace App\Controller;

use App\Entity\Personne;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('personne')]
class PersonneController extends AbstractController
{
    #[Route('/', name: 'personne.list')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $repository = $doctrine->getRepository(Personne::class);
        $personnes = $repository->findAll();
        return $this->render('personne/index.html.twig', ['personnes' => $personnes]);
    }

    #[Route('/alls/age/{ageMin}/{ageMax}', name: 'personne.list.age')]
    public function personnesByAge(ManagerRegistry $doctrine, $ageMin, $ageMax): Response
    {
        $repository = $doctrine->getRepository(Personne::class);
        $personnes = $repository->findPersonnesByAgeInterval($ageMin, $ageMax);
        return $this->render('personne/index.html.twig', ['personnes' => $personnes]);
    }

    #[Route('/stats/age/{ageMin}/{ageMax}', name: 'personne.list.age')]
    public function statsPersonnesByAge(ManagerRegistry $doctrine, $ageMin, $ageMax): Response
    {
        $repository = $doctrine->getRepository(Personne::class);
        $stats = $repository->statsPersonnesByAgeInterval($ageMin, $ageMax);
        return $this->render('personne/stats.html.twig', [
            'stats' => $stats[0], 
            'ageMin' => $ageMin, 
            'ageMax' => $ageMax
        ]);
    }

    #[Route('/alls/{page?1}/{nbre?12}', name: 'personne.list.alls')]
    public function indexAlls(ManagerRegistry $doctrine, $page, $nbre): Response
    {
        $repository = $doctrine->getRepository(Personne::class);
        $nbPersonne = $repository->count([]);
        $nbrePage = ceil($nbPersonne / $nbre);
        $personnes = $repository->findBy([], [], $nbre, ($page -1 ) * $nbre);
        return $this->render('personne/index.html.twig', [
            'personnes' => $personnes, 
            'isPaginated' => true,
            'nbrePage' => $nbrePage,
            'page' => $page,
            'nbre' => $nbre
        ]);
    }

    #[Route('/{id<\d+>}', name: 'personne.detail')]
    public function detail(ManagerRegistry $doctrine, $id): Response
    {
        $repository = $doctrine->getRepository(Personne::class);
        $personne = $repository->find($id);
        if(!$personne) {
            $this->addFlash('error', "La personne d'id $id n'existe pas ");
            return $this->redirectToRoute('personne.list');
        }
        return $this->render('personne/detail.html.twig', ['personne' => $personne]);
    }

    #[Route('/add', name: 'personne.add')]
    public function addPersonne(ManagerRegistry $doctrine): Response
    {
        //$this->getDoctrine() : Version Sf <= 5
        $entityManager = $doctrine->getManager();
        $personne = new Personne();
        $personne->setFirstname('Rania');
        $personne->setName('Jalel');
        $personne->setAge('27');
        //$personne2 = new Personne();
        //$personne2->setFirstname('Tasnim');
        //$personne2->setName('Jalel');
        //$personne2->setAge('19');

        // Ajouter l'operation d'insertion de la personne dans ma transaction
        $entityManager->persist($personne);
        //$entityManager->persist($personne2);

        //Exécute la transaction Todo
        $entityManager->flush();
        return $this->render('personne/detail.html.twig', [
            'personne' => $personne,
        ]);
    }

    #[Route('/delete/{id<\d+>}', name: 'personne.delete')]
    public function deletePersonne(ManagerRegistry $doctrine, $id): RedirectResponse
    {
        $repository = $doctrine->getRepository(Personne::class);
        $personne = $repository->find($id);
        // Récupérer la personne
        if($personne) {
            // Si la personne existe => le supprimer et retourner un flashMessage de succés
            $manager = $doctrine->getManager();
            // Ajoute la fonction de suppression dans la transaction
            $manager->remove($personne);
            // Exécuter la transaction
            $manager->flush();
            $this->addFlash('success', "La personne a été supprimé avec succès");
        } else {
            //Sinon retourner un flashMessage d'erreur
            $this->addFlash('error', "Personne inexistante");
        }
        return $this->redirectToRoute('personne.list.alls');
    }

    #[Route('/update/{id<\d+>}/{name}/{firstname}/{age}', name: 'personne.update')]
    public function updatePersonne($id, ManagerRegistry $doctrine, $name, $firstname, $age): RedirectResponse
    {
        $repository = $doctrine->getRepository(Personne::class);
        $personne = $repository->find($id);
        //Vérifier que la personne à mettre à jour existe
        if($personne) {
            // Si la personne existe => mettre à jour notre personne + message de succés
            $personne->setName($name);
            $personne->setFirstname($firstname);
            $personne->setAge($age);
            $manager = $doctrine->getManager();
            $manager->persist($personne);

            $manager->flush();
            $this->addFlash('success', "La personne a été mis à jour avec succès");
        } else {
            //Sinon retourner un flashMessage d'erreur
            $this->addFlash('error', "Personne inexistante");
        }
        return $this->redirectToRoute('personne.list.alls');
    }
}
