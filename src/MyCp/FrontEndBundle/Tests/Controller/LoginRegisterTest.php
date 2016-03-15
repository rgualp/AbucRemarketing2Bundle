<?php

namespace MyCp\FrontEndBundle\Tests\Controller;

use Symfony\Component\BrowserKit\Cookie;

class LoginRegisterTest extends TestBaseMyCp
{
    private $cant_users= 1;//Configure

    private $url_register= '/es/registrar-usuario/';//Configure

    private $url_login= '/es/login/';//Configure

    private $const= 'abcdefg';//Configure

    /**
     *  @var array
     */
    private $users = null;

    public function setUp()
    {
        parent::setUp();
        $this->users= array();
    }

    public function testAll(){
        $this->deleteUsersTest();
        $this->createUsersTest();
        $count_bad_register= $this->register_Test();
        $count_bad_login_no_active= $this->loginNoActiveUsers_Test();
        $this->activateUsersTest();
        $count_bad_login= $this->login_Test();
        $this->deleteUsersTest();

        $this->assertTrue($count_bad_register==0);
        $this->assertTrue($count_bad_login_no_active==0);
        $this->assertTrue($count_bad_login==0);
    }

    public function register_Test(){
        $btn_register = 'btn_submit';//Configure
        $count_bad= 0;

        for($i=0; $i< $this->cant_users;$i++){

            $user= $this->users[$i];

            $cookie = new Cookie('mycp_user_session', $user['id']);
            $this->client->getCookieJar()->set($cookie);

            $crawler = $this->client->request('GET', $this->url_register);
            $form = $crawler->selectButton($btn_register)->form();

            $params= array();
            $params['mycp_frontendbundle_register_usertype[user_user_name]'] = $user['username'];
            $params['mycp_frontendbundle_register_usertype[user_last_name]'] = $user['lastname'];
            $params['mycp_frontendbundle_register_usertype[user_email]'] = $user['email'];
            $params['mycp_frontendbundle_register_usertype[user_password][Clave]'] = $user['password'];
            $params['mycp_frontendbundle_register_usertype[user_password][Repetir]'] = $user['password'];
            $params['mycp_frontendbundle_register_usertype[user_country]'] = $user['country'];

            $this->client->submit($form,$params);
            $crawler= $this->client->followRedirect();
            $created_account= $crawler->filter('html:contains("Gracias por registrarse. Se ha enviado un email para que active su cuenta")')->count();//Configure

            if($created_account!=1){
				$count_bad++;
//                echo '***Register***';
//                dump($response->getContent());
            }

        }
        return $count_bad;
    }

    public function loginNoActiveUsers_Test(){
        $btn_register = 'Identificarse';//Configure
        $count_bad= 0;

        for($i=0; $i< $this->cant_users;$i++){
            $user= $this->users[$i];

            $cookie = new Cookie('mycp_user_session', $user['id']);
            $this->client->getCookieJar()->set($cookie);

            $crawler = $this->client->request('GET', $this->url_login);
            $response= $this->client->getResponse();
            $statusCode= $response->getStatusCode();
            while($statusCode==302){
                $crawler= $this->client->followRedirect();
                $response= $this->client->getResponse();
                $statusCode= $response->getStatusCode();
            }
            $form = $crawler->selectButton($btn_register)->form();

            $this->client->submit($form,
                array('_username' => $user['email'],
                    '_password' => $user['password']));

            $response= $this->client->getResponse();
            $statusCode= $response->getStatusCode();
            while($statusCode==302){
                $crawler= $this->client->followRedirect();
                $response= $this->client->getResponse();
                $statusCode= $response->getStatusCode();
            }

//            $login= $crawler->filter('html:contains("Usuario no activo")')->count();
            $login= $crawler->filter('html:contains("User account is disabled.")')->count();

            if($login!=1){
                $count_bad++;
//                echo '***Login sin confirmar***';
//                dump($response->getContent());
            }

        }
        return $count_bad;
    }

    public function login_Test(){
        $btn_register = 'Identificarse';//Configure
        $count_bad= 0;

        for($i=0; $i< $this->cant_users;$i++){
            $user= $this->users[$i];

            $cookie = new Cookie('mycp_user_session', $user['id']);
            $this->client->getCookieJar()->set($cookie);

            $crawler = $this->client->request('GET', $this->url_login);
            $response= $this->client->getResponse();
            $statusCode= $response->getStatusCode();
            while($statusCode==302){
                $crawler= $this->client->followRedirect();
                $response= $this->client->getResponse();
                $statusCode= $response->getStatusCode();
            }
            $form = $crawler->selectButton($btn_register)->form();

            $this->client->submit($form,
                array('_username' => $user['email'],
                    '_password' => $user['password']));

            $response= $this->client->getResponse();
            $statusCode= $response->getStatusCode();
            while($statusCode==302){
                $crawler= $this->client->followRedirect();
                $response= $this->client->getResponse();
                $statusCode= $response->getStatusCode();
            }

            $login= $crawler->filter('html:contains("Mi perfil")')->count();

            if($login!=1){
                $count_bad++;
//                echo '***Login***';
//                dump($response->getContent());
            }

        }
        return $count_bad;
    }

    #region Utils
    public function createUsersTest(){

        for($i=1; $i<=$this->cant_users;$i++){
            $user= array();

            $user['id']= $i;
            $user['username']= $this->const.$i;
            $user['lastname']= $this->const.$i;
            $user['email']= $this->const.$i.'@functional.test.com';
            $user['password']= md5($this->const.$i);
            $user['country']= 52;

            $this->users[]= $user;
        }
    }

    public function activateUsersTest(){
        $users = $this->em->getRepository("mycpBundle:user")->createQueryBuilder('u')
            ->update()
            ->set('u.user_enabled', ':ENABLED')
            ->where('u.user_email LIKE :EMAIL')
            ->setParameter('ENABLED', 1)
            ->setParameter('EMAIL', '%@functional.test.com')
            ->getQuery()
            ->execute();
    }

    public function deactivateUsersTest(){
        $users = $this->em->getRepository("mycpBundle:user")->createQueryBuilder('u')
            ->update()
            ->set('u.user_enabled', ':ENABLED')
            ->where('u.user_email LIKE :EMAIL')
            ->setParameter('ENABLED', 0)
            ->setParameter('EMAIL', '%@functional.test.com')
            ->getQuery()
            ->execute();
    }

    public function deleteUsersTest(){
        $users = $this->em->getRepository("mycpBundle:user")->createQueryBuilder('u')
            ->where('u.user_email LIKE :EMAIL')
            ->setParameter('EMAIL', '%@functional.test.com')
            ->getQuery()
            ->getResult();
        foreach($users as $user){
            $user_tourist = $this->em->getRepository("mycpBundle:userTourist")->findOneBy(array('user_tourist_user'=>$user->getUserId()));
            $users_log = $this->em->getRepository("mycpBundle:log")->findBy(array('log_user'=>$user->getUserId()));
            $users_comment = $this->em->getRepository("mycpBundle:comment")->findBy(array('com_user'=>$user->getUserId()));

            foreach($users_log as $user_log){
                $this->em->remove($user_log);
            }
			foreach($users_comment as $user_comment){
				$this->em->remove($user_comment);
			}
            if($user_tourist)
                $this->em->remove($user_tourist);
            if($user)
                $this->em->remove($user);
        }
        $this->em->flush();
    }
    #endregion
}
