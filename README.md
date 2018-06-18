# Rave-CS-Cart
Rave by Flutterwave CS-Cart

- Contributor - Oluwole Adebiyi (King Flamez)
- Keywords - ussd, cs-cart, cscart, rave, flutterwave, account, bank payments, debit-cards, MPESA, ecommerce, cscart payment.

## Description
Allows you to use CS-Cart with flutterwave's Rave. Accept Payments worldwide into your CSCart website. 



## Requirements
1. CS-Cart installation
2. A [live rave account](https://rave.flutterwave.com)

> [Test rave account](https://ravesandboxapi.flutterwave.com)

## Installation

> Step 1

1. Download this repositry.
2. There is an sql-dump file which needs to be run in your database (rave_installation.sql), run this with phpMyAdmin or SQL console.
3. Upload [rave.php](app/payments) into `app/payments` in your CS-Cart installation
4. Upload [rave.tpl](design/backend/templates/views/payments/components/cc_processors) into `design/backend/templates/views/payments/components/cc_processors`  in your CS-Cart installation


> Step 2

1. Log into CS-Cart as an administrator. Navigate to Administration / Payment Methods.
2. Click the "+" to add a new payment method.
3. Choose Rave from the List
4. Give it a name you want.
5. Go to the `Configure Tab` and add your configuration details.
6. Click `Save`

💪🏿 You are ready to go
