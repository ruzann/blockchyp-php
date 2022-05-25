<?php

use BlockChyp\BlockChyp;

require_once(__DIR__ . '/../BlockChypTestCase.php');

class MediaAssetTest extends BlockChypTestCase
{

    /**
     * @group itest
     */
    public function testMediaAsset()
    {
        $config = $this->loadTestConfiguration();

        BlockChyp::setApiKey($config->apiKey);
        BlockChyp::setBearerToken($config->bearerToken);
        BlockChyp::setSigningKey($config->signingKey);
        BlockChyp::setGatewayHost($config->gatewayHost);
        BlockChyp::setTestGatewayHost($config->testGatewayHost);

        $this->processTestDelay("MediaAssetTest", $config->defaultTerminalName);

        // Set request values
        $request = [
            'fileName' => 'aviato.png',
            'fileSize' => 18843,
            'uploadId' => $this->getUUID(),
        ];

        self::logRequest($request);

        $response = BlockChyp::uploadMedia($request);

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
            'mediaId' => ,
        ];

        self::logRequest($request);

        $response = BlockChyp::mediaAsset($request);

        self::logResponse($response);

        // Response assertions
        $this->assertTrue($response['success']);
        $this->assertNotEmpty($response['id']);

        $this->assertEquals('aviato.png', $response['originalFile']);
        $this->assertNotEmpty($response['fileUrl']);
        $this->assertNotEmpty($response['thumbnailUrl']);
        $this->processResponseDelay($request);
    }
}