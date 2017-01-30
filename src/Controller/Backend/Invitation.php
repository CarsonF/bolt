<?php

namespace Bolt\Controller\Backend;

use Bolt\Form\FormType\InviteCreateType;
use Bolt\Form\FormType\InviteShareType;
use Bolt\Storage\Entity;
use Bolt\Translation\Translator as Trans;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Backend controller for invitation code generation.
 *
 * @author Carlos Perez <mrcarlosdev@gmail.com>
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Invitation extends BackendBase
{
    /**
     * {@inheritdoc}
     */
    protected function addRoutes(ControllerCollection $c)
    {
        $c->match('/users/invite/{invite}', 'inviteCreate')
          ->bind('inviteCreate')
          ->assert('invite', '\d+')
          ->value('invite', null)
            ->convert('invite', function ($id) {
                if ($id === null) {
                    return null;
                }

                return $this->storage()->getRepository(Entity\Invitation::class)->find($id);
            })
        ;

        $c->post('/users/delete/{id}', 'inviteDelete')
          ->bind('inviteDelete')
          ->assert('id', '\d+')
        ;

        $c->match('/users/invite/share/{code}', 'inviteShare')
            ->assert('code', '.*')
            ->bind('inviteShare');

        $c->before([$this, 'before']);
    }

    /**
     * {@inheritdoc}
     */
    public function before(Request $request, Application $app, $roleRoute = null)
    {
        return parent::before($request, $app, 'useredit:invitation');
    }

    /**
     * Invitation link route.
     *
     * @param Request $request The Symfony Request
     *
     * @return \Bolt\Response\TemplateResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function inviteCreate(Request $request, $invite)
    {
        $form = $this->createForm(InviteCreateType::class, $invite)
                     ->handleRequest($request)
        ;

        if ($form->isValid()) {
            /** @var Entity\Invitation $invite */
            $invite = $form->getData();

            $repo = $this->getRepository(Entity\Invitation::class);

            if ($repo->save($invite)) {
                $this->flashes()->success(Trans::__('page.invitation.message.code-saved', ['%code%' => $invite->getToken()]));
            } else {
                $this->flashes()->error(Trans::__('page.invitation.message.code-failed'));
            }

            return $this->redirectToRoute('inviteShare', ['code' => $invite->getToken()]);
        }

        $context = [
            'form' => $form->createView(),
        ];

        return $this->render('@bolt/invitation/generate.twig', $context);
    }

    /**
     * Share link route.
     *
     * @param Request $request The Symfony Request
     * @param string  $code    The invitation code
     *
     * @return \Bolt\Response\TemplateResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function inviteShare(Request $request, $code)
    {
        // Generate the full URL to put it into the link field
        $linkUrl = $this->generateUrl('invitation', ['code' => $code], UrlGeneratorInterface::ABSOLUTE_URL);
        $entity = (object) ['to' => null, 'subject' => null, 'message' => null, 'link' => $linkUrl];
        $form = $this->createFormBuilder(InviteShareType::class, $entity)
            ->getForm()
            ->handleRequest($request)
        ;

        // Check if the form was POST-ed, and valid. If so, store the invitation.
        if ($form->isValid()) {
            $this->sendInvite($entity);

            return $this->redirectToRoute('users');
        }

        // Preparing the forms for the view
        $context = [
            'form' => $form->createView(),
            'code' => $code,
            'link' => $linkUrl,
        ];

        return $this->render('@bolt/invitation/share.twig', $context);
    }

    /**
     * Send the invitation to the specified address.
     *
     * @param object $entity
     */
    private function sendInvite($entity)
    {
        $logger = $this->app['logger.system'];
        $mailer = $this->app['mailer'];
        $twig = $this->app['twig'];
        $spool = $this->app['swiftmailer.spooltransport']->getSpool();
        $transport = $this->app['swiftmailer.transport'];
        $from  = $this->getOption('general/mailoptions/senderMail', $this->getUser()->getEmail());

        // Compile the email with the invitation link.
        $mailHtml = $twig->render('@bolt/mail/invitation.twig', [
            'message' => $entity->message,
            'link'    => $entity->link,
        ]);

        /** @var \Swift_Message $message */
        $message = $mailer->createMessage('message');
        $message
            ->setSubject($entity->subject)
            ->setFrom($from)
            ->setReplyTo($from)
            ->setTo($entity->to)
            ->setBody(strip_tags($mailHtml))
        ;
        $message->addPart($mailHtml, 'text/html');

        $failed = true;
        $failedRecipients = [];

        try {
            // Try and send immediately
            $recipients = $mailer->send($message, $failedRecipients);
            $spool->flushQueue($transport);
            if ($recipients) {
                $logger->info(sprintf('Invitation request sent to %s .', $entity->to), ['event' => 'authentication']);
                $failed = false;
            }
        } catch (\Exception $e) {
            // Notify below
        }

        if ($failed) {
            $logger->error(sprintf('Failed to send invitation request sent to %s', $entity->to), ['event' => 'authentication']);
            $this->flashes()->error(Trans::__('page.invitation.share.email-error'));
        } else {
            $this->flashes()->success(Trans::__('page.invitation.share.email-sent', ['%email%' => $entity->to]));
        }
    }
}
