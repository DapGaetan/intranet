<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Ldap\LdapInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class LdapTestController extends AbstractController
{
    private $ldap;
    private $params;

    public function __construct(LdapInterface $ldap, ParameterBagInterface $params)
    {
        $this->ldap = $ldap;
        $this->params = $params;
    }

    #[Route('/test-ldap', name: 'test_ldap')]
    public function testLdap(): Response
    {
        $bindDn = $this->getParameter('ldap_bind_dn');
        $bindPassword = $this->getParameter('ldap_bind_password');
    
        try {
            $this->ldap->bind($bindDn, $bindPassword);
    
            $query = $this->ldap->query('dc=osartis,dc=local', '(objectClass=*)');
            $result = $query->execute();
    
            return new Response('Connexion réussie à l\'annuaire LDAP. Entrées trouvées : ' . count($result));
        } catch (\Exception $e) {
            return new Response('Échec de la connexion à l\'annuaire LDAP: ' . $e->getMessage());
        }
    }
}
