<?php

namespace App\Controller;

use App\Entity\Messages;
use App\Form\MessagesType;
use Symfony\Component\Mime\Message;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\MessagesRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MessagesController extends AbstractController
{
    /**
     * @Route("/messages", name="messages")
     */
    public function index(): Response
    {
        return $this->render('messages/index.html.twig', [
            'controller_name' => 'MessagesController',
        ]);
    }

    /**
 * @Route("/send", name="send")
 */
public function send(Request $request, EntityManagerInterface $em): Response
{
    $message = new Messages;
    $form = $this->createForm(MessagesType::class, $message);
    
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $sender = $this->getUser();
        $recipient = $form->get('recipient')->getData();

        // Vérifier que l'utilisateur connecté ne peut pas envoyer un message à lui-même
        if ($sender === $recipient) {
            $this->addFlash("danger", "Vous ne pouvez pas vous envoyer un message à vous-même.");
            return $this->redirectToRoute("send");
        }

        $message->setSender($sender);

        $em->persist($message);
        $em->flush();

        $this->addFlash("success", "Message envoyé avec succès.");
        return $this->redirectToRoute("messages");
    }

    return $this->render("messages/send.html.twig", [
        "form" => $form->createView()
    ]);
}

    /**
     * @Route("/received", name="received")
     */
    public function received(): Response
    {
        return $this->render('messages/received.html.twig');
    }


    /**
     * @Route("/sent", name="sent")
     */
    public function sent(): Response
    {
        return $this->render('messages/sent.html.twig');
    }

    /**
     * @Route("/read/{id}", name="read")
     */
    public function read(Messages $message,  EntityManagerInterface $em): Response
    {
        $message->setIsRead(true);
        $em->persist($message);
        $em->flush();

        return $this->render('messages/read.html.twig', compact("message"));
    }

    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function delete(Messages $message, EntityManagerInterface $em): Response
    {
        $em->remove($message);
        $em->flush();

        return $this->redirectToRoute("received");
    }

    #[Route('/msg_notif', name: 'msg_notif')]
    public function NotiftMessage(ManagerRegistry $doctrine, MessagesRepository $repository): Response {
        $msg = $repository->findMessagesSortedByDate();
        
        $unreadNotificationsCount = $repository->countUnreadNotifications();
        
        return $this->render('messages/notif.html.twig', [
            'messages' => $msg,
            'unreadNotificationsCount' => $unreadNotificationsCount,
        ]);
    }
    
}