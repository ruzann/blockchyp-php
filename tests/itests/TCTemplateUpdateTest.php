<?php

use BlockChyp\BlockChyp;

require_once(__DIR__ . '/../BlockChypTestCase.php');

class TCTemplateUpdateTest extends BlockChypTestCase
{

    /**
     * @group itest
     */
    public function testTCTemplateUpdate()
    {
        $config = $this->loadTestConfiguration();

        BlockChyp::setApiKey($config->apiKey);
        BlockChyp::setBearerToken($config->bearerToken);
        BlockChyp::setSigningKey($config->signingKey);
        BlockChyp::setGatewayHost($config->gatewayHost);
        BlockChyp::setTestGatewayHost($config->testGatewayHost);

        $this->processTestDelay("TCTemplateUpdateTest", $config->defaultTerminalName);

        // Set request values
        $request = [
            'alias' => $this->getUUID(),
            'name' => 'HIPPA Disclosure',
            'content' => 'Lorem ipsum dolor sit amet.',
        ];

        self::logRequest($request);

        $response = BlockChyp::tcUpdateTemplate($request);

        self::logResponse($response);

        // Response assertions
        $this->assertTrue($response['success']);
        $this->assertNotEmpty($response['alias']);

        $this->assertEquals('HIPPA Disclosure', $response['name']);

        $this->assertEquals('Lorem ipsum dolor sit amet.', $response['content']);
        $this->processResponseDelay($request);
    }
}