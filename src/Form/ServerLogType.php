<?php

namespace App\Form;

use App\Entity\ServerLog;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServerLogType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('server')
            ->add('status')
            ->add('date', DateTimeType::class, ['widget' => 'single_text', 'property_path' => 'dateLog'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ServerLog::class,
            'csrf_protection' => false
        ]);
    }
}
