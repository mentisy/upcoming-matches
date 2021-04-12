<?php
declare(strict_types=1);

namespace Avolle\UpcomingMatches;

use Avolle\UpcomingMatches\Exception\InvalidRendererException;
use Avolle\UpcomingMatches\Exception\InvalidSportException;
use Avolle\UpcomingMatches\Exception\MissingSportConfigurationException;
use Avolle\UpcomingMatches\Render\RenderInterface;
use Avolle\UpcomingMatches\Services\ServicesConfig;
use Avolle\UpcomingMatches\Services\ServicesInterface;
use Avolle\UpcomingMatches\Validator\Validator;
use Avolle\UpcomingMatches\View\View;

/**
 * Class App
 *
 * @package Avolle\UpcomingMatches
 */
class App
{
    /**
     * Application config
     *
     * @var array
     */
    protected array $appConfig;

    /**
     * Request data retrieved from query string
     *
     * @var array
     */
    protected array $requestData;

    /**
     * View class
     *
     * @var \Avolle\UpcomingMatches\View\View
     */
    protected View $view;

    /**
     * Service to use in the app
     *
     * @var \Avolle\UpcomingMatches\Services\ServicesInterface
     */
    protected ServicesInterface $service;

    /**
     * Sport's config
     *
     * @var \Avolle\UpcomingMatches\SportConfig
     */
    protected SportConfig $sportConfig;

    /**
     * App constructor.
     *
     * @param array $requestData Request data on this request
     */
    public function __construct(array $requestData)
    {
        $this->appConfig = $this->appConfig();
        $this->requestData = $requestData;
        $this->view = new View($requestData);
    }

    /**
     * Run the application
     *
     * @throws \Avolle\UpcomingMatches\Exception\InvalidRendererException
     * @throws \Avolle\UpcomingMatches\Exception\InvalidSportException
     * @throws \Avolle\UpcomingMatches\Exception\MissingSportConfigurationException
     * @throws \Avolle\UpcomingMatches\Exception\RuleNotFoundException
     * @throws \Avolle\UpcomingMatches\Exception\MissingViewException
     */
    public function run()
    {
        $this->view->setVar('sports', $this->appConfig['sports']);
        if (empty($this->requestData)) {
            return $this->view->display('form');
        }

        $errors = $this->validateRequestData($this->requestData);
        if (!empty($errors)) {
            $this->view->setErrors($errors);

            return $this->view->display('form');
        }

        $sport = $this->requestData['sport'];

        $this->service = $this->initService($sport);
        $dateFrom = $this->requestData['dateFrom'];
        $dateTo = $this->requestData['dateTo'];

        $this->service->useCache()->fetch($dateFrom, $dateTo);

        $matches = $this->service->toArray();

        if (empty($matches)) {
            $this->view->setInfoMessage('Ingen kamper funnet.');

            return $this->view->display('form');
        }

        return $this->render($matches);
    }

    /**
     * Initializes the requested sport service instance
     *
     * @param string $sport Sport service to use
     * @return \Avolle\UpcomingMatches\Services\ServicesInterface
     * @throws \Avolle\UpcomingMatches\Exception\InvalidSportException
     * @throws \Avolle\UpcomingMatches\Exception\MissingSportConfigurationException
     */
    protected function initService(string $sport): ServicesInterface
    {
        $serviceName = str_replace(' ', '', ucwords($sport));
        $serviceClassName = sprintf("Avolle\\UpcomingMatches\\Services\\%sService", $serviceName);

        if (!class_exists($serviceClassName)) {
            throw new InvalidSportException($serviceName . ' is not a valid sport.');
        }

        if (!isset($this->appConfig['sports'][$sport]->serviceConfig)) {
            throw new MissingSportConfigurationException('Missing `' . $sport . '` Service configuration.');
        }
        if (!$this->appConfig['sports'][$sport]->serviceConfig instanceof ServicesConfig) {
            throw new MissingSportConfigurationException(
                'The `' . $sport . '` configuration must be instance of ' . ServicesConfig::class
            );
        }

        $this->sportConfig = $this->appConfig['sports'][$sport];

        return new $serviceClassName($this->sportConfig->serviceConfig);
    }

    /**
     * Load the app config
     */
    protected function appConfig(): array
    {
        return require ROOT . 'config.php';
    }

    /**
     * Render the matches depending on the configuration's render class
     *
     * @param array $matches Match entities retrieved from the selected service
     * @return \Avolle\UpcomingMatches\App
     * @throws \Avolle\UpcomingMatches\Exception\InvalidRendererException
     */
    protected function render(array $matches): self
    {
        $renderClass = $this->appConfig['renderClass'];

        if (!class_exists($renderClass)) {
            throw new InvalidRendererException($renderClass . ' is not a valid render class.');
        }

        $matches = collection($matches);

        $renderer = new $renderClass($matches, $this->sportConfig);
        $theme = new $this->appConfig['theme']();
        $renderer->setTheme($theme);
        $renderer->render();

        if (!$renderer instanceof RenderInterface) {
            throw new InvalidRendererException($renderClass . ' must implement ' . RenderInterface::class);
        }

        $renderer->output();

        return $this;
    }

    /**
     * Validate the request data, making sure they follow the structure
     *
     * @param array $requestData Request data for this request
     * @return array
     * @throws \Avolle\UpcomingMatches\Exception\RuleNotFoundException
     */
    protected function validateRequestData(array $requestData): array
    {
        $validator = new Validator($requestData);

        $validator
            ->notEmpty('dateFrom')
            ->date('dateFrom');

        $validator
            ->notEmpty('dateTo')
            ->date('dateTo');

        $validator
            ->notEmpty('sport')
            ->inList('sport', $this->validServices());

        $validator->validate();

        return $validator->getErrors();
    }

    /**
     * Retrieves the valid services based on the application config
     *
     * @return array
     */
    private function validServices(): array
    {
        return array_keys($this->appConfig['sports']);
    }
}
