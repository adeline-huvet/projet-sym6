<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Form\IngredientType;
use Doctrine\ORM\EntityManager;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Container0jRK7Ab\PaginatorInterface_82dac15;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\{TextType, ButtonType, EmailType, HiddenType, PasswordType, TextareaType, SubmitType, NumberType, DateType, MoneyType, BirthdayType};

class IngredientController extends AbstractController
{
    /**
     * This function display all ingredients
     *
     * @param IngredientRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */

    #[Route('/ingredient', name: 'ingredient', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(IngredientRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {
        $ingredients = $paginator->paginate(
            $repository->findBy(['user' => $this->getUser()]),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('pages/ingredient/index.html.twig', [
            'ingredients' => $ingredients

        ]);
    }

    /**
     * This function allow us to create new ingredient
     * 
     * @param EntityManagerInterface $manager
     * @param Request $request
     * @return Response
     */

    #[Route('/ingredient/nouveau', name: 'ingredient.new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $ingredient = new Ingredient();
        $form = $this->createForm(IngredientType::class, $ingredient);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $ingredient = $form->getData();
            $ingredient->setUser($this->getUser());

            $manager->persist($ingredient);
            $manager->flush();

            $this->addFlash(

                'success',
                'Votre ingrédient à été crée avec succès !',
            );
            return $this->redirectToRoute('ingredient');
        }

        return $this->render('pages/ingredient/new.html.twig', [
            'form' => $form->createView()
        ]);
    }



    /**
     * This function allow us to edit ingredient
     * 
     * @param EntityManagerInterface $manager
     * @param Ingredient $ingredient
     * @param Request $request
     * @return Response
     */
    #[Route('ingredient/edition/{id}', 'ingredient.edit', methods: ['GET', 'POST'])]
    #[Security("is_granted('ROLE_USER') and user === ingredient.getUser()")]
    public function edit(
        Ingredient $ingredient, 
        Request $request, 
        EntityManagerInterface $manager
        ): Response
    {
        $form = $this->createForm(IngredientType::class, $ingredient);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $ingredient = $form->getData();

            $manager->persist($ingredient);
            $manager->flush();

            $this->addFlash(

                'success',
                'Votre ingrédient à été modifié avec succès !',
            );
            return $this->redirectToRoute('ingredient');
        }

        return $this->render(
            'pages/ingredient/edit.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }


    /**
     * This function allow us to delete ingredient
     * 
     * @param EntityManagerInterface $manager
     * @param Ingredient $ingredient
     * @return Response
     */
    #[Route('/ingredient/suppression/{id}', 'ingredient.delete', methods: ['GET'])]
    public function delete(EntityManagerInterface $manager, Ingredient $ingredient): Response
    {

        if (!$ingredient) {
            $this->addFlash(

                'warning',
                'L\'ingrédient en question n\'a pas été trouvé',
            );
            return $this->redirectToRoute('ingredient');
        }

        $manager->remove($ingredient);
        $manager->flush();

        $this->addFlash(

            'success',
            'Votre ingrédient à été supprimé avec succès !',
        );

        return $this->redirectToRoute('ingredient');
    }
}
