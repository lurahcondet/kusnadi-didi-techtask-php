<?php

/**
 * @author Didi Kusnadi <jalapro08@gmail.com>
 */

namespace App\Test\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CookingControllerTest extends WebTestCase
{
    /**
     * test index method
     *
     * @return void
     */
    public function testIndex()
    {
        $client = static::createClient();

        $client->request('GET', '/lunch/2019-01-01');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
