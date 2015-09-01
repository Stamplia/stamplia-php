<?php
/**
 * Created by PhpStorm.
 * User: christophebenoit1
 * Date: 01/09/2015
 * Time: 11:38
 */

namespace Stamplia;

class ApiClientTest extends \PHPUnit_Framework_TestCase
{
    public function testGetTemplates()
    {
        $client = new ApiClient();

        $templates = $client->getTemplates(array());

        $this->assertNotNull($templates);
    }

    public function testGetCategories()
    {
        $client = new ApiClient();

        $templates = $client->getCategories(array());

        $this->assertNotNull($templates);
    }

    /**
     * @expectedException     \Stamplia\ApiException
     * @expectedExceptionCode 400
     */
    public function testInvalidLogin()
    {
        $client = new ApiClient();

        $templates = $client->login(array(
            'email' => 'invalid@email.com',
            'password' => 'nopassword'
        ));

        $this->assertNotNull($templates);
    }

    /**
     * @expectedException     \Stamplia\ApiException
     * @expectedExceptionCode 401
     */
    public function testUnauthorizedAccess()
    {
        $client = new ApiClient();

        $client->getUserTemplates(array());
    }
}
