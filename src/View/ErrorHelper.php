<?php
declare(strict_types=1);

namespace Avolle\UpcomingMatches\View;

/**
 * Class ErrorHelper
 *
 * @package Avolle\UpcomingMatches\View
 */
class ErrorHelper
{
    /**
     * Templates for rendering errors
     *
     * @var array|string[]
     */
    private array $templates = [
        'errorContainer' => '<div class="error">%s</div>',
        'errorMessage' => '<div>%s</div>',
    ];

    /**
     * Errors passed from validation
     *
     * @var array
     */
    private array $errors;

    /**
     * ErrorHelper constructor.
     *
     * @param array $errors
     */
    public function __construct(array $errors = [])
    {
        $this->errors = $errors;
    }

    /**
     * Create an error message based on the field errors
     *
     * @param string $field Field to get errors for
     * @return string
     */
    public function message(string $field): string
    {
        if (!isset($this->errors[$field])) {
            return '';
        }

        $message = "";
        foreach ($this->errors[$field] as $error) {
            $message .= sprintf($this->templates['errorMessage'], $error['message']);
        }

        return sprintf($this->templates['errorContainer'], $message);
    }

    /**
     * Set errors to use in the helper
     *
     * @param array $errors Errors occured in validation
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    /**
     * Checks whether there are any validation errors
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Set a new array of templates to use in error rendering
     *
     * @param array $templates Array of templates
     */
    public function setTemplates(array $templates): void
    {
        $this->templates = $templates;
    }

    /**
     * Change a specific template to use in error rendering
     *
     * @param string $templateKey The template key to change
     * @param string $templateString The template to use for error rendering
     */
    public function setTemplate(string $templateKey, string $templateString): void
    {
        $this->templates[$templateKey] = $templateString;
    }
}
