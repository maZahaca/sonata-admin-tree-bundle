<?php

namespace RedCode\TreeBundle\Controller;

use Doctrine\ORM\EntityManager;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TreeAdminController extends CRUDController
{
    public function listAction()
    {
        $request = $this->getRequest();
        if ($listMode = $request->get('_list_mode')) {
            $this->admin->setListMode($listMode);
        }
        $listMode = $this->admin->getListMode();

        if ($listMode === 'tree') {
            $this->admin->checkAccess('list');

            $preResponse = $this->preList($request);
            if ($preResponse !== null) {
                return $preResponse;
            }

            return $this->render(
                'RedCodeTreeBundle:CRUD:tree.html.twig',
                [
                    'action' => 'list',
                    'csrf_token' => $this->getCsrfToken('sonata.batch'),
                    '_sonata_admin' => $request->get('_sonata_admin'),
                ],
                null,
                $request
            );
        }

        return parent::listAction();
    }

    public function treeDataAction()
    {
        $request = $this->getRequest();
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var NestedTreeRepository $repo */
        $repo = $em->getRepository($this->admin->getClass());

        $operation = $request->get('operation');
        switch ($operation) {
            case 'get_node':
                $nodeId = $request->get('id');
                if ($nodeId) {
                    $parentNode = $repo->find($nodeId);
                    $nodes = $repo->getChildren($parentNode, true);
                } else {
                    $nodes = $repo->getRootNodes();
                }

                $nodes = array_map(
                    function ($node) {
                        return [
                            'id' => $node->getId(),
                            'text' => (string) $node,
                            'children' => true,
                        ];
                    },
                    $nodes
                );

                return new JsonResponse($nodes);
            case 'move_node':
                $nodeId = $request->get('id');
                $parentNodeId = $request->get('parent_id');

                $parentNode = $repo->find($parentNodeId);
                $node = $repo->find($nodeId);
                $node->setParent($parentNode);

                $this->admin->getModelManager()->update($node);

                $siblings = $repo->getChildren($parentNode, true);
                $position = $request->get('position');
                $i = 0;

                foreach ($siblings as $sibling) {
                    if ($sibling->getId() === $node->getId()) {
                        break;
                    }

                    $i++;
                }

                $diff = $position - $i;

                if ($diff > 0) {
                    $repo->moveDown($node, $diff);
                } else {
                    $repo->moveUp($node, abs($diff));
                }

                return new JsonResponse(
                    [
                        'id' => $node->getId(),
                        'text' => $node->{'get'.ucfirst($this->admin->getTreeTextField())}(),
                    ]
                );
            case 'rename_node':
                $nodeId = $request->get('id');
                $nodeText = $request->get('text');
                $node = $repo->find($nodeId);

                $node->{'set'.ucfirst($this->admin->getTreeTextField())}($nodeText);
                $this->admin->getModelManager()->update($node);

                return new JsonResponse(
                    [
                        'id' => $node->getId(),
                        'text' => $node->{'get'.ucfirst($this->admin->getTreeTextField())}(),
                    ]
                );
            case 'create_node':
                $parentNodeId = $request->get('parent_id');
                $parentNode = $repo->find($parentNodeId);
                $nodeText = $request->get('text');
                $node = $this->admin->getNewInstance();
                $node->{'set'.ucfirst($this->admin->getTreeTextField())}($nodeText);
                $node->setParent($parentNode);
                $this->admin->getModelManager()->create($node);

                return new JsonResponse(
                    [
                        'id' => $node->getId(),
                        'text' => $node->{'get'.ucfirst($this->admin->getTreeTextField())}(),
                    ]
                );
            case 'delete_node':
                $nodeId = $request->get('id');
                $node = $repo->find($nodeId);
                $this->admin->getModelManager()->delete($node);

                return new JsonResponse();
        }

        throw new BadRequestHttpException('Unknown action for tree');
    }
}
