<?php

namespace App\Controller;

use App\Entity\Users;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OruserController extends AbstractController
{
    #[Route('/oruser/{id}', name: 'app_oruser')]
    public function index(Users $user, OrderRepository $orders): Response
    {
        $ab = $orders->findBy(['user' => $user->getId()]);
        $c = [];
    
        foreach ($ab as $i => $order) {
            $c[$i]['id'] = $order->getId();
            $c[$i]['date'] = $order->getCreatedAt();
            $c[$i]['dateliv'] = $order->getDateliv();
    
            if ($order->isIsVerified()) {
                $c[$i]['verifier'] = "demande acceptée";
            } elseif ($order->isIsVerified() === false) {
                $c[$i]['verifier'] = "demande refusée";
            } elseif ($order->isIsVerified() === null) {
                $c[$i]['verifier'] = "en attente";
            }
    
            $c[$i]['produits'] = [];
    
            foreach ($order->getProducts() as $j => $orderProduct) {
                $product = $orderProduct->getProduct()[0] ?? null;
                if ($product && !is_null($product->getName())) {
                    $quantity = $orderProduct->getQuantite();
                    $c[$i]['produits'][$j] = [
                        "product" => $product->getName(),
                        "quantity" => $quantity
                    ];
                }
            }
    
            // Remove orders without products
            if (empty($c[$i]['produits'])) {
                unset($c[$i]);
            }
        }
    
        return $this->render('oruser/index.html.twig', [
            'user' => $user->getFirstname(),
            'c' => $c,
        ]);
    }
    
    
    #[Route('/print/{id}', name: 'app_print')]
    public function printCommande(Request $request, OrderRepository $orders): Response
    {
        $id = $request->get('id');
    
        // Récupérer les informations de la commande à partir de son ID
        $order = $orders->find($id);
    
        // Vérifier si la commande existe
        if (!$order) {
            throw $this->createNotFoundException('La commande n\'existe pas.');
        }
    
        // Effectuer les opérations nécessaires pour l'impression, par exemple :
        // - Générer une page HTML ou un fichier PDF avec les données de la commande
        // - Utiliser une bibliothèque comme Dompdf ou TCPDF pour générer un fichier PDF
        // - Préparer les données pour l'impression, comme les informations du client, les produits commandés, etc.
    
        // Exemple de génération d'une page HTML pour l'impression
        $html = $this->renderView('oruser/print.html.twig', [
            'order' => $order,
        ]);
    
        // Retourner la réponse pour l'impression
        // Par exemple, retourner une page HTML ou un fichier PDF à télécharger
        return new Response($html);
    }
}
