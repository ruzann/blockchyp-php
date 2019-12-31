<?php

namespace BlockChyp;

require_once(__DIR__ . '/CryptoUtils.php');

/**
 * Base class for the PHP BlockChyp client.
 *
 * @package BlockChyp
 */
class BlockChypClient {

  const CARD_TYPE_CREDIT = 0;
  const CARD_TYPE_DEBIT = 1;
  const CARD_TYPE_EBT = 2;
  const CARD_TYPE_BLOCKCHAIN_GIFT = 3;

  const SIGNATURE_FORMAT_NONE = "";
  const SIGNATURE_FORMAT_PNG = "png";
  const SIGNATURE_FORMAT_JPG = "jpg";
  const SIGNATURE_FORMAT_GIF = "gif";

  const PROMPT_TYPE_AMOUNT = "amount";
  const PROMPT_TYPE_EMAIL = "email";
  const PROMPT_TYPE_PHONE_NUMBER = "phone";
  const PROMPT_TYPE_CUSTOMER_NUMBER = "customer-number";
  const PROMPT_TYPE_REWARDS_NUMBER = "rewards-number";

  protected static $apiKey;

  protected static $bearerToken;

  protected static $signingKey;

  protected static $gatewayHost = "https://api.blockchyp.com";

  protected static $testGatewayHost = "https://test.blockchyp.com";

  protected static $https = TRUE;

  protected static $routeCacheLocation;

  protected static $routeCacheTTL = 60;

  protected static $gatewayTimeout = 20;

  protected static $terminalTimeout = 120;

  protected static $terminalConnectTimeout = 5;

  protected static $offlineCacheEnabled = TRUE;

  protected static $routeCache = [];

  private static $offlineFixedKey = "cb22789c9d5c344a10e0474f134db39e25eb3bbf5a1b1a5e89b507f15ea9519c";

  public static function setApiKey($apiKey) {
    self::$apiKey = $apiKey;
  }

  public static function setBearerToken($bearerToken) {
    self::$bearerToken = $bearerToken;
  }

  public static function setSigningKey($signingKey) {
    self::$signingKey = $signingKey;
  }

  public static function setGatewayHost ($gatewayHost) {
    self::$gatewayHost = $gatewayHost;
  }

  public static function setTestGatewayHost ($testGatewayHost) {
    self::$testGatewayHost = $testGatewayHost;
  }

  public static function setHttps($https) {
    self::$https = $https;
  }

  public static function setRouteCacheTTL($routeCacheTTL) {
    self::$routeCacheTTL = $routeCacheTTL;
  }

  public static function setRouteCacheLocation($routeCacheLocation) {
    self::$routeCacheLocation = $routeCacheLocation;
  }

  public static function setGatewayTimeout($gatewayTimeout) {
    self::$gatewayTimeout = $gatewayTimeout;
  }

  public static function setTerminalTimeout($terminalTimeout) {
    self::$terminalTimeout = $terminalTimeout;
  }

  protected static function generateErrorResponse($msg) {

    return [
      "success" => FALSE,
      "error" => $msg,
      "responseDescription" => $msg
    ];

  }

  protected static function routeCacheGet($terminalName, $stale) {

    $routeCacheEntry = $routeCache[self::$apiKey . $terminalName];

    if (!$routeCacheEntry && self::$offlineCacheEnabled) {
      $offlineCache = self::readOfflineCache();
      $routeCacheEntry = $offlineCache[self::$apiKey . $terminalName];
      if ($routeCacheEntry) {
        $route = $routeCacheEntry["route"];
        $txCreds = $route["transientCredentials"];
        $txCreds["apiKey"] = self::decrypt($txCreds["apiKey"]);
        $txCreds["bearerToken"] = self::decrypt($txCreds["bearerToken"]);
        $txCreds["signingKey"] = self::decrypt($txCreds["signingKey"]);
        $route["transientCredentials"] = $txCreds;
        $routeCacheEntry["route"] = $route;
      }
    }

    if ($routeCacheEntry) {
      $now = new \DateTime();
      $ttl = new \DateTime($routeCacheEntry["ttl"]["date"]);
      if ($stale || ($now < $ttl)) {
        return $routeCacheEntry["route"];
      }
    }
    return FALSE;

  }

  protected static function routeTerminalRequest($request, $terminalPath, $cloudPath, $method="POST") {

    if (!empty($request["terminalName"])) {
      $route = self::resolveTerminalRoute($request["terminalName"]);
      if (!$route) {
        return self::generateErrorResponse("Unknown Terminal");
      } else if ($route["cloudRelayEnabled"]) {
        return self::gatewayRequest($cloudPath, $request, $method);
      }
      return self::terminalRequest($route, $terminalPath, $request, $method);
    } else {
      return self::gatewayRequest($cloudPath, $request, $method);
    }

  }

  protected static function resolveTerminalURL($route, $path) {

    $url = "";
    if (self::$https) {
      $url = $url . "https://";
    } else {
      $url = $url . "http://";
    }
    $url = $url . $route["ipAddress"];
    if (self::$https) {
      $url = $url . ":8443";
    } else {
      $url = $url . ":8080";
    }
    $url = $url . $path;

    return $url;
  }

  protected static function terminalRequest($route, $path, $request, $method, $evictEnabled=TRUE) {

    $url = self::resolveTerminalURL($route, $path);

    $txCreds = $route["transientCredentials"];

    $terminalRequest = [
      "apiKey" => $txCreds["apiKey"],
      "bearerToken" => $txCreds["bearerToken"],
      "signingKey" => $txCreds["signingKey"],
      "request" => $request
    ];

    echo $method . ": " . $url . PHP_EOL;

    $content = json_encode($terminalRequest);

    $headers = [];
    array_push($headers, 'Content-Type: application/json');
    array_push($headers, 'Content-Length: ' . strlen($content));

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
    curl_setopt($ch, CURLOPT_TIMEOUT, self::$terminalTimeout * 60);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::$terminalConnectTimeout);
    if (self::$https) {
      curl_setopt($ch, CURLOPT_CAINFO, __DIR__.'/terminal.crt');
      curl_setopt($ch, CURLOPT_CAPATH, __DIR__.'/terminal.crt');
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    }
    if( ! $result = curl_exec($ch))
    {
      $err = curl_error($ch);
      if ($evictEnabled && strpos($err, "timed out") > 0) {
        curl_close($ch);
        self::refreshRoute($request["terminalName"]);
        return self::terminalRequest($route, $path, $request, $method, FALSE);
      } else {
        trigger_error($err);
      }
    }
    curl_close($ch);

    return json_decode($result, TRUE);

  }

  protected static function refreshRoute($terminalName) {

    $route = self::requestRouteFromGateway($terminalName);
    if ($route) {
      $date = new \DateTime();
      $interval = \DateInterval::createFromDateString(self::$routeCacheTTL . " minutes");
      $routeCacheEntry = [
        "route" => $route,
        "ttl" => $date->add($interval)
      ];
      self::$routeCache[self::$apiKey . $terminalName] = $routeCacheEntry;
      self::updateOfflineCache($routeCacheEntry);
    }

  }

  protected static function resolveTerminalRoute($terminalName) {

    $route = self::routeCacheGet($terminalName, FALSE);

    if (!$route) {
      echo "Route Cache Miss For " . $terminalName . PHP_EOL;
      $route = self::requestRouteFromGateway($terminalName);
      if ($route) {
        $date = new \DateTime();
        $interval = \DateInterval::createFromDateString(self::$routeCacheTTL . " minutes");
        $routeCacheEntry = [
          "route" => $route,
          "ttl" => $date->add($interval)
        ];
        $routeCache[self::$apiKey . $terminalName] = $routeCacheEntry;
        self::updateOfflineCache($routeCacheEntry);
        return $route;
      }
    } else {
      echo "Route Cache Hit For " . $terminalName . PHP_EOL;
      return $route;
    }

  }

  protected static function resolveOfflineCacheLocation() {

    if (self::$routeCacheLocation) {
      return self::$routeCacheLocation;
    } else {
      return "/" . CacheUtils::joinPaths(sys_get_temp_dir(), ".blockchyp_routes");
    }

  }

  protected static function updateOfflineCache($routeCacheEntry) {

    if (self::$offlineCacheEnabled) {
      $offlineCache = self::readOfflineCache();
      $route = $routeCacheEntry["route"];
      $txCreds = $route["transientCredentials"];
      $txCreds["apiKey"] = self::encrypt($txCreds["apiKey"]);
      $txCreds["bearerToken"] = self::encrypt($txCreds["bearerToken"]);
      $txCreds["signingKey"] = self::encrypt($txCreds["signingKey"]);
      $route["transientCredentials"] = $txCreds;
      $routeCacheEntry["route"] = $route;
      $offlineCache[self::$apiKey . $route["terminalName"]] = $routeCacheEntry;
      $fileHandle = fopen(self::resolveOfflineCacheLocation(), "w");
      fwrite($fileHandle, json_encode($offlineCache));
      fclose($fileHandle);
    }

  }

  protected static function decrypt($cipherText) {

    $cipherText = hex2bin($cipherText);
    $key = self::deriveOfflineKey();
    $method = "AES-256-CBC";
    $iv = substr($cipherText, 0, 16);
    $cipherText = substr($cipherText, 16, strlen($cipherText));
    return openssl_decrypt($cipherText , $method, $key, OPENSSL_RAW_DATA, $iv);
  }

  protected static function encrypt($plainText) {

    $key = self::deriveOfflineKey();
    $method = "AES-256-CBC";
    $iv = openssl_random_pseudo_bytes(16);
    $cipherText = openssl_encrypt($plainText, $method, $key, OPENSSL_RAW_DATA, $iv);
    return bin2hex($iv . $cipherText);
  }

  protected static function deriveOfflineKey() {

    return hash("sha256", self::$offlineFixedKey . self::$signingKey);

  }

  protected static function readOfflineCache() {

    if (self::$offlineCacheEnabled) {

      echo "Cache Location: " . self::resolveOfflineCacheLocation() . PHP_EOL;

      if (!file_exists(self::resolveOfflineCacheLocation())) {
        return [];
      }
      return json_decode(file_get_contents(self::resolveOfflineCacheLocation()), TRUE);
    }

  }

  protected static function requestRouteFromGateway($terminalName) {

    $route = self::gatewayGet("/api/terminal-route?terminal=" . urlencode($terminalName), FALSE);
    if (!$route) {
      return FALSE;
    }
    if ($route && !empty($route["ipAddress"])) {
      $route["exists"] = true;
      return $route;
    }
    return FALSE;

  }

  protected static function gatewayPost($path, $request) {

    $url = self::resolveGatewayURL($path, $test);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, self::generateGatewayHeaders());
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $gatewayTimeout * 60);
    if( ! $result = curl_exec($ch))
    {
        trigger_error(curl_error($ch));
    }
    curl_close($ch);

    return json_decode($result, TRUE);

  }

  protected static function gatewayRequest($path, $request, $method="POST") {

    $url = self::resolveGatewayURL($path, $request["test"]);

    echo $method . ": " . $url . PHP_EOL;

    $content = json_encode($request);

    $headers = self::generateGatewayHeaders();

    array_push($headers, 'Content-Type: application/json');
    array_push($headers, 'Content-Length: ' . strlen($content));

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
    curl_setopt($ch, CURLOPT_TIMEOUT, $gatewayTimeout * 60);
    if( ! $result = curl_exec($ch))
    {
        trigger_error(curl_error($ch));
    }
    curl_close($ch);

    return json_decode($result, TRUE);

  }

  protected static function gatewayGet($path, $test) {

    $url = self::resolveGatewayURL($path, $test);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, self::generateGatewayHeaders());
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $gatewayTimeout * 60);
    if( ! $result = curl_exec($ch))
    {
        trigger_error(curl_error($ch));
    }
    curl_close($ch);

    echo "GET RESPONSE: " . $result . PHP_EOL;

    return json_decode($result, TRUE);

  }

  private static function resolveGatewayURL($path, $test) {

    $url = "";

    if ($test) {
      $url = $url . self::$testGatewayHost;
    } else {
      $url = $url . self::$gatewayHost;
    }

    return $url . $path;

  }

  private static function generateGatewayHeaders() {

    $nonce = generateNonce();
    $timestamp = timestamp();
    $sig = self::compute_hmac($timestamp, $nonce);

    $headers = [
      "Nonce: " . $nonce,
      "Timestamp: " . $timestamp,
      "Authorization: Dual " . self::$bearerToken . ":" . self::$apiKey. ":" . $sig
    ];

    return $headers;

  }

  private static function compute_hmac($ts, $nonce) {

    $c = self::$apiKey . self::$bearerToken . $ts . $nonce;
    $sig = hash_hmac('sha256', $c, hex2bin(self::$signingKey));
    return $sig;

  }


}