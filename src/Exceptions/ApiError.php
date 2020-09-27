<?php

declare(strict_types=1);

namespace Kuvardin\SmsCenter\Exceptions;

use Exception;
use RuntimeException;
use Throwable;

/**
 * Class ApiError
 *
 * @package Kuvardin\SmsCenter
 * @author Maxim Kuvardin <maxim@kuvard.in>
 */
class ApiError extends Exception
{
    /** Неизвестная ошибка */
    public const CODE_UNKNOWN = 0;

    /** Ошибка в параметрах */
    public const CODE_PARAMS = 1;

    /** Неверный логин или пароль */
    public const CODE_LOGIN = 2;

    /** Недостаточно средств на счете Клиента */
    public const CODE_MONEY = 3;

    /** IP-адрес временно заблокирован из-за частых ошибок в запросах. Подробне */
    public const CODE_IP_BLOCKED = 4;

    /** Неверный формат даты */
    public const CODE_INCORRECT_DATE_FORMAT = 5;

    /** Сообщение запрещено (по тексту или по имени отправителя). Также данная ошибка возникает при попытке отправки
     * массовых и (или) рекламных сообщений без заключенного договора
     */
    public const CODE_SPAM_BLOCK = 6;

    /** Неверный формат номера телефона */
    public const CODE_PHONE_FORMAT = 7;

    /** Сообщение на указанный номер не может быть доставлено */
    public const CODE_CANNOT_BE_DELIVERED = 8;

    /** Отправка более одного одинакового запроса на передачу SMS-сообщения либо более пяти одинаковых запросов на
     * получение стоимости сообщения в течение минуты. Данная ошибка возникает также при попытке отправки более
     * 15 любых запросов одновременно
     */
    public const CODE_DOS_BLOCK = 9;

    /**
     * ApiError constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message, int $code, Throwable $previous = null)
    {
        if (!self::checkCode($code)) {
            throw new RuntimeException("Unknown SMS Center error code: $code");
        }
        parent::__construct($message, $code, $previous);
    }

    /**
     * @param int $code
     * @return bool
     */
    public static function checkCode(int $code): bool
    {
        return $code === self::CODE_PARAMS ||
            $code === self::CODE_LOGIN ||
            $code === self::CODE_MONEY ||
            $code === self::CODE_IP_BLOCKED ||
            $code === self::CODE_INCORRECT_DATE_FORMAT ||
            $code === self::CODE_SPAM_BLOCK ||
            $code === self::CODE_PHONE_FORMAT ||
            $code === self::CODE_CANNOT_BE_DELIVERED ||
            $code === self::CODE_DOS_BLOCK;
    }
}
