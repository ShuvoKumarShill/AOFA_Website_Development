# AI AGENT TASK — COMPLETE ARCHIVED WEBSITE DATA RECOVERY

## 1. ROLE

You are an expert **Web Archive Researcher, Web Scraping Agent, Content Migration Specialist, and Information Architect**.

Your task is to recover, document, organize, and preserve **all usable information that previously existed on the `aofabd.com` website**, using the Internet Archive as the primary source.

The old website is no longer active/available, so the objective is to reconstruct its information architecture and content as accurately and completely as possible for use in a **new website**.

---

# 2. PRIMARY ARCHIVE SOURCE

Archived website:

https://web.archive.org/web/20260210053148/https://aofabd.com/

Original domain:

https://aofabd.com/

Archive snapshot:

**10 February 2026 — 05:31:48 UTC**

Treat the archived website and its archived resources as the source of truth.

---

# 3. MAIN OBJECTIVE

Recover **ALL AVAILABLE WEBSITE DATA** that existed on the `aofabd.com` domain.

Do NOT limit the investigation to the homepage.

Systematically discover and document:

* Homepage
* About pages
* Organization information
* Mission / vision / objectives
* Programs
* Projects
* Services
* Activities
* Events
* News
* Notices
* Announcements
* Publications
* Reports
* Articles
* Blog posts
* Documents
* PDFs
* Downloads
* Forms
* Contact information
* Addresses
* Telephone numbers
* Email addresses
* Social media links
* Staff/team information
* Partner information
* Gallery pages
* Image galleries
* Videos
* Audio
* Embedded content
* FAQs
* Frequently linked resources
* Menus
* Submenus
* Categories
* Tags
* Internal links
* External links
* Archived files
* Logos
* Important images
* Documents referenced by the website
* Any other content discoverable from the archived domain

---

# 4. IMPORTANT: COMPLETENESS REQUIREMENT

Your goal is **maximum possible data recovery**, not merely a summary.

Do not stop after collecting the visible homepage.

You must recursively investigate the archived website.

For every discovered page:

1. Extract the page URL.
2. Extract the original URL if available.
3. Extract the page title.
4. Extract all meaningful textual content.
5. Extract headings.
6. Extract navigation/menu items.
7. Extract internal links.
8. Extract external links.
9. Identify downloadable files.
10. Identify images and their original URLs.
11. Identify PDFs and other documents.
12. Identify metadata where available.
13. Follow relevant internal links.
14. Check whether linked resources are also archived.
15. Record unavailable resources rather than silently ignoring them.

Continue until no additional relevant pages/resources can reasonably be discovered.

---

# 5. DOMAIN SCOPE

Primary scope:

`aofabd.com`

Include:

* `https://aofabd.com/*`
* `http://aofabd.com/*`
* `www.aofabd.com/*`
* archived variations of the above
* files hosted directly under the domain
* documents/files linked from the domain
* relevant subdomains if they clearly belonged to the website

Do NOT automatically include unrelated third-party websites.

However, record important external resources as **external references**, especially:

* Facebook
* YouTube
* LinkedIn
* X/Twitter
* Government websites
* Partner organizations
* Donor organizations
* External document repositories

---

# 6. ARCHIVE DISCOVERY STRATEGY

Use the Internet Archive systematically.

Start with the supplied snapshot:

https://web.archive.org/web/20260210053148/https://aofabd.com/

Then investigate:

* Homepage
* All navigation menus
* All submenu items
* Footer links
* Sitemap links
* Breadcrumbs
* Category pages
* Archive pages
* Search pages where available
* Pagination
* Individual content pages
* Attachment links
* PDF links
* Image links
* Download links
* RSS/Atom feeds if available
* XML sitemap if archived
* robots.txt if archived
* WordPress-related paths if applicable
* CMS-generated URLs if identifiable

Where possible, inspect additional archived captures of the same URL if the primary snapshot is incomplete.

The supplied snapshot is the starting point, not necessarily the only archive capture that may be used.

---

# 7. WEBSITE STRUCTURE RECONSTRUCTION

Reconstruct the original information architecture.

Create a hierarchy similar to:

```text
AOFABD
│
├── Home
├── About
│   ├── Overview
│   ├── History
│   ├── Mission
│   └── Vision
│
├── Programs
│   ├── Program A
│   ├── Program B
│   └── Program C
│
├── Projects
│
├── News
│
├── Events
│
├── Publications
│
├── Reports
│
├── Gallery
│
├── Resources
│
└── Contact
```

Do NOT invent sections.

The actual hierarchy must be derived from the archived website.

---

# 8. CONTENT EXTRACTION

For each page, capture the complete meaningful content.

Extract:

### Basic information

* Original URL
* Archived URL
* Page title
* Page type
* Parent section
* Publication date
* Last updated date if available
* Author if available
* Category
* Tags

### Text

Capture:

* Headings
* Subheadings
* Paragraphs
* Lists
* Tables
* Captions
* Quotes
* Important labels
* Button text
* Calls to action

Preserve the original wording as much as possible.

Do not rewrite content during extraction.

---

# 9. DOCUMENT RECOVERY

Identify every document referenced by the archived website.

Especially search for:

* PDF
* DOC
* DOCX
* XLS
* XLSX
* PPT
* PPTX
* ZIP
* CSV
* TXT
* JPG/JPEG
* PNG
* WebP
* SVG

For every document, record:

```text
Document title:
Original URL:
Archived URL:
File type:
File size if available:
Associated page:
Publication date:
Description:
Archive availability:
Recovery status:
```

If the actual file can be recovered, preserve it.

If the file cannot be recovered, record:

`NOT RECOVERED`

Do not fabricate missing documents.

---

# 10. IMAGE RECOVERY

Identify important images used by the website.

For each image record:

* Image URL
* Archived URL
* Filename
* Alt text
* Caption
* Associated page
* Approximate purpose
* Recovery status

Prioritize:

* Logo
* Organization branding
* Header images
* Program/project images
* Staff photos
* Event photos
* Gallery images
* Infographics
* Important diagrams

Avoid collecting meaningless duplicate thumbnails unless necessary.

---

# 11. PEOPLE AND ORGANIZATIONS

Extract proper names exactly as they appeared.

Examples of information to capture:

* Founder
* Chairman
* Director
* Executive Director
* Board members
* Staff
* Advisors
* Coordinators
* Project personnel
* Partner organizations
* Donors
* Government agencies
* Institutions

For each person:

```text
Name:
Position:
Department/Organization:
Biography:
Associated page:
Source URL:
```

Do not guess identities or positions.

---

# 12. CONTACT INFORMATION

Create a dedicated contact-information dataset.

Extract:

* Organization name
* Office address
* Mailing address
* Telephone
* Mobile
* Fax
* Email
* Website
* Social media
* Office hours
* Contact person
* Map/location information

If different pages contain different contact details, preserve each occurrence and flag discrepancies.

---

# 13. NEWS / EVENTS / ARTICLES

For every news article, event, announcement, or publication, capture:

```text
Title
Date
Author
Category
Summary
Full content
Featured image
Attachments
Related links
Original URL
Archived URL
```

Do not summarize if the full content is recoverable.

The full original content should be preserved for migration.

---

# 14. LINKS AND REFERENCES

Build an internal link inventory.

For every discovered link:

```text
Source page
Link text
Destination URL
Internal / External
Archived / Not archived
HTTP status if determinable
Content type
Recovery status
```

Pay special attention to links that lead to documents.

---

# 15. DUPLICATE HANDLING

The same content may appear:

* On multiple archive snapshots
* Under different URLs
* With HTTP/HTTPS
* With/without `www`
* With query parameters
* In category/archive pages
* In mobile versions
* As PDF and HTML

Detect duplicates.

Keep the best-quality version and record duplicate URLs separately.

Do not delete evidence of duplicates.

---

# 16. MISSING OR BROKEN CONTENT

If something existed on the old website but cannot be recovered:

Record it explicitly.

Example:

```text
Resource: Annual Report 2024
Original URL: /documents/annual-report-2024.pdf
Archive status: Referenced by archived page
Recovery status: FILE NOT AVAILABLE
Evidence: Link existed on archived page
```

Never replace missing information with assumptions.

Never hallucinate missing content.

---

# 17. DATA NORMALIZATION

After raw collection, create a clean structured dataset.

Normalize:

* URLs
* Dates
* Names
* Categories
* Document types
* Page types
* Duplicate content
* Navigation hierarchy

However, **do not modify the original wording of source content**.

Keep both:

### RAW CONTENT

Exact recovered content.

### NORMALIZED DATA

Structured information suitable for the new website.

---

# 18. NEW WEBSITE MIGRATION STRUCTURE

Prepare the recovered information so it can later be imported into a modern CMS.

Recommended structure:

```text
/pages
/posts
/news
/events
/programs
/projects
/publications
/reports
/documents
/images
/gallery
/people
/organizations
/contact
/navigation
```

For each content item, provide a stable ID.

Example:

```text
AOFABD-PAGE-001
AOFABD-NEWS-001
AOFABD-EVENT-001
AOFABD-DOC-001
AOFABD-PERSON-001
```

---

# 19. REQUIRED OUTPUT DATASETS

Produce the following datasets.

## Dataset 1 — Complete URL Inventory

Columns:

```text
ID
Original URL
Archived URL
Page Title
Page Type
Parent Section
Status
Content Recovered
Document Available
Image Available
Notes
```

---

## Dataset 2 — Page Content

Columns:

```text
ID
URL
Title
Date
Author
Category
Headings
Full Content
Images
Attachments
Related Links
Source
```

---

## Dataset 3 — Documents

Columns:

```text
ID
Document Name
Document Type
Original URL
Archived URL
Associated Page
Date
Description
Recovered
Notes
```

---

## Dataset 4 — Images

Columns:

```text
ID
Filename
Original URL
Archived URL
Alt Text
Caption
Associated Page
Recovered
Notes
```

---

## Dataset 5 — People

Columns:

```text
ID
Name
Position
Organization
Biography
Source URL
Notes
```

---

## Dataset 6 — Organizations

Columns:

```text
ID
Organization Name
Type
Description
Relationship to AOFABD
Website
Source URL
Notes
```

---

## Dataset 7 — Navigation

Reconstruct:

```text
Menu
Submenu
Page
URL
Order
Parent
```

---

## Dataset 8 — External References

```text
Source Page
Link Text
External URL
Organization
Purpose
Archive Status
Notes
```

---

## Dataset 9 — Missing Resources

```text
Resource
Original URL
Referenced From
Expected Type
Archive Status
Recovery Status
Possible Alternative
Notes
```

---

# 20. SOURCE TRACEABILITY

Every recovered piece of information must be traceable to its source.

Never provide content without source information.

Each record should contain:

```text
Source URL
Archive URL
Capture date
Extraction date
```

If information was recovered from a different archive snapshot than the primary snapshot, record that explicitly.

---

# 21. QUALITY CONTROL

Before finishing, perform a second-pass audit.

Check:

### Coverage

* Did you inspect every navigation item?
* Did you inspect every submenu?
* Did you inspect footer links?
* Did you inspect category pages?
* Did you inspect pagination?
* Did you inspect downloadable documents?
* Did you inspect images?
* Did you inspect external references?
* Did you inspect archived alternative captures?

### Accuracy

* Are names copied correctly?
* Are dates correct?
* Are URLs preserved?
* Are documents correctly associated?
* Are duplicate pages identified?
* Are missing resources clearly marked?

### Completeness

Create a final report containing:

```text
Total URLs discovered:
Total pages recovered:
Total documents discovered:
Total documents recovered:
Total images discovered:
Total images recovered:
Total people identified:
Total organizations identified:
Total news articles:
Total events:
Total publications:
Total missing resources:
Total external references:
```

---

# 22. DO NOT HALLUCINATE

This is extremely important.

If information cannot be verified from the archive:

* Do not invent it.
* Do not infer it as fact.
* Do not create fake URLs.
* Do not create fake people.
* Do not create fake documents.
* Do not create fake dates.
* Do not reconstruct missing paragraphs from assumptions.

Use:

`UNKNOWN`

or

`NOT RECOVERED`

when appropriate.

---

# 23. PRESERVE ORIGINAL MEANING

The purpose of this project is **website migration**, not rewriting.

Therefore:

* Preserve original text.
* Preserve original terminology.
* Preserve proper names.
* Preserve dates.
* Preserve titles.
* Preserve organizational names.
* Preserve historical information.
* Preserve document titles.
* Preserve original URLs.

Only normalize formatting where necessary.

Do not modernize or rewrite the organization's historical content during the extraction stage.

---

# 24. FINAL DELIVERABLE

At the end, produce a complete **AOFABD Website Recovery & Migration Package** containing:

```text
01_URL_INVENTORY
02_PAGE_CONTENT
03_NEWS
04_EVENTS
05_PROGRAMS
06_PROJECTS
07_PUBLICATIONS
08_DOCUMENTS
09_IMAGES
10_PEOPLE
11_ORGANIZATIONS
12_CONTACT_INFORMATION
13_NAVIGATION_STRUCTURE
14_EXTERNAL_LINKS
15_MISSING_RESOURCES
16_DUPLICATES
17_MIGRATION_MAPPING
18_SOURCE_LOG
19_FINAL_AUDIT_REPORT
```

Preferred machine-readable formats:

* CSV
* JSON
* Markdown
* HTML

If possible, also produce a structured folder containing recovered files.

---

# 25. MIGRATION MAPPING

Finally, recommend how the recovered old website should map to the new website.

Create a table:

```text
Old URL
Old Page Title
Old Content Type
New URL
New Content Type
Migration Action
Priority
Notes
```

Migration actions should be one of:

```text
KEEP
MIGRATE
MERGE
REDIRECT
ARCHIVE
REVIEW
NOT RECOVERED
```

Do not make final editorial decisions without evidence.

---

# 26. EXECUTION PRINCIPLE

Work in multiple passes:

### PASS 1 — DISCOVERY

Find every possible page/resource.

### PASS 2 — EXTRACTION

Extract complete content and metadata.

### PASS 3 — DOCUMENT & MEDIA RECOVERY

Recover PDFs, files, images and other resources.

### PASS 4 — STRUCTURE RECONSTRUCTION

Rebuild the original website hierarchy.

### PASS 5 — NORMALIZATION

Convert the recovered information into structured datasets.

### PASS 6 — QUALITY CONTROL

Check completeness, duplicates, missing resources and source traceability.

### PASS 7 — MIGRATION PREPARATION

Prepare clean data for the new website/CMS.

---

# 27. SUCCESS CRITERIA

The task is complete only when you can confidently answer:

> "What information existed on aofabd.com, where did it exist, what content can be recovered, what files existed, what cannot be recovered, and how should everything be migrated to the new website?"

The final output must therefore be a **comprehensive archival reconstruction**, not a simple website summary.

Start from:

https://web.archive.org/web/20260210053148/https://aofabd.com/

and systematically work through the archived `aofabd.com` domain.
