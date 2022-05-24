<?php

use BlockChyp\BlockChyp;

require_once(__DIR__ . '/../BlockChypTestCase.php');

class TCTemplateTest extends BlockChypTestCase
{

    /**
     * @group itest
     */
    public function testTCTemplate()
    {
        $config = $this->loadTestConfiguration();

        BlockChyp::setApiKey($config->apiKey);
        BlockChyp::setBearerToken($config->bearerToken);
        BlockChyp::setSigningKey($config->signingKey);
        BlockChyp::setGatewayHost($config->gatewayHost);
        BlockChyp::setTestGatewayHost($config->testGatewayHost);

        $this->processTestDelay("TCTemplateTest", $config->defaultTerminalName);

        // Set request values
        $request = [
            'alias' => $this->getUUID(),
            'name' => 'HIPPA Disclosure',
            'content' => 'Lorem ipsum dolor sit amet.',
        ];

        self::logRequest($request);

        $response = BlockChyp::tcUpdateTemplate($request);

        self::logResponse($response);

        if (!empty($response['transactionId'])) {
            $lastTransactionId = $response['transactionId'];
        }
        if (!empty($response['transactionRef'])) {
            $lastTransactionRef = $response['transactionRef'];
        }
        if (!empty($response['customer'])) {
            $lastCustomer = $response['customer'];
        }
        if (!empty($response['token'])) {
            $lastToken = $response['token'];
        }
        if (!empty($response['linkCode'])) {
            $lastLinkCode = $response['linkCode'];
        }

        // Set request values
        $request = [
            'templateId' => ,
        ];

        self::logRequest($request);

        $response = BlockChyp::tcTemplate($request);

        self::logResponse($response);

        // Response assertions
        $this->assertTrue($response['success']);

        $this->assertEquals('HIPPA Disclosure', $response['name']);

        $this->assertEquals('Lorem ipsum dolor sit amet.', $response['content']);
        $this->processResponseDelay($request);
    }
}
