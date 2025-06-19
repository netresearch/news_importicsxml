# 7.0.0

## BREAKING

- 5d25dea [!!!][TASK] Remove Task in favor of command
- 1963545 [!!!][TASK] Change files of pdfs in xml
- fda76fd [!!!][TASK] Move from signal slot to event for newsimportaspect
- 92c7cfb [!!!][FEATURE] Disable content filtering for rss feeds

## FEATURE

- 1ade98b [FEATURE] ICS import: Category mapping to support multiple categories
- 93e400a [FEATURE] ICS import: Category mapping to support multiple categories
- df7e528 [FEATURE] Make the path configurable
- 9517c94 [FEATURE] Provide command
- 92c7cfb [!!!][FEATURE] Disable content filtering for rss feeds
- d48aa09 [FEATURE] Set slug after import
- ae32bf4 [FEATURE] Provide full event also for iccs
- ed4ae0f [FEATURE] Allow cleanup

## TASK

- 496d3fe [TASK] Update Manual Section 
- 5d25dea [!!!][TASK] Remove Task in favor of command
- 5178dda [TASK] Make it work with v12
- 2381638 [TASK] Followup 2
- 171c9f2 [TASK] Followup path change
- 1963545 [!!!][TASK] Change files of pdfs in xml
- 4b9d4d2 [TASK] Add teaser
- fda76fd [!!!][TASK] Move from signal slot to event for newsimportaspect
- ca7b336 [TASK] Raise version
- 52c2725 [TASK] streamline dependencies in ext_emconf.php and composer.json
- 1f616ef [TASK] Fix composer.json
- 9bbd9a5 [TASK] Finish upgrade
- 48e894d [TASK] Start upgrading using rector
- 52267b1 [TASK] Cleanup by real deletes
- a6d738d [TASK] Update extension icon

## BUGFIX

- f9116c4 [BUGFIX] ICS: Check for missing LOCATION field
- 2e8e242 [BUGFIX] Prevent Error during file cleanup
- c37c639 [BUGFIX] Handle ICS files without events gracefully
- e9f9d6f [BUGFIX] better timezone handling for ICS import
- dc62819 [BUGFIX] Fix notices in mapper
- be33299 [BUGFIX] Generate proper slug #40
- c53db43 [BUGFIX] Fix files import in xml mapper
- 91e5fe5 [BUGFIX] Use png as img
- c9063c7 [BUGFIX] Fix multiple files in xml
- ccbf24b [BUGFIX] fix dependeny constraints for georgringer/news (allow all versions in v9 branch) and typo3/cms-scheduler (allow all versions in v10.4 branch)
- 6d44c33 [BUGFIX] dependiencies error georgringer/news-importicsxml dev-master requires typo3/cms-scheduler 10.4 || ^11.5 -> found typo3/cms-scheduler[v10.4.0, v11.5.0, v11.5.1, v11.5.2] but the package is fixed to v10.4.21 (lock file version) by a partial
- 3d85c37 [BUGFIX] Replace deprecated curly brace syntax with square brackets
- 147c772 [BUGFIX] Set missing hidden field in XmlMapper
- 18e78a8 [BUGFIX] Correct TYPO3 dependencies in ext_emconf
- d2f80fd [BUGFIX] Use correct path
- c55f754 [BUGFIX] Fix regex expression for Ical import

## MISC

- 43fec02 Update README
- 063d7b0 Rework feed import
- 8adbfc7 Update underlying feed library
- 2852a4b Rework extension
- 7b36c2a Remove contrib directory
- 670213d Apply php-cs-fixer rules
- 4a035d8 Add missing method property/return types
- f9dd02d Fix abstract/interface usage
- f58e1e9 Use LoggerAwareInterface
- 8a8e68d Fix unittests
- 999832b Update TCA override
- 91d4daa Move form element to match core structure
- 18c2826 Apply rector rules
- 8fc0b7d Apply php-cs-fixer rules
- 9ea4ff6 Add dev tools
- 33dda41 Switch to https://github.com/u01jmg3/ics-parser Allows Linebreaks in ics
- 5bcf817 Switch to new version
- f23dd17 Parameter mapping delimiter: | instead of LF
- 5482e34 Update composer.json
- a5e056c Add return type
- 19412fa Update IcsMapper.php
- 32ef367 Update composer.json
- 552400b [DOC] update requirements in documentation
- 07509f9 TYPO3 10-11 compatibility
- d430b1f TYPO3 10-11 compatibility
- 7b96c9d TYPO3 10-11 compatibility

## Contributors

- Aristeidis
- Florian Langer
- Georg Ringer
- Georg Ringer
- Konstantin Berendakov
- Mark Houben
- Michael Wagner
- Rico Sonntag
- Sascha Schieferdecker
- Schlupp
- Yunus Kahveci
- areimund
- chris
- tkowtsch
- tudy

# 3.0.0

## FEATURE

- 07d7ba0 [FEATURE] Catgory mapping

## TASK

- a624a50 [TASK] Start with support 8+9
- 8ce5b62 [TASK] Followup

## BUGFIX

- 6274af4 [BUGFIX] Set default type

## MISC

- ae8e90c [DOC] Adopt readme

## Contributors

- Georg Ringer

# 2.1.1

## TASK

- 87a9f04 [TASK] Release
- c85f959 [TASK] Allow php7

## Contributors

- Georg Ringer

# 2.1.0

## FEATURE

- 953bb9a [FEATURE] Persist news as external

## MISC

- f4cca0b Apply fixes from StyleCI

## Contributors

- Georg Ringer

# 2.0.3

## TASK

- 3055b8f [TASK] 2.0.3
- d7efa44 [TASK] Disable json output

## BUGFIX

- a405b67 [BUGFIX] Fix wrong variable name

## Contributors

- Georg Ringer

# 2.0.2

## TASK

- 016b38d [TASK] Release 2.0.2

## Contributors

- Georg Ringer

# 2.0.1

## BUGFIX

- ba8094b [BUGFIX] Fix composer constraints

## Contributors

- Georg Ringer

# 2.0.0

## FEATURE

- 5d91bb0 [FEATURE] Use json render type
- 540a165 [FEATURE] Add image import

## TASK

- ceca8ed [TASK] Update images in manual
- 166a6c1 [TASK] Update description
- 8da25e9 [TASK] Update readme
- 98b9c20 [TASK] More cleanups
- 917bb88 [TASK] Rework
- d51da5c [TASK] Add styleci
- 1a4bd8d [TASK] More psr-2 changes
- aa3888b [TASK] Apply PSR-2 styles

## BUGFIX

- 1718619 [BUGFIX] Fix composer.json

## MISC

- a31da13 [DOC] Sponsors
- 7fb1c1f Apply fixes from StyleCI
- 0d35ece Apply fixes from StyleCI
- 1089d47 Apply fixes from StyleCI
- 403017c Apply fixes from StyleCI
- 94c265d Update XmlMapper.php
- c8512c8 Update ext_emconf.php
- b29e5e2 Update composer.json

## Contributors

- Georg Ringer
- Georg Ringer

# 1.0.1

## TASK

- 024d22d [TASK] Release 1.0.1
- 895a5d0 [TASK] Sponsors
- 7087ce2 [TASK] Followup
- 593bebd [TASK] add typo3 constraint
- 03f4adc [TASK] Followup
- 6f20b25 [TASK] Add picofeed directly
- 731cb34 [TASK] Rework
- d8e8325 [TASK] Metadata as readonly
- b29bf75 [TASK] Add import date
- c1d630a [TASK] Improve mappers
- 8be0cc7 [TASK] Add aspect for import
- f30c376 [TASK] Tests + cleanups
- 8e08ee6 [TASK] ICS/XML
- c0e7890 [TASK] Initial release

## MISC

- 851f3ab [DOC] Fix screenshot url
- e3457c3 [TASK} Unittests

## Contributors

- Georg Ringer

