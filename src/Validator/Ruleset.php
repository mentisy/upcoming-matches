<?php

namespace Avolle\UpcomingMatches\Validator;

use Cake\Chronos\Date;
use Exception;

/**
 * Class Ruleset
 *
 * @package Avolle\UpcomingMatches\Validator
 */
class Ruleset
{
    /**
     * Validates that the given input is a valid date
     *
     * @param mixed $input
     * @return bool
     */
    public function date($input): bool
    {
        try {
            return Date::parse($input) !== null;
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * Validates that the given input is not empty
     *
     * @param mixed $input Input to validate is not empty
     * @return bool
     */
    public function notEmpty($input): bool
    {
        return !empty($input);
    }

    /**
     * Validates that the input variable exists inside the list variable
     *
     * @param mixed $input Input to validate exists in list
     * @param array $list List to validate input against
     * @return bool
     */
    public function inList($input, array $list): bool
    {
        return in_array($input, $list);
    }

    /**
     * Returns whether the input $rule exists in this ruleset class
     *
     * @param string $rule Rule to check exists
     * @return bool
     */
    public function hasRule(string $rule): bool
    {
        return method_exists($this, $rule);
    }
}
