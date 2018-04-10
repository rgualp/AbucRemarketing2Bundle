<?php

namespace MyCp\mycpBundle\Service;

use Doctrine\Common\Persistence\ObjectManager;

class UpdateCoverImageService
{
    /**
     * @var ObjectManager
     */
    private $em;


    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    public function updateCoverAccomodation($coverId, $ownership)
    {
        $ownershipPhoto = $this->em->getRepository('mycpBundle:ownershipPhoto')->getPhotosByIdOwnership($ownership);
        $order = 2;
        $cover = null;
        foreach ($ownershipPhoto as $photo) {
            if ($photo->getOwnPhoPhoto()->getPhoId() == $coverId) {
                $photo->getOwnPhoPhoto()->setFront(true);
                $photo->getOwnPhoPhoto()->setPhoOrder(1);
                $cover = $photo->getOwnPhoPhoto();
            } else {
                $photo->getOwnPhoPhoto()->setPhoOrder($order++);
                $photo->getOwnPhoPhoto()->setFront(false);
            }
        }
        return $ownershipPhoto;

    }
}
