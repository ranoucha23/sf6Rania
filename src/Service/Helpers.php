<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Security\Core\Security;

class Helpers
{
    public function __construct(private Security $security) {
    }

    public function getUser(): User {
        if($this->security->isGranted('ROLE_ADMIN')) {
            $user = $this->security->getUser();
            if ($user instanceof User) {
                return $user;
            }
        }
    }
}