<?php

namespace PhingGitHub;

use BuildException;
use FileSet;
use Project;

/**
 * Tak to create one or more asset for a task by uploading the contents of files.
 *
 * @package PhingGitHub
 */
class CreateAssetsTask extends GitHubTask
{

    /**
     * @var int
     */
    protected $releaseId;

    /**
     * @var string
     */
    protected $releaseName;

    /**
     * @var FileSet[]
     */
    protected $filesets = array();

    /**
     * @param mixed $releaseId
     */
    public function setReleaseId($releaseId)
    {
        $this->releaseId = $releaseId;
    }

    /**
     * @param mixed $releaseName
     */
    public function setReleaseName($releaseName)
    {
        $this->releaseName = $releaseName;
    }

    public function createFileSet()
    {
        $num = array_push($this->filesets, new FileSet());
        return $this->filesets[$num-1];
    }

    /**
     * @inheritdoc
     */
    public function main()
    {
        $this->authenticate();

        if (!empty($this->releaseName)) {
            $this->releaseId = $this->getReleaseId($this->releaseName);
        }

        foreach ($this->filesets as $fileset) {
            $files = $fileset->getDirectoryScanner($this->project)->getIncludedFiles();
            foreach ($files as $file) {
                $filename = basename($file);
                $content = file_get_contents($file);

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $contentType = finfo_file($finfo, $file);
                if (empty($contentType)) {
                    $this->log('Unable to determine content type for file '. $file, Project::MSG_WARN);
                }

                $asset = $this->client->api('repo')->releases()->assets()->create(
                    $this->owner,
                    $this->repository,
                    $this->releaseId,
                    $filename,
                    $contentType,
                    $content
                );
                $this->log(sprintf('Created asset %s (ID# %d) for release ID %d', $asset['name'], $asset['id'], $this->releaseId));
            }
        }
    }

    /**
     * Determines the id of a release based on its name.
     *
     * @param string $releaseName Release name
     * @return int Release id
     * @throws \BuildException
     */
    protected function getReleaseId($releaseName)
    {
        $releaseId = null;
        $releaseNames = array();
        $releases = $this->client->api('repo')->releases()->all(
            $this->owner,
            $this->repository
        );
        foreach ($releases as $release) {
            if ($release['name'] == $releaseName) {
                $releaseId = $release['id'];
            }
            $releaseNames[] = $release['name'];
        }

        if (empty($releaseId)) {
            throw new BuildException(sprintf(
                'Unable to determine release id for name "%s". Valid release names: "%s"',
                $releaseName,
                implode('", "', $releaseNames)
            ));
        }

        return $releaseId;
    }
}
