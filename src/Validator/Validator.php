<?php

namespace Avolle\UpcomingMatches\Validator;

use Avolle\UpcomingMatches\Exception\RuleNotFoundException;

/**
 * Class Validator
 *
 * @package Avolle\UpcomingMatches\Validator
 */
class Validator
{
    /**
     * Request data
     *
     * @var array
     */
    private array $data;

    /**
     * Rules to check for. Will contain fields and the rules to check for the fields
     *
     * @var array
     */
    private array $rules = [];

    /**
     * Errors that occured during validation
     *
     * @var array
     */
    private array $errors = [];

    /**
     * Error messages to append to the rules
     *
     * @var array|string[]
     */
    private array $messages = [
        'date' => 'Dato kunne ikke tolkes',
        'notEmpty' => 'Dette feltet er påkrevd',
        'inList' => 'Verdien er ikke i den godkjente listen',
    ];

    /**
     * Validator constructor.
     *
     * @param array $data Request data to validate against
     * @param array $messages Messages to use in the event of errors. Should contain the rule name as array key
     *
     * @return void
     */
    public function __construct(array $data, array $messages = [])
    {
        if (!empty($messages)) {
            $this->messages = $messages;
        }
        $this->data = $data;
    }

    /**
     * @param string $field Field to apply rule check to
     * @param string $rule Which rule to apply to field
     * @param array $passed Any passed values for the given rule
     *
     * @return void
     */
    public function add(string $field, string $rule, array $passed = [])
    {
        $this->rules[$field][] = [
            'method' => $rule,
            'passed' => $passed,
        ];
    }

    /**
     * Validate the fields based on the passed rules
     *
     * @return bool True if valid request data. False otherwise
     * @throws \Avolle\UpcomingMatches\Exception\RuleNotFoundException
     */
    public function validate(): bool
    {
        $ruleset = new Ruleset();

        foreach ($this->rules as $field => $rules) {
            foreach ($rules as $rule) {
                if (!$ruleset->hasRule($rule['method'])) {
                    throw new RuleNotFoundException($rule['method']);
                }
                $method = $rule['method'];
                if (!$ruleset->$method($this->data[$field] ?? '', $rule['passed'])) {
                    $this->errors[$field][] = [
                        'rule' => $rule['method'],
                        'message' => $this->messages[$rule['method']],
                    ];
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Add a date validation check to the given field
     *
     * @param string $field Field to add date rule to
     * @return self
     */
    public function date(string $field): self
    {
        $this->add($field, 'date');

        return $this;
    }

    /**
     * Add a notEmpty validation check to the given field
     *
     * @param string $field Field to add notEmpty rule to
     * @return self
     */
    public function notEmpty(string $field): self
    {
        $this->add($field, 'notEmpty');

        return $this;
    }

    /**
     * Add a inList validation check to the given field. Must contain a list to check against
     *
     * @param string $field Field to add inList rule to
     * @param array $list List to check value against
     * @return self
     */
    public function inList(string $field, array $list): self
    {
        $this->add($field, 'inList', $list);

        return $this;
    }

    /**
     * Returns an array of errors that occured during validation. Empty array if no errors occured
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
