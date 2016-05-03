<?php

namespace hds\SeoBundle\Extension;

use Symfony\Component\HttpFoundation\Request;

class SeoUtilsExtension extends \Twig_Extension
{
	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * @var \hds\SeoBundle\Repository\Block
	 */
	protected $block_repository;

	/**
	 * @var \hds\SeoBundle\Repository\BlockContent
	 */
	protected $blockcontent_repository;

	/**
	 * @var Request
	 */
	protected $request;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct($container, $block_repository, $blockcontent_repository)
	{
		$this->container = $container;
		$this->block_repository = $block_repository;
		$this->blockcontent_repository= $blockcontent_repository;

		if ($this->container->isScopeActive('request')) {
			$this->request = $this->container->get('request');
		}
	}

	public function getName()
	{
		return 'SeoUtils';
	}

	/**
	 * Returns a list of functions to add to the existing list.
	 *
	 * @return array An array of functions
	 */
	public function getFunctions()
	{
		return array(
			new \Twig_SimpleFunction('get_metas', array($this, 'getMetas')),
		);
	}

	public function getMetas($block_name, $language_code, array $replacements=array()){
		$block= $this->block_repository->findOneBy(array('name'=>$block_name));
		if(!$block){
			return '<!--- Seo: Bloque "'.$block_name.'" no existe!!! --->';
		}
		if(!$block->getIsActive()){
			return '<!---Seo: Bloque "'.$block_name.'" esta desactivado!!! --->';
		}
		$block_contents= $this->blockcontent_repository->findBy(array(
			'block'=>$block->getId(),
			'language_code'=>$language_code
		));
		if(!count($block_contents)){
			return '<!---Seo: Bloque "'.$block_name.'" con el idioma "'.$language_code.'" no tiene headers asociados!!! --->';
		}


		$headers= array();
		$metas= '';
		foreach($block_contents as $block_content){
			$header= $block_content->getHeader();
			$headers[]= $header;
			$metas.= $block_content->getMeta($replacements);
		}
		return $metas;
	}

}