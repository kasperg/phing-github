<?php

namespace Phing\Github\Tasks;

use BuildException;
use FileSet;
use Project;

/**
 * Tak to create one or more asset for a task by uploading the contents of files.
 *
 * @package PhingGitHub
 */
class CreateReleaseTask extends GitHubTask
{

    /**
     * @var string
     */
    protected $tagName;

    /**
     * @var string
     */
    protected $commitish;

    /**
     * @var string
     */
    protected $releaseName;

    /**
     * @var string
     */
    protected $releaseBody;

    /**
     * @var bool
     */
    protected $draft = false;

    /**
     * @var bool
     */
    protected $prerelease = false;

    /**
     * @var int
     */
    protected $releaseId;

    /**
     * @inheritdoc
     */
    public function main()
    {
        // Convert properties to API parameters.
        $propertyMap = array(
            'tagName' => 'tag_name',
            'commitish' => 'target_commitish',
            'releaseName' => 'name',
            'releaseBody' => 'body',
            'draft' => 'draft',
            'prerelease' => 'prerelease',
        );
        $params = array();
        foreach ($propertyMap as $objectVar => $apiParam) {
            if (isset($this->$objectVar)) {
                $params[$apiParam] = $this->$objectVar;
            }
        }

        $this->authenticate();
        $release = $this->client->api('repo')->releases()->create(
            $this->owner,
            $this->repository,
            $params
        );
        $this->log(sprintf('Created release for tag %s (ID#: %d)', $release['tag_name'], $release['id']));

        if (!empty($this->releaseId)) {
            $this->project->setProperty($this->releaseId, $release['id']);
        }
    }

    /**
     * @param string $tagName
     */
    public function setTagName($tagName)
    {
        $this->tagName = $tagName;
    }

    /**
     * @param string $commitsh
     */
    public function setCommitish($commitsh)
    {
        $this->commitish = $commitsh;
    }

    /**
     * @param string $releaseName
     */
    public function setReleaseName($releaseName)
    {
        $this->releaseName = $releaseName;
    }

    /**
     * @param string $releaseBody
     */
    public function setReleaseBody($releaseBody)
    {
        $this->releaseBody = $releaseBody;
    }

    /**
     * @param boolean $draft
     */
    public function setDraft($draft)
    {
        $this->draft = $draft;
    }

    /**
     * @param boolean $prerelease
     */
    public function setPrerelease($prerelease)
    {
        $this->prerelease = $prerelease;
    }

    /**
     * @param int $releaseId
     */
    public function setReleaseId($releaseId)
    {
        $this->releaseId = $releaseId;
    }

}
