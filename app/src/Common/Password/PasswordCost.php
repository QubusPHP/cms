<?php
namespace TriTan\Common\Password;

use TriTan\Interfaces\Password\PasswordCostInterface;

final class PasswordCost implements PasswordCostInterface
{
    /**
     * This code will benchmark your server to determine how high of a cost you can
     * afford. You want to set the highest cost that you can without slowing down
     * you server too much. 8-10 is a good baseline, and more is good if your servers
     * are fast enough. The code below aims for ≤ 50 milliseconds stretching time,
     * which is a good baseline for systems handling interactive logins.
     *
     * @since 1.0.0
     * @return int Server's appropriate cost.
     */
    public function cost()
    {
        $timeTarget = 0.05; // 50 milliseconds

        $cost = 8;
        do {
            $cost++;
            $start = microtime(true);
            password_hash('fy3@zDtF15BNKlVP', PASSWORD_BCRYPT, ['cost' => $cost]);
            $end = microtime(true);
        } while (($end - $start) < $timeTarget);

        return $cost;
    }
}
