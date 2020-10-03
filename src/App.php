<?php

namespace Avolle\WeeklyMatches;

use Avolle\WeeklyMatches\Exception\InvalidRendererException;
use Avolle\WeeklyMatches\Exception\InvalidSportException;
use Avolle\WeeklyMatches\Exception\MissingSportConfigurationException;
use Avolle\WeeklyMatches\Render\RenderInterface;
use Avolle\WeeklyMatches\Services\ServicesConfig;
use Avolle\WeeklyMatches\Services\ServicesInterface;
use Avolle\WeeklyMatches\Validator\Validator;
use Avolle\WeeklyMatches\View\View;

/**
 * Class App
 *
 * @package Avolle\WeeklyMatches
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
     * @var \Avolle\WeeklyMatches\View\View
     */
    protected View $view;

    /**
     * Service to use in the app
     *
     * @var \Avolle\WeeklyMatches\Services\ServicesInterface
     */
    protected ServicesInterface $service;

    /**
     * Service's config
     *
     * @var \Avolle\WeeklyMatches\Services\ServicesConfig
     */
    protected ServicesConfig $serviceConfig;

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
     * @throws \Avolle\WeeklyMatches\Exception\InvalidRendererException
     * @throws \Avolle\WeeklyMatches\Exception\InvalidSportException
     * @throws \Avolle\WeeklyMatches\Exception\MissingSportConfigurationException
     * @throws \Avolle\WeeklyMatches\Exception\RuleNotFoundException
     * @throws \Avolle\WeeklyMatches\Exception\MissingViewException
     */
    public function run()
    {
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
     * @return \Avolle\WeeklyMatches\Services\ServicesInterface
     * @throws \Avolle\WeeklyMatches\Exception\InvalidSportException
     * @throws \Avolle\WeeklyMatches\Exception\MissingSportConfigurationException
     */
    protected function initService(string $sport): ServicesInterface
    {
        $serviceName = str_replace(' ', '', ucwords($sport));
        $serviceClassName = sprintf("Avolle\\WeeklyMatches\\Services\\%sService", $serviceName);

        if (!class_exists($serviceClassName)) {
            throw new InvalidSportException($serviceName . ' is not a valid sport.');
        }

        if (!isset($this->appConfig['services'][$sport])) {
            throw new MissingSportConfigurationException('Missing `' . $sport . '` Service configuration.');
        }
        if (!$this->appConfig['services'][$sport] instanceof ServicesConfig) {
            throw new MissingSportConfigurationException(
                'The `' . $sport . '` configuration must be instance of ' . ServicesConfig::class
            );
        }

        $this->serviceConfig = $this->appConfig['services'][$sport];

        return new $serviceClassName($this->serviceConfig);
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
     * @return \Avolle\WeeklyMatches\App
     * @throws \Avolle\WeeklyMatches\Exception\InvalidRendererException
     */
    protected function render(array $matches): self
    {
        $renderClass = $this->appConfig['renderClass'];

        if (!class_exists($renderClass)) {
            throw new InvalidRendererException($renderClass . ' is not a valid render class.');
        }

        $matches = collection($matches);

        $renderer = new $renderClass($matches);

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
     * @throws \Avolle\WeeklyMatches\Exception\RuleNotFoundException
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
    private function validServices()
    {
        return array_keys($this->appConfig['services']);
    }
}
