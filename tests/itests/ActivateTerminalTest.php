<?php

use BlockChyp\BlockChyp;

require_once(__DIR__ . '/../BlockChypTestCase.php');

class ActivateTerminalTest extends BlockChypTestCase
{

    /**
     * @group itest
     */
    public function testActivateTerminal()
    {
        $config = $this->loadTestConfiguration();

        BlockChyp::setApiKey($config->apiKey);
        BlockChyp::setBearerToken($config->bearerToken);
        BlockChyp::setSigningKey($config->signingKey);
        BlockChyp::setGatewayHost($config->gatewayHost);
        BlockChyp::setTestGatewayHost($config->testGatewayHost);

        $this->processTestDelay("ActivateTerminalTest", $config->defaultTerminalName);

        // Set request values
        $request = [
            'terminalName' => 'Bad Terminal Code',
            'activationCode' => 'XXXXXX',
        ];

        self::logRequest($request);

        $response = BlockChyp::activateTerminal($request);

        self::logResponse($response);

        // Response assertions
        $this->assertFalse($response['success']);

        $this->assertEquals('Invalid Activation Code', $response['error']);
        $this->processResponseDelay($request);
    }
}