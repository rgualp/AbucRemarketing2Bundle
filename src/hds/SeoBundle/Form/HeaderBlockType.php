<?php

namespace hds\SeoBundle\Form;

use hds\SeoBundle\Entity\Header;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HeaderBlockType extends AbstractType
{
	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('name', null, array(
			'required'=>true
		));
		$builder->add('decription');
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'hds\SeoBundle\Entity\HeaderBlock'
		));
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'headerblock_formtype';
	}
}