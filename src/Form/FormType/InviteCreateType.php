<?php

namespace Bolt\Form\FormType;

use Bolt\AccessControl\Token\Token;
use Bolt\Form\FieldType\UserRoleType;
use Bolt\Security\Random\Generator;
use Bolt\Storage\Entity\Invitation;
use Bolt\Translation\Translator as Trans;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Invitation creation form.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class InviteCreateType extends AbstractType
{
    /** @var SessionInterface */
    protected $session;
    /** @var Generator */
    protected $generator;

    /**
     * Constructor.
     *
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
        $this->generator = new Generator();
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'roles',
                UserRoleType::class,
                [
                    'constraints' => [
                        new Assert\NotBlank(['message' => Trans::__('general.phrase.select.one-role')]),
                    ],
                ]
            )
            ->add(
                'expiration',
                DateTimeType::class,
                [
                    'input'       => 'datetime',
                    'date_widget' => 'single_text',
                    'time_widget' => 'single_text',
                    'required'    => true,
                    'disabled'    => false,
                    'data'        => new \DateTime('+24 hours'),
                    'label'       => Trans::__('page.invitation.expiration-date'),
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Range([
                            'min'        => $options['expiryMin'],
                            'max'        => $options['expiryMax'],
                            'minMessage' => Trans::__('page.invitation.generate.expiry-min', ['%PERIOD%' => $options['expiryMin']]),
                            'maxMessage' => Trans::__('page.invitation.generate.expiry-max', ['%PERIOD%' => $options['expiryMax']]),
                        ]),
                    ],
                ]
            )
            ->add(
                'save',
                SubmitType::class,
                [
                    'label' => Trans::__('page.invitation.button.generate')
                ]
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(['expiryMin', 'expiryMax']);
        $resolver->setDefaults([
            'empty_data' => function () {
                $invite = new Invitation();
                $invite->setToken($this->generator->generateString(32));
                $invite->setOwnerId($this->getUser()->getId());

                return $invite;
            },
            'expiryMin' => '30 minutes',
            'expiryMax' => '48 hours',
        ]);
    }

    protected function getUser()
    {
        $auth = $this->session->get('authentication');
        if (!$auth instanceof Token) {
            throw new \RuntimeException('User should be logged in to create an invitation.');
        }

        return $auth->getUser();
    }
}
