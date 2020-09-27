<?php

declare(strict_types=1);

namespace Kuvardin\SmsCenter;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Kuvardin\SmsCenter\Exceptions\ApiError;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Api
 *
 * @package Kuvardin\SmsCenter
 * @author Maxim Kuvardin <maxim@kuvard.in>
 */
class Api
{
    public const CONNECTION_TIMEOUT_DEFAULT = 10;
    public const REQUEST_TIMEOUT_DEFAULT = 15;
    public const USER_AGENT_DEFAULT = 'Web client';
    public const CHARSET_DEFAULT = 'utf-8';
    public const HOST = 'https://smsc.kz';

    /**
     * @var Client
     */
    protected Client $client;

    /**
     * @var string
     */
    protected string $login;

    /**
     * @var string
     */
    protected string $password;

    /**
     * @var int
     */
    protected int $requests_counter = 0;

    /**
     * @var array|null
     */
    protected ?array $last_request_info;

    /**
     * @var ResponseInterface|null
     */
    protected ?ResponseInterface $last_response = null;

    /**
     * @var int
     */
    protected int $connection_timeout = self::CONNECTION_TIMEOUT_DEFAULT;

    /**
     * @var int
     */
    protected int $request_timeout = self::REQUEST_TIMEOUT_DEFAULT;

    /**
     * @var string
     */
    protected string $user_agent = self::USER_AGENT_DEFAULT;

    /**
     * @var string
     */
    protected string $charset = self::CHARSET_DEFAULT;

    /**
     * Api constructor.
     *
     * @param Client $client
     * @param string $login
     * @param string $password
     */
    public function __construct(Client $client, string $login, string $password)
    {
        $this->client = $client;
        $this->login = $login;
        $this->password = $password;
    }

    /**
     * @return array|null
     */
    public function getLastRequestInfo(): ?array
    {
        return $this->last_request_info;
    }

    /**
     * @return int
     */
    public function getRequestsCounter(): int
    {
        return $this->requests_counter;
    }

    /**
     * @return ResponseInterface|null
     */
    public function getLastResponse(): ?ResponseInterface
    {
        return $this->last_response;
    }

    /**
     * @param string $text
     * @param string $phone_number
     * @param string|null $sender
     * @return mixed
     * @throws ApiError
     * @throws GuzzleException
     */
    public function sendMessageBySms(string $text, string $phone_number, string $sender = null)
    {
        return $this->request('send', [], [
            'phones' => $phone_number,
            'mes' => $text,
            'cost' => 3,
            'fmt' => 3,
            'sender' => $sender,
            'charset' => $this->charset,
        ]);
    }

    /**
     * @param string $method
     * @param array|null $get_params
     * @param array|null $post_params
     * @return mixed
     * @throws ApiError
     * @throws GuzzleException
     */
    public function request(string $method, array $get_params = null, array $post_params = null)
    {
        $this->requests_counter++;

        if ($post_params === null) {
            $get_params ??= [];
            $get_params['login'] = $this->login;
            $get_params['psw'] = $this->password;
        } else {
            $post_params['login'] = $this->login;
            $post_params['psw'] = $this->password;
        }

        $this->last_response = $this->client->request(
            $post_params === null ? 'GET' : 'POST',
            self::HOST . "/sys/{$method}.php",
            [
                RequestOptions::CONNECT_TIMEOUT => $this->connection_timeout,
                RequestOptions::TIMEOUT => $this->request_timeout,
                RequestOptions::DECODE_CONTENT => true,
                RequestOptions::FORM_PARAMS => $post_params,
            ],
        );

        if ($this->last_response->getStatusCode() !== 200) {
            throw new ApiError("SMS Center returned HTTP error #{$this->last_response->getStatusCode()}",
                ApiError::CODE_UNKNOWN);
        }

        $result_decoded = json_decode($this->last_response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        if (!empty($result_decoded['error_code'])) {
            throw new ApiError($result_decoded['error'], $result_decoded['error_code']);
        }

        return $result_decoded;
    }

    /**
     * @param string $text
     * @param string[] $phone_numbers
     * @param string|null $sender
     * @return mixed
     * @throws ApiError
     * @throws GuzzleException
     */
    public function sendMessagesBySms(string $text, array $phone_numbers, string $sender = null)
    {
        return $this->request('send', null, [
            'phones' => implode(',', $phone_numbers),
            'fmt' => 3,
            'sender' => $sender,
            'charset' => $this->charset,
        ]);
    }

    /**
     * @param string $text
     * @param array $phone_numbers
     * @return mixed
     * @throws ApiError
     * @throws GuzzleException
     */
    public function getPriseOfSmsMessages(string $text, array $phone_numbers)
    {
        return $this->request('send', null, [
            'phones' => implode(',', $phone_numbers),
            'mes' => $text,
            'cost' => 1,
            'fmt' => 3,
            'charset' => $this->charset,
        ]);
    }
}
