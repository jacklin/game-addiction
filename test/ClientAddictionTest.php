<?php

require '../src/Game/Addiction/ClientAddiction.php';

use Game\Addiction;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;


class ClientAddictionTest extends TestCase
{
	protected $app_id = '27e26cdbf912475ba6d72abcc363f488';
	protected $biz_id = '1101999999';
	protected $key = '90fb2b0fdeff9607eea431a5e7c57560';

	public function __construct($name = null, array $data = [], $dataName = '')
    {
        $this->client_addiction = new ClientAddiction($this->app_id, $this->biz_id, $this->key);
        parent::__construct($name, $data, $dataName);
    }
    public function testExample()
    {
        $check = $this->client_addiction->check('100000000000000001', '某一一', '110000190101010001');
        $this->assertStringContainsString('errcode', $check);

        $testCheck = $this->client_addiction->testCheck('100000000000000001', '某一一', '110000190101010001', 'yA2RxS');
        $this->assertStringContainsString('errcode', $testCheck);

        $query = $this->client_addiction->query('100000000000000001');
        $this->assertStringContainsString('errcode', $query);

        $testQuery = $this->client_addiction->testQuery('100000000000000001', 'HHatGD');
        $this->assertStringContainsString('errcode', $testQuery);

        $logout = $this->client_addiction->report([['bt'=>1, 'ct'=>0, 'pi'=>'1fffbjzos82bs9cnyj1dna7d6d29zg4esnh99u']]);
        $this->assertStringContainsString('errcode', $logout);

        $testLogout = $this->client_addiction->testReport([['bt'=>1, 'ct'=>0, 'pi'=>'1fffbjzos82bs9cnyj1dna7d6d29zg4esnh99u']], '99u6kr');
        $this->assertStringContainsString('errcode', $testLogout);
    }
    public function testCheck()
    {
        // 认证成功
        echo "\n";
        echo $this->client_addiction->testCheck('100000000000000001', '某一一', '110000190101010001', 'yA2RxS');
        echo $this->client_addiction->flushInfo();

        // 认证中
        echo $this->client_addiction->testCheck('200000000000000001', '某二一', '110000190201010009', '3xTBoG');
        echo $this->client_addiction->flushInfo();

        // 认证失败
        echo $this->client_addiction->testCheck('300000000000000001', '某三一', '110000190201010009', 'hkqdzR');
        echo $this->client_addiction->flushInfo();

    }

    public function testQuery()
    {
        // 认证成功
        echo "\n";
        echo $this->client_addiction->testQuery('100000000000000001', 'HHatGD');
        echo $this->client_addiction->flushInfo();

        // 认证中
        echo $this->client_addiction->testQuery('200000000000000001', 'BwgbTE');
        echo $this->client_addiction->flushInfo();

        // 认证失败
        echo $this->client_addiction->testQuery('300000000000000001', 'whzSne');
        echo $this->client_addiction->flushInfo();

    }



    public function testReport()
    {
        // 游客模式
        echo "\n";
        echo $this->client_addiction->testLoginOrOut([['bt'=>0, 'ct'=>2, 'di'=>md5('device')]], 'BUSRy9');
        echo $this->client_addiction->flushInfo();

        // 认证模式
        echo $this->client_addiction->testLoginOrOut([['bt'=>1, 'ct'=>0, 'pi'=>'1fffbjzos82bs9cnyj1dna7d6d29zg4esnh99u']], '99u6kr');
        echo $this->client_addiction->flushInfo();
    }
}