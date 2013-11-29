phing-github
============

[Phing](http://www.phing.info/) tasks for working with [GitHub](https://github.com/).

Currently supported:

* Creating [releases](https://github.com/blog/1547-release-your-software)
* Creating [release assets](https://github.com/blog/1547-release-your-software)

Pull requests with new tasks (updating releases, creating and closing pull requests etc.) are very welcome.

Usage
-----

1. Add `kasperg/phing-github` to `requires` in `composer.json`
2. Define task using [`<taskdef/>`](http://www.phing.info/docs/guide/stable/apbs31.html)
3. Use the task

See [example.build.xml](https://raw.github.com/kasperg/phing-github/master/example.build.xml) for how this can be done.

Extend
------

1. Implement tasks which subclass `PhingGitHub\GitHubTask`
2. Access [GitHub client](https://github.com/KnpLabs/php-github-api) class using `$this->client`
3. Call `$this->authenticate()` to support authentication
4. Update [example.build.xml](https://raw.github.com/kasperg/phing-github/master/example.build.xml)
