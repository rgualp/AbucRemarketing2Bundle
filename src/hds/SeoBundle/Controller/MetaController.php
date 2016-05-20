<?php

namespace hds\SeoBundle\Controller;

use hds\SeoBundle\Entity\Block;
use hds\SeoBundle\Entity\BlockContent;
use hds\SeoBundle\Entity\Header;
use hds\SeoBundle\Entity\HeaderBlock;
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
 * @Route("meta")
 */
class MetaController extends Controller
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
	 * @DI\Inject("header_formtype")
	 *
	 * @var \hds\SeoBundle\Form\HeaderType
	 */
	private $header_formtype;

	/**
	 * @DI\Inject("headerblock_formtype")
	 *
	 * @var \hds\SeoBundle\Form\HeaderBlockType
	 */
	private $headerblock_formtype;

	/**
	 * @Route("/list", name="hdsseo_meta_list")
	 */
	function listPrivilegesAction(Request $request){
		$entities= $this->header_repository->findAll();
		$entities= $this->header_repository->findBy(array(), array('header_block'=>'ASC'));

		$page= $request->get('page')?$request->get('page'):1;
		$items_per_page= $request->get('items_per_page')?$request->get('items_per_page'):20;
		$paginator = $this->get('ideup.simple_paginator');
		$paginator->setItemsPerPage($items_per_page);
		$metas = $paginator->paginate($entities)->getResult();

		return $this->render('SeoBundle:Meta:list.html.twig', array(
			'metas' => $metas,
			'items_per_page' => $items_per_page,
			'current_page' => $page,
			'total_items' => $paginator->getTotalItems()
		));
	}

	/**
	 * @param Request $request
	 * @return Response
	 * @Route("/edit", name="hdsseo_meta_edit")
	 * @Route("/new", name="hdsseo_meta_new")
	 * @Method({"POST", "GET"})
	 */
	public function processAction(Request $request)
	{
		$data= array();
		$id= $request->get('id');
		if($id){
			$obj= $this->header_repository->find($id);
		}else{

			#region Redirect if edit and not contain id
			$route = $request->get('_route');
			if($route=='hdsseo_meta_edit'){
				return $this->redirectToRoute('hdsseo_meta_new');
			}
			#endregion

			$obj= new Header();
		}
		$form = $this->createForm($this->header_formtype, $obj);

		#region Save
		$formtype= $request->get($this->header_formtype->getName());
		if($formtype){
			$form->submit($request);
			if ($form->isValid()) {
				$this->em->persist($obj);
				$this->em->flush();
				$this->get('session')->getFlashBag()->add('message_success', 'Meta guardado satisfactoriamente.');
				return $this->redirectToRoute('hdsseo_meta_list');
			}
			else{
				dump($form->getErrors(true));exit;
			}
		}
		#endregion

		$data['obj']= $obj;
		$data['form']= $form->createView();
		return $this->render('SeoBundle:Meta:process.html.twig', $data);
	}

	/**
	 * @param Request $request
	 * @return Response
	 * @Route("/delete", name="hdsseo_meta_delete")
	 * @Method({"POST"})
	 */
	public function removeAction(Request $request)
	{
		$id= $request->get('id');
		if($id){
			$obj= $this->header_repository->find($id);
			$this->em->remove($obj);
			$this->em->flush();
			$this->get('session')->getFlashBag()->add('message_success', 'Meta eliminada satisfactoriamente.');
		}else{
			$this->get('session')->getFlashBag()->add('message_warning', 'La Meta que intenta eliminar no existe o ya fue eliminado.');
		}
		return $this->redirectToRoute('hdsseo_meta_list');
	}

	/**
	 * @Route("/tab/list", name="hdsseo_tab_list")
	 */
	function tabListPrivilegesAction(Request $request){
		$entities= $this->headerblock_repository->findBy(array(), array('name'=>'ASC'));

		$page= $request->get('page')?$request->get('page'):1;
		$items_per_page= $request->get('items_per_page')?$request->get('items_per_page'):20;
		$paginator = $this->get('ideup.simple_paginator');
		$paginator->setItemsPerPage($items_per_page);
		$objs = $paginator->paginate($entities)->getResult();

		return $this->render('SeoBundle:Meta:tabList.html.twig', array(
			'tabs' => $objs,
			'items_per_page' => $items_per_page,
			'current_page' => $page,
			'total_items' => $paginator->getTotalItems()
		));
	}

	/**
	 * @param Request $request
	 * @return Response
	 * @Route("/tab/edit", name="hdsseo_tab_edit")
	 * @Route("/tab/new", name="hdsseo_tab_new")
	 * @Method({"POST", "GET"})
	 */
	public function tabProcessAction(Request $request)
	{
		$data= array();
		$id= $request->get('id');
		if($id){
			$obj= $this->headerblock_repository->find($id);
		}else{

			#region Redirect if edit and not contain id
			$route = $request->get('_route');
			if($route=='hdsseo_tab_edit'){
				return $this->redirectToRoute('hdsseo_tab_new');
			}
			#endregion

			$obj= new HeaderBlock();
		}
		$form = $this->createForm($this->headerblock_formtype, $obj);

		#region Save
		$formtype= $request->get($this->headerblock_formtype->getName());
		if($formtype){
			$form->submit($request);
			if ($form->isValid()) {
				$this->em->persist($obj);
				$this->em->flush();
				$this->get('session')->getFlashBag()->add('message_success', 'Tab guardado satisfactoriamente.');
				return $this->redirectToRoute('hdsseo_tab_list');
			}
			else{
				dump($form->getErrors(true));exit;
			}
		}
		#endregion

		$data['obj']= $obj;
		$data['form']= $form->createView();
		return $this->render('SeoBundle:Meta:tabProcess.html.twig', $data);
	}

	/**
	 * @param Request $request
	 * @return Response
	 * @Route("/tab/delete", name="hdsseo_tab_delete")
	 * @Method({"POST"})
	 */
	public function tabRemoveAction(Request $request)
	{
		$id= $request->get('id');
		if($id){
			$obj= $this->headerblock_repository->find($id);
			$this->em->remove($obj);
			$this->em->flush();
			$this->get('session')->getFlashBag()->add('message_success', 'Tab eliminado satisfactoriamente.');
		}else{
			$this->get('session')->getFlashBag()->add('message_warning', 'El tab que intenta eliminar no existe o ya fue eliminado.');
		}
		return $this->redirectToRoute('hdsseo_tab_list');
	}

}