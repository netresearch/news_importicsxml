<!--
SPDX-FileCopyrightText: Netresearch DTT GmbH
SPDX-License-Identifier: GPL-2.0-or-later
-->

# Architecture and security assurance

This document describes how the TYPO3 extension `news_importicsxml` is built, which data it moves, what users can and cannot expect from it in terms of security, and how its code counters common weaknesses. It covers the code at the time of the last change of this file. Every statement names the file it is based on.

Vulnerabilities are reported privately as described in the [security policy of the `netresearch` organisation](https://github.com/netresearch/.github/blob/main/SECURITY.md).

## Architecture

### Actors

| Actor | What it does |
| --- | --- |
| Operator | A person with shell access to the TYPO3 installation, or a backend administrator. Runs the console command or creates the scheduler task, and chooses the feed location, target page, format and category mapping. The scheduler backend module is restricted to administrators (`access => 'admin'` in `typo3/cms-scheduler` `Configuration/Backend/Modules.php`). |
| Feed provider | Whoever controls the XML (RSS/Atom) or ICS file the operator configured: a remote web server or a local file. Supplies titles, texts, dates, categories, links and, for XML, enclosures. |
| Backend editor | Edits the imported news records in the TYPO3 backend and can see the stored import metadata if the field is granted to the editor's group. |
| TYPO3 scheduler / console | Executes `news:importicsxml` in the TYPO3 CLI context. |

### Components

| Component | File | Responsibility |
| --- | --- | --- |
| Console command | `Classes/Command/ImportCommand.php`, registered in `Configuration/Services.yaml` as `news:importicsxml` | Reads the `path` argument and the options into a `TaskConfiguration` and starts the import job. |
| Import configuration | `Classes/Domain/Model/Dto/TaskConfiguration.php` | Holds path, format, page id, category mapping (`uid:title` entries separated by `|`) and the flags. |
| Import job | `Classes/Jobs/ImportJob.php` | Selects the XML or ICS mapper by format, rejects any other format with an exception, and passes the mapped records to EXT:news. |
| XML mapper | `Classes/Mapper/XmlMapper.php` | Reads RSS/Atom feeds with `laminas/laminas-feed`, maps entries to news records, downloads enclosures whose declared MIME type is JPEG, GIF, PNG or PDF and saves them as files. |
| ICS mapper | `Classes/Mapper/IcsMapper.php` | Downloads remote ICS files to a temporary file (or reads a local path relative to the public directory), parses them with `johngrogg/ics-parser` and maps events to news records. |
| Shared mapper code | `Classes/Mapper/AbstractMapper.php` | Reads the extension configuration, removes earlier imported records when `--cleanBeforeImport` is set, logs through PSR-3. |
| Import listener | `Classes/EventListener/NewsImportListener.php` | On EXT:news' `NewsImportPostHydrateEvent`, stores the import metadata as JSON in the field `news_import_data` (`Classes/Domain/Model/News.php`, `ext_tables.sql`). |
| Backend form element | `Classes/Backend/Form/Element/JsonElement.php`, registered in `ext_localconf.php`; field defined in `Configuration/TCA/Overrides/tx_news_domain_model_news.php` | Shows `news_import_data` read-only in the news record form. |
| EXT:news import service | `GeorgRinger\News\Domain\Service\NewsImportService` (dependency `georgringer/news`) | Creates or updates the news records, categories, media and related files. |

### Data flow

1. The operator starts `news:importicsxml <path> --format=xml|ics --pid=<id>` from the shell or through the scheduler task "Execute console commands".
2. XML: `XmlMapper` passes the path to `Laminas\Feed\Reader\Reader::import()`, which fetches it with the `laminas/laminas-http` client. For each entry with an enclosure of one of these declared MIME types, the file is fetched with `GeneralUtility::getUrl()` and saved with `GeneralUtility::writeFile()`.
3. ICS: `IcsMapper` fetches an `http://` or `https://` path with `GeneralUtility::getUrl()` and writes it to a temporary file in the public `typo3temp/` directory, which it deletes after the events were mapped. Any other path is read as a file, relative to the public directory.
4. The mapper returns one array per entry or event. `ImportJob` hands the list to `NewsImportService::import()`, which writes the news records on the configured page.
5. `NewsImportListener` stores the import metadata (source URL, import date, entry identifiers and, for ICS, the raw event fields) as JSON in `news_import_data`.
6. An editor who opens the record sees the metadata rendered by `JsonElement`.

## What users can expect

- **Only privileged users start an import.** The extension has no frontend plugin, no route, no AJAX endpoint and no backend module of its own (`Configuration/Services.yaml`, `ext_localconf.php`). An import runs only as the console command, so it needs shell access or the administrator-only scheduler module.
- **Imported data goes through TYPO3 and EXT:news APIs.** News records are written by `NewsImportService` through Extbase persistence; the only direct database write of this extension, the removal of earlier imported records, uses Doctrine DBAL's `Connection::delete()` with an array of criteria, which DBAL (4.4.5 at the time of writing) turns into `column = ?` placeholders with bound values (`Classes/Mapper/AbstractMapper.php`).
- **Stored import metadata is shown escaped.** `JsonElement` decodes the JSON with `JSON_THROW_ON_ERROR` and renders it with `DebugUtility::viewArray()`, which uses Extbase's `DebuggerUtility` and escapes values with `htmlspecialchars()`. The field is read-only and marked `exclude`, so editors see it only when their group is granted the field (`Configuration/TCA/Overrides/tx_news_domain_model_news.php`).
- **XML feeds with a DOCTYPE are rejected.** `laminas/laminas-feed` (2.26.2 at the time of writing) parses the feed without entity substitution and refuses any document that contains a DOCTYPE node before an entry is mapped (`Reader::importString()`), so the feed cannot declare external entities or entity expansions.
- **Unsupported formats stop the import.** `ImportJob::run()` throws an `UnexpectedValueException` for any format other than `xml` or `ics` (tested in `Tests/Unit/Jobs/ImportJobTest.php`).

## What users cannot expect

- **The extension does not judge the feed content.** Titles, texts, links, categories and enclosures are taken from the feed as delivered, and the imported records are published unless the page or EXT:news settings prevent it (`hidden` is set to `0` in both mappers). Configure only feeds whose provider, and the network path to it, you trust with your site, and use `https://` URLs.
- **No authentication towards the feed.** The command has no option for credentials or client certificates.
- **Network settings differ between the two formats.** ICS files and XML enclosures are fetched with `GeneralUtility::getUrl()`, which uses TYPO3's HTTP settings (`$GLOBALS['TYPO3_CONF_VARS']['HTTP']`). XML feeds are fetched by the `laminas/laminas-http` client, which does not read these settings, for example a configured proxy.
- **Local paths are not restricted.** A local path is resolved relative to the public directory without further checks (`IcsMapper::getFileContent()`); the operator who configures the path is trusted with it.
- **Email notification does not exist.** The `--email` option is only written to the log (`ImportJob::run()`).
- **The temporary ICS copy can remain.** If parsing fails with an exception, the temporary file in the public `typo3temp/` directory is not deleted.

## Threat model and trust boundaries

| Boundary | Trusted side | Untrusted side | Relevant code |
| --- | --- | --- | --- |
| Operator input | Operator with shell or scheduler access | none; this input is trusted | `ImportCommand.php`, `TaskConfiguration.php` |
| Feed content | TYPO3 installation | Feed provider and the network between it and the server | `XmlMapper.php`, `IcsMapper.php`, `laminas/laminas-feed`, `johngrogg/ics-parser` |
| Stored metadata | TYPO3 backend | Values that originate from the feed | `NewsImportListener.php`, `JsonElement.php` |

Threats considered: a feed provider or network attacker who injects markup or script into the feed, a feed that uses XML entities to read files or exhaust memory, and a manipulated feed entry that tries to reach the database directly. The countermeasures are listed in the next two sections. Threats outside the scope of this extension: access to the shell or to an administrator account, and the security of TYPO3, EXT:news and the rendering templates of the site.

## Secure design principles applied

- **Least privilege:** no entry point for unauthenticated users or ordinary editors; the only entry point is a console command.
- **Complete mediation of the database:** all record writes go through `NewsImportService` and Extbase, and the one direct delete uses bound parameters.
- **Fail closed:** an unknown format, an unreadable local ICS path, an empty remote ICS response or invalid JSON in the stored metadata raise an exception instead of continuing (`ImportJob.php`, `IcsMapper.php`, `JsonElement.php`).
- **Economy of mechanism:** parsing of XML and ICS is left to maintained libraries (`composer.json`), not to code in this extension.
- **Strict typing and static analysis:** every PHP file declares `strict_types=1` (enforced by `Build/.php-cs-fixer.dist.php`), and PHPStan runs at level 6 with the strict and deprecation rules on every pull request (`Build/phpstan.neon`, `.github/workflows/ci.yml`).

## Countermeasures against common weaknesses

| Weakness | Countermeasure | Where |
| --- | --- | --- |
| CWE-89 SQL injection (OWASP A03) | Records are written through Extbase; the delete uses DBAL parameter binding. | `AbstractMapper::removeImportedRecordsFromPid()`, `NewsImportService` |
| CWE-79 Cross-site scripting in the backend (OWASP A03) | Import metadata is rendered through `DebuggerUtility`, which escapes values. How the imported texts appear in the frontend is decided by the EXT:news templates of the site. | `JsonElement.php` |
| CWE-611 XML external entities, CWE-776 entity expansion (OWASP A05) | The feed is parsed without entity substitution, and documents with a DOCTYPE are rejected by `laminas/laminas-feed`. | `XmlMapper::map()` via `Reader::import()` |
| CWE-862 Missing authorization (OWASP A01) | No web entry point; the scheduler module is administrator-only. | `Configuration/Services.yaml`, `ext_localconf.php` |
| CWE-20 Improper input validation | The format is checked against a fixed list before any mapper runs. | `ImportJob::run()` |
| CWE-1104 Use of unmaintained third-party components (OWASP A06) | Dependencies are declared with version ranges in `composer.json` and no lock file is committed, so every CI run installs the newest versions the ranges allow. Dependabot is configured to check the Composer dependencies daily. | `composer.json`, `.gitignore`, `.github/dependabot.yml` |

## Keeping this document current

A pull request that adds an entry point, a new external fetch, a new place where files are written or a new stored field updates this document in the same pull request.
