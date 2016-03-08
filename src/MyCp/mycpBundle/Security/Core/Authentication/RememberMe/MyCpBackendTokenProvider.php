<?php
/**
 * Created by PhpStorm.
 * User: neti
 * Date: 06/03/2016
 * Time: 12:47
 */

namespace MyCp\mycpBundle\Security\Core\Authentication\RememberMe;


use Symfony\Component\Security\Core\Authentication\RememberMe\InMemoryTokenProvider;
use Symfony\Component\Security\Core\Authentication\RememberMe\PersistentToken;
use Symfony\Component\Security\Csrf\Exception\TokenNotFoundException;

class MyCpBackendTokenProvider extends  InMemoryTokenProvider
{

    private $tokens = array();
    /**
     * {@inheritdoc}
     */
    public function updateToken($series, $tokenValue, \DateTime $lastUsed)
    {   die(dump($series));
        if (!isset($this->tokens[$series])) {
            throw new TokenNotFoundException('No token found.');
        }

        $token = new PersistentToken(
            $this->tokens[$series]->getClass(),
            $this->tokens[$series]->getName(),
            $series,
            $tokenValue,
            $lastUsed
        );
        $this->tokens[$series] = $token;
        die(dump($this->tokens));
    }
}