<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'E-mail'
            ])
            ->add('lastname', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Nom'
            ])
            ->add('firstname', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Prénom'
            ])
            ->add('address', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Adresse'
            ])
            ->add('Company', ChoiceType::class, [
                'choices'  => [
                    'SOFAP' => 'SOFAP' ,
                    'TMS' => 'TMS',
                    'SODIMAC' => 'SODIMAC',
                    'SIPS' => 'SIPS',
                    'SCPC' => 'SCPC',
                    'NCI' => 'NCI',


                    
                ],
                'label' => 'Societé'

            ])
            ->add('site', ChoiceType::class, [
                'choices'  => [
                    'tunis-mnihla' =>  'tunis-mnihla',
                    'tunis-marsa' => 'tunis-marsa',
                    'tunis-bokri' => 'tunis-bokri',
                    'sfax-saltnia' =>  'sfax-saltnia',
                    'sfax-route-gabes' => 'sfax-route-gabes',
                    'gabes' => 'gabes',
                    'tataouine' => 'tataouine',
                    'zarzis'=> 'zarzis',
                    
                ],
            ])
            ->add('department', ChoiceType::class, [
                'choices'  => [
                    'Ressources Humaine' => 'Ressources Humaine',
                    'Informatique' => 'Informatique',
                    'MARKETING' => 'Marketing',
                    'finance' => 'finance',
                    'commercial' => 'commercial',
                ],
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Membre' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                    'administrateur des produits' => 'ROLE_PRODUCT_ADMIN'
                ],
                'multiple' => true,
                'mapped' => false,
                'label' => 'Rôle',
            ])            
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
                'label' => 'Mot de passe'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
