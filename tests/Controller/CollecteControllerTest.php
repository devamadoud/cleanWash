<?php

namespace App\Tests\Controller;

use App\Entity\Collecte;
use App\Entity\Customer;
use App\Entity\User;
use App\Entity\Shop;
use App\Controller\CollecteController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\SecurityBundle\Security as SecurityBundleSecurity;

class CollecteControllerTest extends WebTestCase
{
    public function testCollecteIndex()
    {
        $client = static::createClient();
        $client->request('GET', '/collectes');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}

