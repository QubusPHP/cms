<?php
namespace TriTan\Common\Password;

use TriTan\Interfaces\Password\PasswordGenerateInterface;
use Qubus\Hooks\Interfaces\ActionFilterHookInterface;

final class PasswordGenerate implements PasswordGenerateInterface
{
    protected $hook;

    public function __construct(ActionFilterHookInterface $hook)
    {
        $this->hook = $hook;
    }

    /**
     * Generates a random password drawn from the defined set of characters.
     *
     * Uses `random_lib` library to create passwords with far less predictability.
     *
     * @since 1.0.0
     * @param int  $length              Optional. The length of password to generate. Default 12.
     * @param bool $special_chars       Optional. Whether to include standard special characters.
     *                                  Default true.
     * @param bool $extra_special_chars Optional. Whether to include other special characters.
     *                                  Default false.
     * @return string The system generated password.
     */
    public function generate(int $length = 12, bool $special_chars = true, bool $extra_special_chars = false)
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        if ($special_chars) {
            $chars .= '!@#$%^&*()';
        }

        if ($extra_special_chars) {
            $chars .= '-_ []{}<>~`+=,.;:/?|';
        }

        $password = (
            new \RandomLib\Factory()
        )->getGenerator(
            new \SecurityLib\Strength(
                \SecurityLib\Strength::MEDIUM
            )
        )->generateString($length, $chars);

        /**
         * Filters the system generated password.
         *
         * @since 1.0.0
         * @param string $password            The generated password.
         * @param int    $length              The length of password to generate.
         * @param bool   $special_chars       Whether to include standard special characters.
         * @param bool   $extra_special_chars Whether to include other special characters.
         */
        return $this->hook->applyFilter('random_password', $password, $length, $special_chars, $extra_special_chars);
    }
}
