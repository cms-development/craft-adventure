# Trail Adventure

This repository contains demo code for a fictional travel agency offering adventure tours. Built with Craft CMS and developed locally using ddev, it is intended for educational purposes and not for production use. Some templates are borrowed from CraftQuest.

## Installation

1. Clone the repository.
2. Start ddev (ensure ddev is installed and Docker is running): `ddev start`
3. Copy `.env.example.dev` to `.env` and adjust settings, such as `PRIMARY_SITE_URL` to your local site's URL.
4. Install dependencies: `composer install`
5. Import the database:
    - Copy `_db_snapshots` contents to `.ddev/db_snapshots`.
    - Run `ddev snapshot restore craft-adventure_20241212163730` in the terminal.
6. Set up a tunnel for Mollie webhooks using ngrok:
    - Run `ngrok http ...`
    - Update `PRIMARY_SITE_URL` in `.env` to the ngrok URL.

## Features

### route: adventures/{slug}
A frontend entry form enables logged-in users to apply for a trail using the `_forms/participate.twig` template. The form adheres to Craft CMS validation rules and is submitted to `entries/save-entry`. Upon successful submission, users are redirected back to the previous page.

### route: /rekening
A simple payment form processes payments using the Mollie API, submitted to `mollie/payments/create-payment`. Successful payments redirect users to a thank-you template with a success message.

### route: /goodies
Displays all available goodies using the `goodies/index.twig` template. When a user purchases a goodie, a 'stash' entry is created in Craft CMS and linked to the user. Payments are processed using the Mollie API, and successful payments redirect users to a thank-you template. A webhook updates the stash entry status to 'paid'.
