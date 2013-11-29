<?php

namespace PhingGitHub;

use ConfigurationException;
use Github\Client;
use Github\HttpClient\HttpClient;
use Guzzle\Log\ClosureLogAdapter;
use Guzzle\Plugin\Log\LogPlugin;
use Phing;
use Project;
use Task;

/**
 * Base task for all tasks dealing with GitHub integration.
 *
 * It handles common functionality like repository setting, authentication
 * and logging.
 *
 * @package PhingGitHub
 */
abstract class GitHubTask extends Task
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $owner;

    /**
     * @var string
     */
    protected $repository;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $authMethod;

    /**
     * @param string $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * @param mixed $repository
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @param mixed $authMethod
     */
    public function setAuthMethod($authMethod)
    {
        $this->authMethod = $authMethod;
    }


    public function init()
    {
        $httpClient = new HttpClient();

        // Setup logging.
        // Transfer GitHub API logging to the Phing log.
        $task = $this;
        $logPlugin = new LogPlugin(new ClosureLogAdapter(function ($message, $priority, $extras) use ($task) {
            $logLevelPriorities = array(
                LOG_EMERG => Project::MSG_ERR,
                LOG_ALERT => Project::MSG_ERR,
                LOG_CRIT => Project::MSG_ERR,
                LOG_ERR => Project::MSG_ERR,
                LOG_WARNING => Project::MSG_WARN,
                LOG_NOTICE => Project::MSG_INFO,
                LOG_INFO => Project::MSG_VERBOSE,
                LOG_DEBUG => Project::MSG_DEBUG,
            );
            $task->log($message, $logLevelPriorities[$priority]);
        }));
        $httpClient->client->addSubscriber($logPlugin);
        // Add the debug logger when the debug output level has been set.
        if (Phing::getMsgOutputLevel() == Project::MSG_DEBUG) {
            $httpClient->client->addSubscriber(LogPlugin::getDebugPlugin());
        }

        $this->client = new Client($httpClient);
    }

    /**
     * Add authentication to GitHub API requests.
     */
    protected function authenticate()
    {
        // Check if the enough attributes have been set to perform authentication.
        // Note that password is not required by all methods.
        if (empty($this->username) || empty($this->authMethod)) {
            throw new ConfigurationException(
                sprintf(
                    'Missing authentication parameters. Method: "%s", username "%s".',
                    $this->authMethod,
                    $this->username
                )
            );
        }

        // Make sure a valid authentication method has been set.
        $authMethodMap = array(
            'http-token' => Client::AUTH_HTTP_TOKEN,
            'password' => Client::AUTH_HTTP_PASSWORD,
            'client-id' => Client::AUTH_URL_CLIENT_ID,
            'url-token' => Client::AUTH_URL_TOKEN,
        );
        if (!isset($authMethodMap[$this->authMethod])) {
            throw new ConfigurationException(
                sprintf(
                    'Unknown authentication method "%s". Supported methods "%s"',
                    $this->authMethod,
                    implode('", "', array_keys($authMethodMap))
                )
            );
        }

        // Set authentication.
        $this->client->authenticate($this->username, $this->password, $authMethodMap[$this->authMethod]);
    }
}
