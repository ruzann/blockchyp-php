<?php

namespace BlockChyp;

require_once(__DIR__ . '/../BlockChypTestCase.php');

class SimpleVoidTest extends BlockChypTestCase
{

    /**
     * @group itest
     */
    public function testSimpleVoid()
    {
        $config = $this->loadTestConfiguration();

        BlockChyp::setApiKey($config->apiKey);
        BlockChyp::setBearerToken($config->bearerToken);
        BlockChyp::setSigningKey($config->signingKey);
        BlockChyp::setGatewayHost($config->gatewayHost);
        BlockChyp::setTestGatewayHost($config->testGatewayHost);

        $this->processTestDelay("SimpleVoidTest");

        // Set request values
        $request = [
            'pan' => '4111111111111111',
            'amount' => '25.55',
            'test' => true,
            'transactionRef' => $this->getUUID(),
        ];

        self::logRequest($request);

        $response = BlockChyp::void($request);

        self::logResponse($response);

        if (!empty($response['transactionId'])) {
            $lastTransactionId = $response['transactionId'];
        }
        if (!empty($response['transactionRef'])) {
            $lastTransactionRef = $response['transactionRef'];
        }

        // Set request values
        $request = [
            'transactionId' => $lastTransactionId,
            'test' => true,
        ];

        self::logRequest($request);

        $response = BlockChyp::void($request);

        self::logResponse($response);

        // Response assertions
        $this->assertTrue($response['approved']);
    }
}
