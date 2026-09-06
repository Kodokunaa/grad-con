# Panelist requirements

This file records how the reviewed requirements map to the Laravel application.

| Requirement | Implementation |
| --- | --- |
| Improve spacing and interfaces | Authenticated pages use the shared responsive navbar/sidebar shell. The alumni community feed uses card spacing and mobile breakpoints. |
| Use “competencies” where appropriate | User-facing job and applicant labels use “competencies”; the legacy `skills` database column remains for upgrade compatibility. |
| Remove invitations to apply | The invitation-to-apply and accept/decline workflow remains removed. Employers may send a direct informational job-offer email from a professional alumni snapshot, as requested in the revised workflow. |
| Remove generated reviews | Applications show submitted information and employer decisions only. The system does not generate applicant reviews. |
| About Company | Employer profile and dashboard link to a single About Company area. |
| Application letter | Applicant uploads, admin/employer viewing, and forwarded email copy identify the private file as an application letter. |
| Applicant privacy | The employer Alumni List exposes professional directory fields only. Employers can send email to the account's stored address without seeing it. Contact details and an allowlisted application snapshot become available after the alumni applies. Home address, demographic data, special needs, and other private fields are excluded. |
| Complete course selection | Registration uses the configured eight City College of Calapan programs, including BLIS. |
| Remove redundant search/status text | The requested search helper sentence and “Registered Alumni” label were removed. |
| Email unsubscribe | Alumni can disable update emails in Profile. Automatic and manual job notifications query only subscribed alumni. Account/security mail remains available. |
| Specific job postings | Job title, type, dates, target course, description, location, and required competencies/qualifications are validated. |
| News, announcements, and events | Community posts require a category and display its category badge in the feed. Jobs remain in the opportunity rail. |
| Social alumni panel | Feed cards support reactions, comments, notification mentions, image lightbox viewing, and safe website link cards. |
| Collapsible navigation | Shared role navigation supports compact icon mode and keeps the same position and behavior across authenticated pages. |
| Archive posts | Admin and alumni officers can archive/restore authorized posts without deleting their history. |
| Reports from the database | The alumni report reads live database records and supports all, employed, unemployed, hired, course, and batch filters. |
| External social/RSS content | Website links can be shared and previewed safely. Importing private Facebook content requires a Page/API credential and is intentionally not enabled without one. |

## Upgrade notes

Historical offer tables and columns are left in existing databases so deployment does not destroy old records. No application route, controller, model, policy, navigation item, or email uses that workflow. The event category migration is additive and defaults existing posts to `announcement`.

Run `php artisan migrate --force` during deployment. After deploying, run `php artisan optimize:clear` followed by `php artisan optimize` and keep the queue worker active for email delivery.
