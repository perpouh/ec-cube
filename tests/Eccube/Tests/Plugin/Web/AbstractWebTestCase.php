<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


namespace Eccube\Tests\Plugin\Web;

use Eccube\Tests\EccubeTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

abstract class AbstractWebTestCase extends EccubeTestCase
{
    protected $client;

    // テスト用プラグイン.
    protected $from = __DIR__.'/../HookPoint';

    // テスト用プラグイン設置先.
    protected $to = __DIR__.'/../../../../../app/Plugin/HookPoint';

    public function setUp()
    {
        // テスト用プラグインを設置.
        $fs = new Filesystem();
        $fs->mirror($this->from, $this->to);

        parent::setUp();
        $this->client = $this->createClient();
    }

    public function tearDown()
    {
        // テスト用プラグインを削除.
        $fs = new Filesystem();
        $fs->remove($this->to);

        parent::tearDown();
        $this->client = null;
    }

    /**
     * echo等で出力される内容の検証.
     *
     * @param array $expected
     * @param string $message
     */
    public function verifyOutputString(array $expected, $message = '')
    {
        $expected = implode('', $expected);

        $this->expectOutputString($expected, $message);
    }

    /**
     * {@inheritdoc}
     */
    public function logIn($user = null)
    {
        $firewall = 'customer';

        if (!is_object($user)) {
            $user = $this->createCustomer();
        }
        $token = new UsernamePasswordToken($user, null, $firewall, array('ROLE_USER'));

        $this->app['security.token_storage']->setToken($token);
        $this->app['session']->set('_security_' . $firewall, serialize($token));
        $this->app['session']->save();

        $cookie = new Cookie($this->app['session']->getName(), $this->app['session']->getId());
        $this->client->getCookieJar()->set($cookie);
        return $user;
    }
}
