<?php

namespace App\Controller\Admin;

use App\Entity\Categories;
use App\Form\CategoriesFormType;
use App\Repository\ProductsRepository;
use App\Repository\CategoriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/categories', name: 'admin_categories_')]
class CategoriesController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(CategoriesRepository $categoriesRepository): Response
    {
        $categories = $categoriesRepository->findBy([], ['name' => 'asc']);

        return $this->render('admin/categories/index.html.twig', compact('categories'));
    }
    #[Route('/ajout', name: 'add')]
    public function add(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $categori = new Categories();
        $categoryForm = $this->createForm(CategoriesFormType::class, $categori);
        $categoryForm->handlerequest($request);
        if($categoryForm->isSubmitted() && $categoryForm->isValid()){
            $slug = $slugger->slug($categori->getName());
            $categori->setSlug($slug);
            $em->persist($categori);
            $em->flush();
            $this->addFlash('success', 'categorie ajouté avec succès');

            return $this->redirectToRoute('admin_categories_index');
        }


        return $this->render('admin/categories/add.html.twig',[
            'categoryForm' => $categoryForm->createView()
        ]);
        // ['productForm' => $productForm]
    }
    #[Route('/stats', name: 'admin_categories_stats_index')]
public function categoryStats(EntityManagerInterface $em, CategoriesRepository $categoriesRepository): Response
{
    // Récupération de toutes les catégories
    $categories = $categoriesRepository->findAll();

    // Tableau pour stocker le nombre de produits par catégorie
    $productCountByCategory = [];

    // Calcul du nombre de produits par catégorie
    foreach ($categories as $category) {
        $productCount = count($category->getProducts());
        $productCountByCategory[$category->getName()] = $productCount;
    }

    // Préparation des données pour le rendu de la vue
    $categoryLabels = array_keys($productCountByCategory);
    $productCountData = array_values($productCountByCategory);

    // Rendu de la vue
    return $this->render('aadmin/user_stats.html.twig', [
        'category_labels' => json_encode($categoryLabels),
        'product_count_data' => json_encode($productCountData),
    ]);
}



}
