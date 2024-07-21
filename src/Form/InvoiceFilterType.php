<?php
namespace App\Form;

use App\Data\shipFilterData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceFilterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tel', TelType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Telephone:'
                ]
            ])
            ->add('dateFrom', TextType::class, [
                "label" => false,
                "required" => false,
                "attr" => [
                    "placeholder" => "Date de debut",
                    "datepicker-format" => "yyy-MM-dd"
                ]
            ])
            ->add('dateTo', TextType::class, [
                "label" => false,
                "required" => false,
                "attr" => [
                    "placeholder" => "Date de fin",
                    "datepicker-format" => "yyy-MM-dd"
                ]
            ])
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => shipFilterData::class,
            'method' => 'GET',
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}