<?php

namespace App\Controller\Admin;

use App\Entity\MailSend;
use App\Form\MailSendType;
use App\Repository\MailSendRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/mail/send")
 */
class MailSendController extends AbstractController
{
    /**
     * @Route("/", name="admin_mail_send_index", methods={"GET"})
     */
    public function index(MailSendRepository $mailSendRepository): Response
    {
        return $this->render('admin/mail_send/index.html.twig', [
            'mail_sends' => $mailSendRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="admin_mail_send_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $mailSend = new MailSend();
        $form = $this->createForm(MailSendType::class, $mailSend);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // $datas = $request->request->get('mail_send');
            // die;
            $mailSend->setFromEmail( "service@lazarefortune.com" );
            $mailSend->setAuthor( $this->getUser() );
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($mailSend);
            $entityManager->flush();

            return $this->redirectToRoute('admin_mail_send_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/mail_send/new.html.twig', [
            'mail_send' => $mailSend,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="admin_mail_send_show", methods={"GET"})
     */
    public function show(MailSend $mailSend): Response
    {
        return $this->render('admin/mail_send/show.html.twig', [
            'mail_send' => $mailSend,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_mail_send_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, MailSend $mailSend): Response
    {
        $form = $this->createForm(MailSendType::class, $mailSend);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_mail_send_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/mail_send/edit.html.twig', [
            'mail_send' => $mailSend,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="admin_mail_send_delete", methods={"POST"})
     */
    public function delete(Request $request, MailSend $mailSend): Response
    {
        if ($this->isCsrfTokenValid('delete'.$mailSend->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($mailSend);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_mail_send_index', [], Response::HTTP_SEE_OTHER);
    }
}
