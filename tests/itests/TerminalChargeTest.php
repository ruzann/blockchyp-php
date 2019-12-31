<?php

namespace BlockChyp;

require_once(__DIR__ . '/../BlockChypTestCase.php');

class TerminalChargeTest extends BlockChypTestCase
{

  public function testTerminalCharge()
  {

    $config = $this->loadTestConfiguration();

    BlockChyp::setApiKey($config->apiKey);
    BlockChyp::setBearerToken($config->bearerToken);
    BlockChyp::setSigningKey($config->signingKey);
    BlockChyp::setGatewayHost($config->gatewayHost);
    BlockChyp::setTestGatewayHost($config->testGatewayHost);

    $this->processTestDelay("TerminalChargeTest");



  // setup request object
  $request = [];
  $request["terminalName"] = "Test Terminal";
  $request["amount"] = "25.15";
  $request["test"] = true;
  self::logRequest($request);
  $response = BlockChyp::charge($request);
  self::logResponse($response);


    // response assertions
    $this->assertTrue($response["approved"]);
    $this->assertTrue($response["test"]);
    $this->assertEquals(6, strlen($response["authCode"]));
    $this->assertNotEmpty($response["transactionId"]);
    $this->assertNotEmpty($response["timestamp"]);
    $this->assertNotEmpty($response["tickBlock"]);
    $this->assertEquals("Approved", $response["responseDescription"]);
    $this->assertNotEmpty($response["paymentType"]);
    $this->assertNotEmpty($response["maskedPan"]);
    $this->assertNotEmpty($response["entryMethod"]);
    $this->assertEquals("25.15", $response["authorizedAmount"]);

  }


}