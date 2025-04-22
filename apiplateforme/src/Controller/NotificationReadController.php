<?php

namespace App\Controller;

use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class NotificationReadController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    public function __invoke(Notification $notification): Notification
    {
        // Marquer la notification comme lue
        $notification->setLue(true);
        $notification->setDateLecture(new \DateTime());

        $this->em->persist($notification);
        $this->em->flush();

        return $notification;
    }
}