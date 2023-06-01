<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Form\IngredientType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IngredientController extends AbstractController
{
    /**
     * This controller displays all ingredients
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param PaginatorInterface $paginator
     * @return Response
     */
    #[Route('/ingredient', name: 'ingredient.index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager, PaginatorInterface $paginator): Response
    {
        //pagination
        $ingredients = $paginator->paginate(
            $entityManager->getRepository(Ingredient::class)->findAll(),
            $request->query->getInt('page', 1),
            10 /*limit per page*/
        );
        return $this->render('pages/ingredient/index.html.twig', [
            'ingredients' => $ingredients
        ]);
    }
    /**
     * controller show how to create a new ingredient
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/ingredient/nouveau', name: 'ingredient.new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ingredient = new Ingredient();
        $form = $this->createForm(IngredientType::class, $ingredient);
        //Envoi de la requête
        $form->handleRequest($request);
        //si le form est soumis et valide en respectant toutes les contraintes de IngredientType
        if ($form->isSubmitted() && $form->isValid()) {
            // dd($form->getData());
            $ingredient = $form->getData();

            $entityManager->persist($ingredient);
            $entityManager->flush();
            //Message flash qui doit être récupéré dans la page de redirection ingredient.index
            $this->addFlash('success', "Votre ingrédient " . '<b>' . $ingredient->getName() . '</b>' . " a été créé avec succès !");

            //Rediriger 
            return $this->redirectToRoute('ingredient.index');
        }

        return $this->render('pages/ingredient/new.html.twig', [
            'form' => $form->createView()
        ]);
    }
    //Ancienne méthode
    // #[Route('/ingredient/edition/{id}', name: 'ingredient.edit', methods: ['GET', 'POST'])]
    // public function edit(Request $request, EntityManagerInterface $entityManager, int $id): Response
    // {   
    //     $ingredient = $entityManager->getRepository(Ingredient::class)->findOneBy(["id" => $id]);
    //     $form = $this->createForm(IngredientType::class, $ingredient);

    //     return $this->render('pages/ingredient/edit.html.twig', [
    //         'form' => $form->createView()
    //     ]);
    // }

    //Méthode avec ParamConverter, passer directement l'objet Ingredient (qui lui même contient l'id) au lieu d'aller chercher l'id    
    #[Route('/ingredient/edition/{id}', name: 'ingredient.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager, Ingredient $ingredient): Response
    {
        // dd($ingredient);
        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $ingredient = $form->getData();
            // $entityManager->persist($ingredient);


            $entityManager->flush();

            $this->addflash('success', "L'ingrédient a bien été modifié avec succès.");

            return $this->redirectToRoute('ingredient.index');
        }
        return $this->render('pages/ingredient/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    //Delete
    // #[Route('/ingredient/suppression/{id}', name: 'ingredient.delete', methods: ['GET'])]
    // public function delete(Ingredient $ingredient, EntityManagerInterface $entityManager): Response
    // {

    //     if (!$ingredient) {
    //         $this->addflash('error', "L'ingrédient en question n'a pas été trouvé !");
    //         return $this->redirectToRoute('ingredient.index');
    //     }

    //     $entityManager->remove($ingredient);
    //     $entityManager->flush();

    //     $this->addflash('success', "L'ingrédient " . $ingredient->getName() . " a été supprimé");

    //     return $this->redirectToRoute('ingredient.index');
    // }

    #[Route('/ingredient/suppression/{id}', name: 'ingredient.delete', methods: ['GET'])]
    public function delete(int $id, EntityManagerInterface $entityManager): Response
    {

        $ingredient = $entityManager->getRepository(Ingredient::class)->findOneBy(['id' => $id]);
        if (is_null($ingredient)) {
            $this->addflash('error', "L'ingrédient en question n'a pas été trouvé !");
            return $this->redirectToRoute('ingredient.index');
        }

        $entityManager->remove($ingredient);
        $entityManager->flush();

        $this->addflash('success', "L'ingrédient " . $ingredient->getName() . " a été supprimé");

        return $this->redirectToRoute('ingredient.index');
    }
}
