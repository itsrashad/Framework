<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\ServiceManager;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\StdLib\SingletonTrait;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;

/**
 * ServiceManager is the main class for working with services.
 * @package         Webiny\Component\ServiceManager
 */
class ServiceManager
{
    use StdLibTrait, SingletonTrait;

    private $compiledConfig;

    /**
     * @var ArrayObject
     */
    private $instantiatedServices;

    /**
     * @var ArrayObject
     */
    private $registeredServices;
    private $parameters;
    private $taggedServices;
    private $references;

    /**
     * Get service instance by given name nad optional arguments
     *
     * @param string $serviceName Requested service name
     *
     * @throws ServiceManagerException
     * @return object
     */
    public function getService($serviceName)
    {
        $serviceName = $this->str($serviceName)->trimLeft("@")->val();
        if (!$this->registeredServices->keyExists($serviceName)) {
            throw new ServiceManagerException(ServiceManagerException::SERVICE_DEFINITION_NOT_FOUND, [$serviceName]);
        }

        // Instantiate new service or get existing service instance
        if (!$this->instantiatedServices->keyExists($serviceName)) {
            $service = $this->instantiateService($serviceName);
        } else {
            $service = $this->instantiatedServices->key($serviceName);
        }

        return $service;
    }

    /**
     * Get multiple services by tag
     *
     * @param string      $tag Tag to use for services filter
     * @param null|string $forceType (Optional) Return only services which are instances of $forceType
     *
     * @return array
     */
    public function getServicesByTag($tag, $forceType = null)
    {
        $services = [];
        foreach ($this->taggedServices->key($tag, [], true) as $serviceName) {
            $service = $this->getService($serviceName);
            if (!$this->isNull($forceType) && !$this->isInstanceOf($service, $forceType)) {
                continue;
            }
            $services[$serviceName] = $service;
        }

        return $services;
    }

    /**
     * Register service using given config
     *
     * @param string       $serviceName
     * @param ConfigObject $config
     *
     * @param bool         $overwrite Overwrite service if it has been registered before (Default: false)
     *
     * @throws ServiceManagerException
     * @return $this
     */
    public function registerService($serviceName, ConfigObject $config, $overwrite = false)
    {
        /**
         * Check if service instance already exists
         */
        if ($this->registeredServices->keyExists($serviceName) && !$overwrite) {
            throw new ServiceManagerException(ServiceManagerException::SERVICE_NAME_ALREADY_EXISTS, [$serviceName]);
        }
        $this->registeredServices[$serviceName] = $config;

        if ($this->instantiatedServices->keyExists($serviceName) && $overwrite) {
            $this->instantiatedServices->removeKey($serviceName);
        }

        /**
         * Tagify service
         */
        foreach ($config->get('Tags', []) as $tag) {
            $tagServices = $this->taggedServices->key($tag, [], true);
            $tagServices[] = $serviceName;
            $this->taggedServices->key($tag, $tagServices);
        }

        return $this;
    }

    /**
     * Register given services under given service group
     *
     * @param string       $serviceGroup
     * @param ConfigObject $config
     * @param bool         $overwrite Overwrite service if it has been registered before (Default: false)
     */
    public function registerServices($serviceGroup, ConfigObject $config, $overwrite = false)
    {
        foreach ($config as $serviceKey => $serviceConfig) {
            $this->registerService($serviceGroup . '.' . $serviceKey, $serviceConfig, $overwrite);
        }
    }

    /**
     * Register parameter for use in service configs
     *
     * @param string $name Parameter name
     * @param mixed  $value Parameter value
     *
     * @return $this
     */
    public function registerParameter($name, $value)
    {
        $this->parameters->key($name, $value);

        return $this;
    }

    /**
     * Register multiple parameters for use in service configs
     *
     * @param ArrayObject|array $parameters Array of key => value parameter names and values
     *
     * @return $this
     */
    public function registerParameters($parameters = [])
    {
        foreach ($parameters as $name => $value) {
            $this->registerParameter($name, $value);
        }

        return $this;
    }

    /**
     * Get registered service config
     *
     * @param $serviceName
     *
     * @throws ServiceManagerException
     * @return ConfigObject
     */
    public function getServiceConfig($serviceName)
    {
        if (!$this->registeredServices->keyExists($serviceName)) {
            throw new ServiceManagerException(ServiceManagerException::SERVICE_DEFINITION_NOT_FOUND, [$serviceName]);
        }

        return $this->registeredServices[$serviceName];
    }

    /**
     * Initialize ServiceManager
     */
    protected function init()
    {
        $this->instantiatedServices = $this->arr();
        $this->registeredServices = $this->arr();
        $this->compiledConfig = $this->arr();
        $this->parameters = $this->arr();
        $this->references = $this->arr();
        $this->taggedServices = $this->arr();
    }

    /**
     * Instantiate service using given service name
     *
     * @param string $serviceName
     *
     * @return object
     * @throws ServiceManagerException
     */
    private function instantiateService($serviceName)
    {
        // Make sure service is registered
        if (!$this->registeredServices->keyExists($serviceName)) {
            throw new ServiceManagerException(ServiceManagerException::SERVICE_DEFINITION_NOT_FOUND, [$serviceName]);
        }

        // Get service config from registered services array
        $config = $this->registeredServices->key($serviceName);

        // Check circular referencing
        if ($this->references->keyExists($serviceName)) {
            throw new ServiceManagerException(ServiceManagerException::SERVICE_CIRCULAR_REFERENCE, [$serviceName]);
        }

        // Set service name reference for circular referencing checks
        $this->references->key($serviceName, $serviceName);

        // Compile ConfigObject into ServiceConfig
        $configCompiler = new ConfigCompiler($serviceName, $config, $this->parameters);
        $this->compiledConfig->key($serviceName, $configCompiler->compile());

        /**
         * @var $config ServiceConfig
         */
        $config = $this->compiledConfig->key($serviceName);

        // Construct service container and get service instance
        $serviceCreator = new ServiceCreator($config);
        $service = $serviceCreator->getService();

        // Unset service name reference
        $this->references->removeKey($serviceName);

        // Store instance if this service has a CONTAINER scope
        if ($config->getScope() == ServiceScope::CONTAINER) {
            $this->instantiatedServices->key($serviceName, $service);
        }

        return $service;
    }
}