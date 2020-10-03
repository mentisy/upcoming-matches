<?php

namespace Avolle\WeeklyMatches\View;

use Avolle\WeeklyMatches\Exception\MissingViewException;

/**
 * Class View
 *
 * @package Avolle\WeeklyMatches\View
 */
class View
{
    /**
     * Error Helper
     *
     * @var \Avolle\WeeklyMatches\View\ErrorHelper
     */
    protected ErrorHelper $Error;

    /**
     * The specific view's content
     *
     * @var string
     */
    private string $content;

    /**
     * The variables to be used in the layout or view
     *
     * @var array
     */
    private array $vars = [];

    /***
     * @var array Request data
     */
    private array $requestData;

    /**
     * View constructor.
     *
     * @param array $requestData The query string data for this request
     */
    public function __construct(array $requestData = [])
    {
        $this->Error = new ErrorHelper();
        $this->requestData = $requestData;
    }

    /**
     * Set ErrorHelper errors
     *
     * @param array $errors List of errors to be used in the Error Helper
     * @return void
     */
    public function setErrors(array $errors): void
    {
    	$this->Error->setErrors($errors);
    }

    /**
     * Display the selected view template. Surround with layout
     *
     * @param string $view View template to display
     * @return self
     * @throws \Avolle\WeeklyMatches\Exception\MissingViewException
     */
    public function display(string $view): self
    {
        extract($this->vars);
        $this->createContent($view);
        require TEMPLATES . 'layout.php';

        return $this;
    }

    /**
     * Return the content of the view template
     *
     * @return string
     */
    public function content()
    {
        return $this->content;
    }

    /**
     * Create the view template content
     *
     * @param string $view View template to display
     * @return void
     * @throws \Avolle\WeeklyMatches\Exception\MissingViewException
     */
    private function createContent(string $view): void
    {
        extract($this->vars);
        $file = TEMPLATES . $view . '.php';
        if (!file_exists($file)) {
            throw new MissingViewException('View `' . $view . '` does not exist.');
        }

        ob_start();
        require $file;
        $this->content = ob_get_clean();
    }

    /**
     * Set a view variable
     *
     * @param string $variable Variable name
     * @param mixed $value Variable value
     * @return void
     */
    public function setVar(string $variable, $value): void
    {
        $this->vars[$variable] = $value;
    }

    /**
     * Set info message view var
     *
     * @param string $message Message to store as info message
     * @return void
     */
    public function setInfoMessage(string $message)
    {
        $this->vars['infoMessage'] = $message;
    }
}
