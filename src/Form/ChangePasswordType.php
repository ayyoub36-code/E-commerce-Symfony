<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('firstname', TextType::class, [
                'disabled' => true,
                'label' => 'Prénom'
            ])
            ->add('lastname', TextType::class, ['disabled' => true, 'label' => 'Nom'])
            ->add('email', EmailType::class, ['disabled' => true, 'label' => 'Email'])
            ->add('old_password', PasswordType::class, [
                'mapped' => false,
                'label' => 'Votre mot de passe actuel',
                'attr' => ['placeholder' => 'Veuillez saisir votre mot de passe actuel ']
            ])
            ->add('new_password', RepeatedType::class, [
                'mapped' => false,
                'type' => PasswordType::class,
                'invalid_message' => 'le mot de passe et la confirmation doivent être identiques !',
                'label' => 'Votre nouveau mot de passe',
                'required' => true,
                'first_options' => [
                    'label' => 'Votre nouveau mot de passe',
                    'attr' => ['placeholder' => 'Merci de saisir votre nouveau mot de passe ']
                ],
                'second_options' => [
                    'label' => 'Votre confirmation ',
                    'attr' => ['placeholder' => 'Merci de confirmez votre nouveau mot de passe ']
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Mettre à jour '
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
