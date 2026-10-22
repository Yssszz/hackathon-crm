# AI Assistant for the CS Euro Auto CRM (hackathon prototype)

A simple AI Assistant added to the existing Opportunity page of a SuiteCRM-based dealership CRM. The CRM, its pages and its workflow are unchanged; the assistant is added on top through SuiteCRM's `custom/` mechanism.

## What was added

On the Opportunity detail page, above the normal tabs:

1. **AI Summary** button: a short summary of the customer, vehicle, budget, stage and recent activity.
2. **AI Recommendation**: the recommended next action, a priority (High / Medium / Low) and a short reason.
3. **Generate Follow-up Email**: subject and body for the current stage. The salesperson can edit it, Copy it and Regenerate it. Nothing is sent automatically.
4. **AI Follow-up Suggestions** (Home dashboard): opportunities that need attention (red = urgent, yellow = soon). Each row opens the Opportunity.

Also added:
- A **Product** field on Opportunities (the video shows a product per opportunity).
- A **modern look** for the whole CRM, not only a few pages (`ai-assistant/custom/themes/modern/`):
  - `modern.css`: bundled Inter font, clean navigation, white cards with soft shadows, rounded buttons, restyled forms, subpanels, tables, calendar, administration, search, user profile, modals and a redesigned login page.
  - `modern.js`: colour-coded **sales-stage pills** (lead, evaluation, deal, delivery, done, refund) in lists and detail pages. It only wraps text; it never changes data.
  - It is a style layer loaded through SuiteCRM's supported template override (`custom/themes/SuiteP/tpls/`), so no core file is changed. Delete `runtime/suitecrm/custom/themes/` to go back to the stock look.
  - `runtime/php/php.ini` now has `display_errors = Off`, so PHP warnings no longer appear on screen (they are written to `runtime/php-errors.log`).

## Run

Double-click `start.cmd`, then open:

| What | Address | Login |
|---|---|---|
| CRM (SuiteCRM 7.15.2) | http://localhost:8080 | `admin` / `Admin#2026` |
| Dev pipeline demo (secondary) | http://localhost:3000 | none |

Demo users: `gcr_dev3` (sales consultant) and `bm_butterworth` (branch manager), password `Demo#2026`.

## Demo script (about 3 minutes)

1. Log in as `admin`. The Home dashboard shows **AI Follow-up Suggestions**.
2. Click **Tan Wei Ming** (or open `GCR DEMO TEST OPP12` from Opportunities).
3. Point out the normal CRM page below the panel: stage, amount, description, tabs.
4. Click **AI Summary**: summary, recommendation and reason appear in under a second.
5. Click **Generate Follow-up Email**, edit a line, press **Regenerate**, then **Copy**.
6. Close with the before/after: staff used to read everything, decide, and write the email by hand; now it takes one click.

Other seeded customers show different recommendations: Nurul Huda (Proton X50, no activity), Siti Aminah (test drive tomorrow), Wong Mei Ling (test drive completed), Faizal (booking info pending), Raju (healthy deal).

## How the AI works, honestly

- **Without an API key (default): offline mode.** The panel says "Offline mode (rules, no LLM)". The summary, recommendation and email are produced by rules over the real CRM record (stage, contact, amount, activities, dates). No language model is involved.
- **With a key: Claude.** Set `ANTHROPIC_API_KEY` in the terminal before running `start.cmd`. The same data is sent to Claude (`claude-sonnet-5-5`), the panel says "Powered by Claude", and any failed call falls back to the rules. This path is wired and the connection to the API was tested, but it has not been run end to end with a real key.

## What is real and what is not

Real: SuiteCRM itself with the 17 CS Euro Auto sales stages, the panel on the real Opportunity page, the endpoint reading the real database, the real dashboard dashlet. Verified in Chrome: login, dashboard, summary, recommendation, email generate / edit / regenerate / copy, no JavaScript errors.

Not real:
- It is a fresh SuiteCRM with seeded demo data, not the production CRM or its customisations. Staff, customers and activities are made up.
- Test drive and booking information come from activities (meetings named "Test drive...", notes), because the stock CRM has no dedicated test drive or booking fields.

## Files

- `ai-assistant/custom/` : everything added to the CRM (copied into `runtime/suitecrm/custom/` by `node install-ai-assistant.js`).
  - `include/AIAssistant/AIAssistant.php` : data collection, rules, Claude call.
  - `include/AIAssistant/entry.php` : the endpoint (`index.php?entryPoint=aiAssistant`).
  - `modules/Opportunities/views/view.detail.php` : the panel.
  - `modules/Home/Dashlets/AIFollowUpDashlet/` : the dashboard dashlet.
- `crm-php/seed*.php`, `add_product_field.php`, `add_dashlet.php` : demo data and set-up scripts.
- `runtime/` : PHP, MariaDB and SuiteCRM (nothing installed system-wide).
- `server.js`, `ai.js`, `public/` : the secondary "Dev pipeline" demo (requirement to code to test to debug to release notes) that deploys a Booking Refund notification hook into the same CRM and runs real tests. See below.

## Secondary demo: AI across the development steps

http://localhost:3000 walks one requirement ("After Booking Refund, notify the sales consultant and manager") from request to release: generated requirement, generated PHP hook deployed into this CRM, generated tests run against the real CRM, a planted bug found and fixed, release notes, and an impact summary. AI responses there are recorded unless `ANTHROPIC_API_KEY` is set. Manual-time numbers are estimates in `public/index.html` (`MANUAL`).

## Before presenting

- Replace the manual-time estimates with your real numbers.
- Record a backup run of the demo.
- Optional: set `ANTHROPIC_API_KEY` for live Claude output.
- To log in as `gcr_dev3` and see the same dashboard, log in once, then run `runtime\php\php.exe -c runtime\php\php.ini crm-php\add_dashlet.php gcr_dev3`.
