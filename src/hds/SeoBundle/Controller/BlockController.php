<?php

namespace hds\SeoBundle\Controller;

use hds\SeoBundle\Entity\Block;
use hds\SeoBundle\Entity\BlockContent;
use hds\SeoBundle\Entity\Header;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("block")
 */
class BlockController extends Controller
{
	/**
	 * @DI\Inject("doctrine.orm.entity_manager")
	 *
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @DI\Inject("block_repository")
	 *
	 * @var \hds\SeoBundle\Repository\Block
	 */
	private $block_repository;

	/**
	 * @DI\Inject("header_repository")
	 *
	 * @var \hds\SeoBundle\Repository\Header
	 */
	private $header_repository;

	/**
	 * @DI\Inject("headerblock_repository")
	 *
	 * @var \hds\SeoBundle\Repository\HeaderBlock
	 */
	private $headerblock_repository;

	/**
	 * @DI\Inject("blockcontent_repository")
	 *
	 * @var \hds\SeoBundle\Repository\BlockContent
	 */
	private $blockcontent_repository;

	/**
	 * @DI\Inject("block_formtype")
	 *
	 * @var \hds\SeoBundle\Form\BlockType
	 */
	private $block_formtype;

	/**
	 * @Route("/list", name="hdsseo_block_list")
	 */
	function listPrivilegesAction(Request $request){

		$entities= $this->block_repository->findAll();
		$languages= $this->em->getRepository('mycpBundle:lang')->findBy(array('lang_active'=>1));

		$page= $request->get('page')?$request->get('page'):1;
		$items_per_page= $request->get('items_per_page')?$request->get('items_per_page'):20;
		$paginator = $this->get('ideup.simple_paginator');
		$paginator->setItemsPerPage($items_per_page);
		$blocks = $paginator->paginate($entities)->getResult();

		return $this->render('SeoBundle:Block:list.html.twig', array(
			'blocks' => $blocks,
			'items_per_page' => $items_per_page,
			'current_page' => $page,
			'languages' => $languages,
			'total_items' => $paginator->getTotalItems()
		));
	}

	/**
	 * @param Request $request
	 * @return Response
	 * @Route("/edit", name="hdsseo_block_edit")
	 * @Route("/new", name="hdsseo_block_new")
	 * @Method({"POST", "GET"})
	 */
	public function processAction(Request $request)
	{
		$data= array();
		$id= $request->get('id');
		$locale= $request->get('locale', 'en');
		if($id){
			$obj= $this->block_repository->find($id);
		}else{

			#region Redirect if edit and not contain id
			$route = $request->get('_route');
			if($route=='hdsseo_block_edit'){
				return $this->redirectToRoute('hdsseo_block_new');
			}
			#endregion

			$obj= new Block();
		}
		$form = $this->createForm($this->block_formtype, $obj);

		#region Save
		$formtype= $request->get('block_formtype');
		if($formtype){
			$form->submit($request);
			if ($form->isValid()) {
				$this->em->persist($obj);

				$contents= $request->get('content');
				$block_content_count= 0;
				foreach($contents as $id_header=>$content_str){
					$content_str= trim($content_str);

					$block_content= $this->blockcontent_repository->findOneBy(array(
						'block'=>$obj->getId(),
						'header'=>$id_header,
						'language_code'=>$locale
					));
					if(!$block_content){
						$block_content= new BlockContent();
					}elseif($content_str==''){
						$this->em->remove($block_content);
						continue;
					}

					$header= $this->header_repository->find($id_header);
					if($content_str!= ''){
						$block_content->setBlock($obj);
						$block_content->setHeader($header);
						$block_content->setLanguageCode($locale);
						$block_content->setContent($content_str);
						$this->em->persist($block_content);
						$block_content_count++;
					}
				}
				$this->em->flush();
				if($block_content_count==0){
					$this->get('session')->getFlashBag()->add('message_warning', 'El bloque se guardó satisfactoriamente pero no contine ningún header asociado.');
				}else{
					$this->get('session')->getFlashBag()->add('message_success', 'El bloque se guardó satisfactoriamente.');
				}
				return $this->redirectToRoute('hdsseo_block_list');
			}
			else{
				dump($form->getErrors(true));exit;
			}
		}
		#endregion

		$header_blocks= $this->headerblock_repository->findAll();
		$contents= $obj->getContents();
		$tmp_header_blocks= array();

		foreach($header_blocks as $header_block ){
			$header_block_name= $header_block->getName();
			$tmp_header_blocks[$header_block_name]= '';

			$headers= $header_block->getHeaders();
			foreach($headers as $header ){

				$content= $this->blockcontent_repository->findOneBy(array(
					'block'=>$obj->getId(),
					'header'=>$header->getId(),
					'language_code'=>$locale
				));
				if(!$content){
					$content= new BlockContent();
				}

				$tmp_header_blocks[$header_block_name][]= array(
					'header'=>$header,
					'content'=>$content
				);
			}
		}

		$languages= $this->em->getRepository('mycpBundle:lang')->findBy(array('lang_active'=>1));

		$data['obj']= $obj;
		$data['block']= $obj;
		$data['form_header']= $form->createView();
		$data['header_blocks']= $tmp_header_blocks;
		$data['locale']= $locale;
		$data['languages']= $languages;
		return $this->render('SeoBundle:Block:process.html.twig', $data);
	}

	/**
	 * @param Request $request
	 * @return Response
	 * @Route("/delete", name="hdsseo_block_delete")
	 * @Method({"POST"})
	 */
	public function removeAction(Request $request)
	{
		$id= $request->get('id');
		if($id){
			$obj= $this->block_repository->find($id);
			$this->em->remove($obj);
			$this->em->flush();
			$this->get('session')->getFlashBag()->add('message_success', 'El bloque fue eliminado satisfactoriamente.');
		}else{
			$this->get('session')->getFlashBag()->add('message_warning', 'El bloque que intenta eliminar no existe o ya fue eliminado.');
		}
		return $this->redirectToRoute('hdsseo_block_list');
	}

}