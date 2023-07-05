<?php

namespace App\Controller\Admin;

use App\Repository\UsersRepository;
use App\Repository\CategoriesRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin', name: 'admin_')]
class MainController extends AbstractController
{
    #[Route('/stats', name: 'index')]
    public function userStats(UsersRepository $userRepository, CategoriesRepository $categoriesRepository): Response
    {
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
        
        // Récupération des données d'utilisateurs
        $users = $userRepository->findAll();
        
        $totalUsers = count($users);

        // Calcul du nombre total de produits
        $totalProducts = array_sum($productCountData);
        // Initialisation des variables pour les statistiques par département
        $userDepartments = [];
    
        // Initialisation des variables pour les statistiques par rôle
        $roleStats = [
            'ROLE_ADMIN' => 0,
            'ROLE_PRODUCT_ADMIN' => 0,
            'MEMBRE' => 0,
        ];
    
        // Calcul des statistiques par département et par rôle
        foreach ($users as $user) {
            // Calcul des statistiques par département
            $userDepartment = $user->getDepartment();
            if (!empty($userDepartment) && is_string($userDepartment)) {
                if (isset($userDepartments[$userDepartment])) {
                    $userDepartments[$userDepartment]++;
                } else {
                    $userDepartments[$userDepartment] = 1;
                }
            }
    
            // Calcul des statistiques par rôle
            $roles = $user->getRoles();
            if (in_array('ROLE_ADMIN', $roles)) {
                $roleStats['ROLE_ADMIN']++;
            } elseif (in_array('ROLE_PRODUCT_ADMIN', $roles)) {
                $roleStats['ROLE_PRODUCT_ADMIN']++;
            } else {
                $roleStats['MEMBRE']++;
            }
        }
    
        // Préparation des données pour le rendu de la vue pour les statistiques par département
        $userDepartmentsLabels = array_keys($userDepartments);
        $userDepartmentsData = array_values($userDepartments);
    
        // Préparation des données pour le rendu de la vue pour les statistiques par rôle
        $roleStatsLabels = array_keys($roleStats);
        $roleStatsData = array_values($roleStats);
    
        // Rendu de la vue
        return $this->render('admin/user_stats.html.twig', [
            'users' => $users,
            'user_departments_labels' => json_encode($userDepartmentsLabels),
            'user_departments_data' => json_encode($userDepartmentsData),
            'role_stats_labels' => json_encode($roleStatsLabels),
            'total_users' => $totalUsers,
            'total_products' => $totalProducts,
            'role_stats_data' => json_encode($roleStatsData),
            'category_labels' => json_encode($categoryLabels),
            'product_count_data' => json_encode($productCountData),
        ]);
    }
}