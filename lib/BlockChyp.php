<?php

namespace BlockChyp;

/**
 * Primary entry point for working with BlockChyp in PHP.
 *
 * @package BlockChyp
 */
class BlockChyp extends BlockChypClient
{

    /**
     * tests connection to the gateway
     */
    public static function heartbeat($test)
    {
        return self::gatewayRequest('GET', '/api/heartbeat', ['test' => $test]);
    }

    /**
     * Executes a standard direct preauth and capture.
     *
     * @param array $request The request body.
     *
     * @throws \BlockChyp\Exception\ConnectionException if the connection fails.
     *
     * @return array The API response.
     */
    public static function charge($request)
    {
        return self::routeTerminalRequest('POST', '/api/charge', '/api/charge', $request);
    }

    /**
     * Executes a preauthorization intended to be captured later.
     *
     * @param array $request The request body.
     *
     * @throws \BlockChyp\Exception\ConnectionException if the connection fails.
     *
     * @return array The API response.
     */
    public static function preauth($request)
    {
        return self::routeTerminalRequest('POST', '/api/preauth', '/api/preauth', $request);
    }

    /**
     * Tests connectivity with a payment terminal.
     *
     * @param array $request The request body.
     *
     * @throws \BlockChyp\Exception\ConnectionException if the connection fails.
     *
     * @return array The API response.
     */
    public static function ping($request)
    {
        return self::routeTerminalRequest('POST', '/api/test', '/api/terminal-test', $request);
    }

    /**
     * Checks the remaining balance on a payment method.
     *
     * @param array $request The request body.
     *
     * @throws \BlockChyp\Exception\ConnectionException if the connection fails.
     *
     * @return array The API response.
     */
    public static function balance($request)
    {
        return self::routeTerminalRequest('POST', '/api/balance', '/api/balance', $request);
    }

    /**
     * Clears the line item display and any in progress transaction.
     *
     * @param array $request The request body.
     *
     * @throws \BlockChyp\Exception\ConnectionException if the connection fails.
     *
     * @return array The API response.
     */
    public static function clear($request)
    {
        return self::routeTerminalRequest('POST', '/api/clear', '/api/terminal-clear', $request);
    }

    /**
     * Prompts the user to accept terms and conditions.
     *
     * @param array $request The request body.
     *
     * @throws \BlockChyp\Exception\ConnectionException if the connection fails.
     *
     * @return array The API response.
     */
    public static function termsAndConditions($request)
    {
        return self::routeTerminalRequest('POST', '/api/tc', '/api/terminal-tc', $request);
    }

    /**
     * Appends items to an existing transaction display. Subtotal, Tax, and Total are
     * overwritten by the request. Items with the same description are combined into
     * groups.
     *
     * @param array $request The request body.
     *
     * @throws \BlockChyp\Exception\ConnectionException if the connection fails.
     *
     * @return array The API response.
     */
    public static function updateTransactionDisplay($request)
    {
        return self::routeTerminalRequest('PUT', '/api/txdisplay', '/api/terminal-txdisplay', $request);
    }

    /**
     * Displays a new transaction on the terminal.
     *
     * @param array $request The request body.
     *
     * @throws \BlockChyp\Exception\ConnectionException if the connection fails.
     *
     * @return array The API response.
     */
    public static function newTransactionDisplay($request)
    {
        return self::routeTerminalRequest('POST', '/api/txdisplay', '/api/terminal-txdisplay', $request);
    }

    /**
     * Asks the consumer a text based question.
     *
     * @param array $request The request body.
     *
     * @throws \BlockChyp\Exception\ConnectionException if the connection fails.
     *
     * @return array The API response.
     */
    public static function textPrompt($request)
    {
        return self::routeTerminalRequest('POST', '/api/text-prompt', '/api/text-prompt', $request);
    }

    /**
     * Asks the consumer a yes/no question.
     *
     * @param array $request The request body.
     *
     * @throws \BlockChyp\Exception\ConnectionException if the connection fails.
     *
     * @return array The API response.
     */
    public static function booleanPrompt($request)
    {
        return self::routeTerminalRequest('POST', '/api/boolean-prompt', '/api/boolean-prompt', $request);
    }

    /**
     * Displays a short message on the terminal.
     *
     * @param array $request The request body.
     *
     * @throws \BlockChyp\Exception\ConnectionException if the connection fails.
     *
     * @return array The API response.
     */
    public static function message($request)
    {
        return self::routeTerminalRequest('POST', '/api/message', '/api/message', $request);
    }

    /**
     * Executes a refund.
     *
     * @param array $request The request body.
     *
     * @throws \BlockChyp\Exception\ConnectionException if the connection fails.
     *
     * @return array The API response.
     */
    public static function refund($request)
    {
        return self::routeTerminalRequest('POST', '/api/refund', '/api/refund', $request);
    }

    /**
     * Adds a new payment method to the token vault.
     *
     * @param array $request The request body.
     *
     * @throws \BlockChyp\Exception\ConnectionException if the connection fails.
     *
     * @return array The API response.
     */
    public static function enroll($request)
    {
        return self::routeTerminalRequest('POST', '/api/enroll', '/api/enroll', $request);
    }

    /**
     * Activates or recharges a gift card.
     *
     * @param array $request The request body.
     *
     * @throws \BlockChyp\Exception\ConnectionException if the connection fails.
     *
     * @return array The API response.
     */
    public static function giftActivate($request)
    {
        return self::routeTerminalRequest('POST', '/api/gift-activate', '/api/gift-activate', $request);
    }

    /**
     * Returns the current status of a terminal.
     *
     * @param array $request The request body.
     *
     * @throws \BlockChyp\Exception\ConnectionException if the connection fails.
     *
     * @return array The API response.
     */
    public static function terminalStatus($request)
    {
        return self::routeTerminalRequest('POST', '/api/terminal-status', '/api/terminal-status', $request);
    }

    /**
     * Captures and returns a signature.
     *
     * @param array $request The request body.
     *
     * @throws \BlockChyp\Exception\ConnectionException if the connection fails.
     *
     * @return array The API response.
     */
    public static function captureSignature($request)
    {
        return self::routeTerminalRequest('POST', '/api/capture-signature', '/api/capture-signature', $request);
    }

    /**
     * Executes a manual time out reversal.
     *
     * We love time out reversals. Don't be afraid to use them whenever a request to a
     * BlockChyp terminal times out. You have up to two minutes to reverse any
     * transaction. The only caveat is that you must assign transactionRef values when
     * you build the original request. Otherwise, we have no real way of knowing which
     * transaction you're trying to reverse because we may not have assigned it an id yet.
     * And if we did assign it an id, you wouldn't know what it is because your request to the
     * terminal timed out before you got a response.
     *
     * @param array $request The request body.
     *
     * @throws \BlockChyp\Exception\ConnectionException if the connection fails.
     *
     * @return array The API response.
     */
    public static function reverse($request)
    {
        return self::gatewayRequest('POST', '/api/reverse', $request);
    }
    /**
     * Captures a preauthorization.
     *
     * @param array $request The request body.
     *
     * @throws \BlockChyp\Exception\ConnectionException if the connection fails.
     *
     * @return array The API response.
     */
    public static function capture($request)
    {
        return self::gatewayRequest('POST', '/api/capture', $request);
    }
    /**
     * Closes the current credit card batch.
     *
     * @param array $request The request body.
     *
     * @throws \BlockChyp\Exception\ConnectionException if the connection fails.
     *
     * @return array The API response.
     */
    public static function closeBatch($request)
    {
        return self::gatewayRequest('POST', '/api/close-batch', $request);
    }
    /**
     * Discards a previous preauth transaction.
     *
     * @param array $request The request body.
     *
     * @throws \BlockChyp\Exception\ConnectionException if the connection fails.
     *
     * @return array The API response.
     */
    public static function void($request)
    {
        return self::gatewayRequest('POST', '/api/void', $request);
    }
}
