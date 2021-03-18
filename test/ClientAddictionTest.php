<?php

require_once(__dir__."/vendor/autoload.php");
// require_once(__dir__. "/vendor/jacklin/game-addiction/src/Game/Addiction/ClientAddiction.php");

use Game\Addiction\ClientAddiction;
use Game\Addiction\AesBase64;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;


/**
 * 测试用例如下:
 * 注:
 * 测试时更换 app_id , biz_id key
 * IP白名单需要修改
 */
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
    public function testAseBase64()
    {
		$aesbase64 = new AesBase64('2836e95fcd10e04b0069bb1ee659955b', 'aes-128-gcm', "",0);
		$plaintext = '{"ai":"test-accountId","name":"用户姓名","idNum":"371321199012310912"}';
		$ciphertext = $aesbase64->encbase64($plaintext);
		$_plaintext1 = $aesbase64->descbase64($ciphertext);
		$_plaintext2 = $aesbase64->descbase64('CqT/33f3jyoiYqT8MtxEFk3x2rlfhmgzhxpHqWosSj4d3hq2EbrtVyx2aLj565ZQNTcPrcDipnvpq/D/vQDaLKW70O83Q42zvR0//OfnYLcIjTPMnqa+SOhsjQrSdu66ySSORCAo');
		$this->assertEquals($_plaintext1,$_plaintext2);
    }
    public function testExample()
    {
        $check = $this->client_addiction->setDebug(true)->setBaseUri("https://api.wlc.nppa.gov.cn")->check('100000000000000001', '某一一', '110000190101010001');
        $this->assertTrue(strstr($check, 'errcode') == true);

        $testCheck = $this->client_addiction->setDebug(true)->setBaseUri("https://wlc.nppa.gov.cn")->testCheck('100000000000000001', '某一一', '110000190101010001', 'oxhzBd');
        $this->assertTrue(strstr($testCheck ,'errcode') == true);

        $query = $this->client_addiction->setDebug(true)->setBaseUri("http://api2.wlc.nppa.gov.cn")->query('100000000000000001');
        $this->assertTrue(strstr($query ,'errcode') == true);

        $testQuery = $this->client_addiction->setDebug(true)->setBaseUri("https://wlc.nppa.gov.cn")->testQuery('100000000000000001', 'HHatGD');
        $this->assertTrue(strstr($testQuery ,'errcode') == true);

        $logout = $this->client_addiction->setDebug(true)->setBaseUri("http://api2.wlc.nppa.gov.cn")->report([['bt'=>1, 'ct'=>0, 'pi'=>'1fffbjzos82bs9cnyj1dna7d6d29zg4esnh99u']]);
        $this->assertTrue(strstr($logout ,'errcode') == true);

        $testLogout = $this->client_addiction->setDebug(true)->setBaseUri("https://wlc.nppa.gov.cn")->testReport([['bt'=>1, 'ct'=>0, 'pi'=>'1fffbjzos82bs9cnyj1dna7d6d29zg4esnh99u']], '99u6kr');
        $this->assertTrue(strstr($testLogout ,'errcode') == true);
    }
    public function testCheck()
    {
        /**
         *   testcase01- 实名认证接口 	
         *   请参考测试系统预置数据，调用测试系统中的实名认证接口；如果测试系统返回“认证成功”则通过测试。
         *   路径：https://wlc.nppa.gov.cn/test/authentication/check/测试码
         */
        
        $res = $this->client_addiction->setDebug(true)->setBaseUri("https://wlc.nppa.gov.cn")->testCheck('100000000000000001', '某一一', '110000190101010001', 'dgYK57');
        $this->assertTrue(!empty(strstr($res,'errcode')));


        /**
         * testcase02- 实名认证接口
         * 请参考测试系统预置数据，调用测试系统中的实名认证接口；如果测试系统返回“认证中”则通过测试。
         * 路径：https://wlc.nppa.gov.cn/test/authentication/check/测试码
         */
        $res = $this->client_addiction->setDebug(true)->setBaseUri("https://wlc.nppa.gov.cn")->testCheck('200000000000000001', '某二一', '110000190201010009', 'dgYK57');
        $this->assertTrue(!empty(strstr($res,'errcode')));

        /**
         *  testcase03- 实名认证接口
         *  请参考测试系统预置数据，调用测试系统中的实名认证接口；如果测试系统返回“认证失败”则通过测试。
         *  路径：https://wlc.nppa.gov.cn/test/authentication/check/测试码
         */
        $res = $this->client_addiction->setDebug(true)->setBaseUri("https://wlc.nppa.gov.cn")->testCheck('300000000000000001', '某三一', '110000190201010009', 'KjggWf');
        $this->assertTrue(!empty(strstr($res,'errcode')));

    }

    public function testQuery()
    {
        /**
         *	testcase04- 实名认证结果查询接口
         *  请参考测试系统预置数据，调用测试系统中的实名认证结果查询接口；如果测试系统返回“认证成功”则通过测试。
         *  路径：https://wlc.nppa.gov.cn/test/authentication/query/测试码?
         */
        $res = $this->client_addiction->setDebug(true)->setBaseUri("https://wlc.nppa.gov.cn")->testQuery('100000000000000001', 'GBnWFa');
        $this->assertTrue(!empty(strstr($res,'errcode')));


        /**
         *  testcase05- 实名认证结果查询接口
         *  请参考测试系统预置数据，调用测试系统中的实名认证结果查询接口；如果测试系统返回“认证中”则通过测试。
         *  路径：https://wlc.nppa.gov.cn/test/authentication/query/测试码?
         */
        $res = $this->client_addiction->setDebug(true)->setBaseUri("https://wlc.nppa.gov.cn")->testQuery('200000000000000001', 'kEyNCZ');
        $this->assertTrue(!empty(strstr($res,'errcode')));


        /**
         * testcase06- 实名认证结果查询接口
         * 请参考测试系统预置数据，调用测试系统中的实名认证结果查询接口；如果测试系统返回“认证失败”则通过测试。
         * 路径：https://wlc.nppa.gov.cn/test/authentication/query/测试码?
         */
        $res = $this->client_addiction->setDebug(true)->setBaseUri("https://wlc.nppa.gov.cn")->testQuery('300000000000000001', 'qfhFKm');
        $this->assertTrue(!empty(strstr($res,'errcode')));


    }



    public function testReport()
    {
        /**
         *   testcase07- 游戏用户行为数据上报接口
         *   请参考测试系统预置数据，模拟“游客模式”下游戏用户行为数据上报场景，调用测试系统中的游戏用户行为数据上报接口；如果测试系统返回“上报成功”则通过测试。
         *   路径：https://wlc.nppa.gov.cn/test/collection/loginout/测试码
         */
        $res = $this->client_addiction->setDebug(true)->setBaseUri("https://wlc.nppa.gov.cn")->testReport([['bt'=>0, 'ct'=>2, 'di'=>md5('device')]], 'xrtsp4');
        $this->assertTrue(!empty(strstr($res,'errcode')));

        /**
         *  testcase08- 游戏用户行为数据上报接口
         *  请参考测试系统预置数据，模拟“已认证”游戏用户的行为数据上报场景，调用测试系统中的游戏用户行为数据上报接口；如果测试系统返回“上报成功”则通过测试。
         *  路径：https://wlc.nppa.gov.cn/test/collection/loginout/测试码
         */
        $res = $this->client_addiction->setDebug(true)->setBaseUri("https://wlc.nppa.gov.cn")->testReport([['bt'=>1, 'ct'=>0, 'pi'=>'1fffbjzos82bs9cnyj1dna7d6d29zg4esnh99u']],'ofgeWq');
        $this->assertTrue(!empty(strstr($res,'errcode')));

    }
}