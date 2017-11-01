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
     * @var \hds\SeoBundle\Repository\Header
     */
    protected $header_repository;

	/**
	 * @var Request
	 */
	protected $request;

    /**
     * @var \MyCp\mycpBundle\Entity\langRepository
     */
	protected $languaje_repository;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct($container, $block_repository, $blockcontent_repository, $header_repository, $languaje_repository)
	{
		$this->container = $container;
		$this->block_repository = $block_repository;
		$this->blockcontent_repository = $blockcontent_repository;
		$this->header_repository = $header_repository;
		$this->languaje_repository = $languaje_repository;

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
			new \Twig_SimpleFunction('get_tagvalue_bytag', array($this, 'getTagValueByTag')),
		);
	}

	public function getTagValueByTag($block_name, $language_code, $type_tag){
        $block= $this->block_repository->findOneBy(array('name'=>$block_name));

        if(!$block){
            return false;
        }
        if(!$block->getIsActive()){
            return false;
        }

        $header = $this->header_repository->findOneBy(array('type_tag'=>$type_tag));
        if(!$header){
            return false;
        }
        $block_contents= $this->blockcontent_repository->findOneBy(array(
            'block'=>$block->getId(),
            'header'=>$header->getId(),
            'language_code'=>$language_code
        ));
        if(!$block_contents){
            return false;
        }

        return $block_contents->getContent();
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

			foreach($block_contents as $block_content){
			    $aux = $block_content->getMeta($replacements);
				if ( strpos($aux,'alternate') == false && strpos($aux,'canonical') == false)
				    $metas.= $block_content->getMeta($replacements);
			}

            //Creating Alternate and Canonical links

            $allLanguage = $this->languaje_repository->findBy(array('lang_active' => 1));

            $rel = "";
            $href = "";
            $hreflang = '';

            $attributes = $this->container->get('request')->attributes->all();
            $paramas = $attributes['_route_params'];
            $paramas['locale'] = "es";

            $langCountry = array(
            	'en' => 'en-US',
            	'es' => 'es-ES',
            	'de' => 'de-DE',
            	'it' => 'it-IT',
            	'fr' => 'fr-FR'
            );	

            if (count($allLanguage) > 0){
                foreach ($allLanguage as $lang){
                    $paramas['locale'] = strtolower($lang->getLangCode());

                    if ($attributes['_route'] == "frontend_search_ownership"){
                        $aux_params = array('locale' => $paramas['locale']);
                        if ($paramas['text'] != null){
                            $aux_params = array('locale' => $paramas['locale'],'text' => $paramas['text']);
                        }
                        $url = $this->container->get('router')->generate($attributes['_route'],$aux_params, true);
                    }else{
                        $url = $this->container->get('router')->generate($attributes['_route'],$paramas, true);
                    }

                    $new_url = str_replace("/".$language_code."/", "/".strtolower($lang->getLangCode())."/", $url);
                    $hreflang = 'hreflang=';
                    $rel = "alternate";

                    $hreflang = $hreflang.'"'.$langCountry[strtolower($lang->getLangCode())].'"';

                    if (strtolower($lang->getLangCode()) == $language_code){
                        $rel = "canonical";
                        $hreflang = "";
                    }

                    $metas .= '<link rel="'.$rel.'" href="'.$new_url.'" '. $hreflang .' >';
                }
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