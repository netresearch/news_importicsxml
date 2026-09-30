# Contributing

When contributing to this repository, please first discuss the change you wish to make via issue,
email, or any other method with the owners of this repository before making a change.

## Getting Started

* Make sure you have a [GitHub account](https://github.com/signup/free)
* Submit a ticket for your [issue](https://github.com/netresearch/news_importicsxml/issues), assuming one does not already exist.
  * Clearly describe the issue including steps to reproduce when it is a bug.
* Fork the repository on GitHub

## Making Changes

* Create a topic branch from where you want to base your work.
  * This is usually the master branch.
  * Only target release branches if you are certain your fix must be on that
    branch.
  * To quickly create a topic branch based on master; `git checkout -b
    fix/master/my_contribution master`. Please avoid working directly on the
    `master` branch.
* Make commits of logical units.
* Use `composer ci:cgl` to make sure the code is formatted correctly (configuration: `Build/.php-cs-fixer.dist.php`).
* Write commit messages in the [Conventional Commits](https://www.conventionalcommits.org/) format, for example `feat:`, `fix:`, `docs:`, `chore:` or `ci:`

````
    docs: make the example in CONTRIBUTING imperative and concrete

    The first line is a real life imperative statement.
    The body describes the behavior without the patch,
    why this is a problem, and how the patch fixes the problem when applied.

    Resolves: #123
````

* Make sure you have added the necessary tests for your changes.
* Run _all_ the tests to assure nothing else was accidentally broken (see [Tests](#tests)). The CI workflow will do that for you as well.

## Tests

New functionality and bug fixes need tests: a pull request that adds or changes behaviour adds or changes a unit test under `Tests/Unit/` that fails without the change.

Run all checks locally with PHP 8.3 or 8.4 and the Xdebug extension (`Build/UnitTests.xml` writes a code coverage report):

```
composer install
composer ci:test
```

`composer ci:test` runs, in this order: PHP lint (`ci:test:php:lint`), PHPStan level 6 with the strict and deprecation rules (`ci:test:php:phpstan`), Rector and Fractor dry-runs (`ci:test:php:rector`, `ci:test:php:fractor`), the PHPUnit unit tests (`ci:test:php:unit`) and the code style check (`ci:test:php:cgl`). Each step can also be run on its own with the script name in brackets.

The unit tests cover the import configuration (`Tests/Unit/Domain/Model/Dto/TaskConfigurationTest.php`), the selection of the XML or ICS mapper and the error for an unsupported format (`Tests/Unit/Jobs/ImportJobTest.php`), and the labels of the console command's argument and options (`Tests/Unit/Command/ImportCommandTest.php`). The mappers themselves have no tests yet.

The workflow `.github/workflows/ci.yml` runs the same checks on every push and every pull request, for PHP 8.3 and 8.4 with TYPO3 13. Each check is its own step, so a red run names the failing check; PHPStan findings are also shown as annotations on the changed lines. PHPUnit lists each failing test with its assertion message and the file and line of the assertion.

## Governance and policies

This repository is Netresearch's fork of [georgringer/news_importicsxml](https://github.com/georgringer/news_importicsxml). The copyright of each contribution stays with its author; the code is licensed under GPL-2.0-or-later (`LICENSE.md`). The fork follows the organisation-wide policies of the `netresearch` GitHub organisation:

- [Governance](https://github.com/netresearch/.github/blob/main/GOVERNANCE.md): who decides whether a change is merged, how disagreements are resolved, and which roles (organisation owner, repository admin, maintainer, contributor) carry which responsibilities.
- [Roadmap](https://github.com/netresearch/.github/blob/main/ROADMAP.md): the maintenance work planned for the next twelve months and the work that is explicitly excluded.
- [Handling of dependency and code analysis findings](https://github.com/netresearch/.github/blob/main/SECURITY.md#handling-of-dependency-and-code-analysis-findings): which vulnerability, licence and static-analysis findings block a pull request, the remediation deadlines for the others, and how exceptions are recorded and reviewed.
- [Secret management](https://github.com/netresearch/.github/blob/main/SECURITY.md#secret-management): where CI and release secrets are stored, who can access them, and when they are rotated.
- [Access roster](https://github.com/netresearch/.github/blob/main/docs/access-roster.md): the accounts that hold admin, maintain or write access to the repositories and to the organisation.

Pull requests in this repository run the checks of `.github/workflows/ci.yml` (see [Tests](#tests)): PHP lint, code style, PHPStan, Rector and Fractor dry-runs and the unit tests. GitHub's CodeQL default setup analyses the workflow files (language `actions`) on pull requests, and GitHub secret scanning with push protection is enabled. The repository does not call the shared security workflows of `netresearch/.github`: no dependency review, Composer Audit or static analysis of the PHP code for security findings runs on pull requests. Dependabot is configured in `.github/dependabot.yml` to check the Composer dependencies daily; it has not opened a pull request in this repository so far.

The architecture, the security guarantees and limitations, and the threat model of the extension are described in [docs/SECURITY-ASSURANCE.md](docs/SECURITY-ASSURANCE.md). A pull request that adds an entry point, an external fetch, a new place where files are written or a new stored field updates that document.

## Making Trivial Changes

For changes of a trivial nature, it is not always necessary to create a new issue.

## Additional resources

* [Rendered documentation](https://docs.typo3.org/typo3cms/extensions/news_importicsxml/)
* [How to Write a Git Commit Message](http://chris.beams.io/posts/git-commit/)


## Contributor Code of Conduct

As contributors and maintainers of this project, and in the interest of fostering an open and
welcoming community, we pledge to respect all people who contribute through reporting issues,
posting feature requests, updating documentation, submitting pull requests or patches, and other
activities.

We are committed to making participation in this project a harassment-free experience for everyone,
regardless of level of experience, gender, gender identity and expression, sexual orientation,
disability, personal appearance, body size, race, ethnicity, age, religion, or nationality.

Examples of unacceptable behavior by participants include:

* The use of sexualized language or imagery
* Personal attacks
* Trolling or insulting/derogatory comments
* Public or private harassment
* Publishing other's private information, such as physical or electronic addresses, without explicit
  permission
* Other unethical or unprofessional conduct.

Project maintainers have the right and responsibility to remove, edit, or reject comments, commits,
code, wiki edits, issues, and other contributions that are not aligned to this Code of Conduct. By
adopting this Code of Conduct, project maintainers commit themselves to fairly and consistently
applying these principles to every aspect of managing this project. Project maintainers who do not
follow or enforce the Code of Conduct may be permanently removed from the project team.

This code of conduct applies both within project spaces and in public spaces when an individual is representing the project or its community.

Instances of abusive, harassing, or otherwise unacceptable behavior may be reported by opening an issue or contacting one or more of the project maintainers.

This Code of Conduct is adapted from the [Contributor Covenant](http://contributor-covenant.org),
version 1.2.0, available at [http://contributor-covenant.org/version/1/2/0/](http://contributor-covenant.org/version/1/2/0/)
