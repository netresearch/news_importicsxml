.. SPDX-License-Identifier: GPL-2.0-or-later

.. |version| image:: https://img.shields.io/github/v/release/netresearch/news_importicsxml
   :target: https://github.com/netresearch/news_importicsxml/releases/latest
   :alt: Latest version
.. |license| image:: https://img.shields.io/github/license/netresearch/news_importicsxml
   :target: https://github.com/netresearch/news_importicsxml/blob/master/LICENSE.md
   :alt: License
.. |ci| image:: https://github.com/netresearch/news_importicsxml/actions/workflows/ci.yml/badge.svg
   :target: https://github.com/netresearch/news_importicsxml/actions/workflows/ci.yml
   :alt: CI

|version| |license| |ci|


TYPO3 CMS Extension "news_importicsxml"
=======================================
This extensions provides an import interface for `xml` files reached via URL and `ics` files which can either be located on the same server or reached via URL.
The import is done by the console command ``news:importicsxml``, which the scheduler can run periodically.

**Requirements**

- TYPO3 CMS 13.4 LTS
- EXT:news 12.3.0+

**Sponsors**

- Webconsulting https://webconsulting.at/
- TUM (Technical University of Munich) https://www.tum.de/
- Hochschule Darmstadt - University of Applied Sciences https://www.h-da.de/

Screenshots
^^^^^^^^^^^

Metadata of an imported ICS item:

.. figure:: Resources/Public/Documentation/screenshot-import-ics.png
		:alt: Metadata of an imported ics item

Important information for Upgrade from 6.0 to 7.0
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
The auto-generated ID for identifying an imported source has been changed for ICS files.
If you import existing items again, those will be duplicated one time.

Installation
------------

**Installation using Composer**

The recommended way to install the extension is by using composer. In your composer based TYPO3 project root, just do `composer require georgringer/news-importicsxml`.

**Installation as extension from TYPO3 Extension Repository (TER)**

Download and install the extension with the extension manager module.

Configuration
-------------
The import runs as the console command ``news:importicsxml`` (``Classes/Command/ImportCommand.php``).
Run it from the command line, or switch to the module **scheduler**, create a task of the class **Execute console commands** and select ``news:importicsxml``.
Example: ::

	vendor/bin/typo3 news:importicsxml https://typo3.org/xml-feeds/rss.xml --format=xml --pid=12 --mapping="5:Sports|17:Tech"

The command takes these arguments and options:

Format
""""""
Option ``--format``: either ``xml`` or ``ics``, in any letter case. Any other value stops the import with an error.

Path
""""
Argument ``path``: with ``--format=xml`` a URL like `https://typo3.org/xml-feeds/rss.xml` (the feed is always fetched over HTTP); with ``--format=ics`` a URL or a local path relative to the public directory like `fileadmin/data.ics`.

Page ID
"""""""
Option ``--pid``: the page id where the new records will be saved.

Category mapping
"""""""""""""""""""""""""""
A category mapping can be used to add categories to the news records based on categories found in the feed.

A typical feed item can look like this: ::

	<item>
		<title>A possible title</title>
		<link>http://www.examle.com/feed.xml</link>
		<description>Lorem ipsum</description>
		<content:encoded><![CDATA[A lot of text]]></content:encoded>
		<category>Sports</category>
		<category>Tech</category>
		<category>Information</category>
		<pubDate>Tue, 02 Jun 2015 16:54:00 +0200</pubDate>
	</item>

An ICS entry with a category can look like this: ::

	BEGIN:VCALENDAR
	PRODID:QIS-LSF HIS eG
	VERSION:2.0
	BEGIN:VTIMEZONE
	TZID:Europe/Berlin
	X-LIC-LOCATION:Europe/Berlin
	BEGIN:DAYLIGHT
	TZOFFSETFROM:+0100
	TZOFFSETTO:+0200
	TZNAME:CEST
	DTSTART:19700329T020000
	RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=3
	END:DAYLIGHT
	BEGIN:STANDARD
	TZOFFSETFROM:+0200
	TZOFFSETTO:+0100
	TZNAME:CET
	DTSTART:19701025T030000
	RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10
	END:STANDARD
	END:VTIMEZONE
	METHOD:PUBLISH
	BEGIN:VEVENT
	DTSTART;TZID=Europe/Berlin:20171016T141500
	DTEND;TZID=Europe/Berlin:20171016T154500
	RRULE:FREQ=WEEKLY;UNTIL=20171211T235900Z;INTERVAL=2;BYDAY=MO
	EXDATE;TZID=Europe/Berlin:
	LOCATION:C12 - C12 / 024
	DTSTAMP:20180216T113640Z
	UID:52965155936
	DESCRIPTION:
	SUMMARY:MK.1890a - Finite Berechnungsverfahren  Praktikum (A-Zug) (Eufinger)
	CATEGORIES:Praktikum
	END:VEVENT
	END:VCALENDAR

Option ``--mapping`` takes the category mapping. Separate the entries with ``|``; each entry is ``<category uid>:<category title>``.
A possible category mapping would look like this: ::

	5:Sports|17:Tech|17:Information

As a result, the imported news record will belong to the categories of the IDs *5 & 17*.

Email notification
""""""""""""""""""
Option ``--email``: an email address which will get notified after each run.

**Important**: This feature is not yet implemented!

Persist article with type external page
"""""""""""""""""""""""""""""""""""""""
Option ``--persistAsExternalUrl``: the news article is saved with the type "External Url". This applies to XML feeds only.

Generate the URL segment
""""""""""""""""""""""""
Option ``--slug``: EXT:news generates the URL segment (``path_segment``) of each imported record that has none yet.

Remove previously imported records
""""""""""""""""""""""""""""""""""
Option ``--cleanBeforeImport``: before the import, the records on the target page that an earlier import of the same format created are deleted.

Further information
^^^^^^^^^^^^^^^^^^^

Security
--------
`docs/SECURITY-ASSURANCE.md <docs/SECURITY-ASSURANCE.md>`_ describes the architecture of the extension, the security guarantees and limitations users can expect, the threat model and the countermeasures in the code.
Report vulnerabilities privately as described in the `security policy <https://github.com/netresearch/.github/blob/main/SECURITY.md>`_.

Debugging
---------
This extensions used the logging API of TYPO3 CMS. You can find some basic information in the log files (default `typo3temp/var/logs/typo3_****.log`).

Extending the import
--------------------
If you need to import additional data from a feed or ics item or you need to modify the data, use the Event Dispatching of the extension *news*.
Most likely, you will need the NewsImportPostHydrateEvent EventListener.

.. tip::

   Check the documentation of the news extension for a full list of available events: `https://docs.typo3.org/p/georgringer/news/main/en-us/Reference/Events/Index.html`

.. tip::

   Since TYPO3 v10, event dispatching is based on PSR-14 standard. See the core API docs to learn more details: `https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ApiOverview/Events/EventDispatcher/Index.html`

.. tip::

   An example on how to use the NewsImportPostHydrateEvent EventListener is available in `ext:eventnews`



