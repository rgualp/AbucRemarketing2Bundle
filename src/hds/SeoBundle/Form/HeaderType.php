<?php

namespace hds\SeoBundle\Form;

use hds\SeoBundle\Entity\Header;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HeaderType extends AbstractType
{
	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('header_block', null, array(
			'required'=>true
		));
		$builder->add('type_tag', 'choice', array(
			'choices' => Header::getAllTypeTag(),
		));
		$builder->add('tag');
//		$builder->add('content');
		$builder->add('decription');
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'hds\SeoBundle\Entity\Header'
		));
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'header_formtype';
	}
}