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
			new \Twig_SimpleFunction('get_lang', array($this, 'getLang')),
		);
	}

	public function getMetas($block_name, $language_code, array $replacements=array()){

		$metas= '';
		try{
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


//			$headers= array();
			foreach($block_contents as $block_content){
//				$header= $block_content->getHeader();
//				$headers[]= $header;
				$metas.= $block_content->getMeta($replacements);
			}

		}catch(\Exception $ee){
			$content= $ee->getFile().':'.$ee->getLine().':'.$ee->getMessage();
			$metas.= '<!---Seo: Ocurrio un ERROR!!! --->';
			$metas.= '<!---'.$content.' --->';
		}
		return $metas;
	}

	public function getLang($language_code){

		$lang= '';
		try{
			switch ($language_code) {
				case 'en':
					$lang.= 'lang="en"';
					break;
				case 'es':
					$lang.= 'lang="es-419"';
					break;
				case 'de':
					$lang.= 'lang="de"';
					break;
                case 'fr':
                    $lang.= 'lang="fr"';
                    break;
                case 'it':
                    $lang.= 'lang="it"';
                    break;
				default:
					$lang.= 'lang="en" <!---Seo: "'.$language_code.'" no esta definido... --->';
			}

		}catch(\Exception $ee){
			$content= $ee->getFile().':'.$ee->getLine().':'.$ee->getMessage();
			$lang.= '<!---Seo: Ocurrio un ERROR!!! --->';
			$lang.= '<!---'.$content.' --->';
		}
		return $lang;
	}

}