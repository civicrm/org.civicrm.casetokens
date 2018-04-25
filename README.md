# CiviCRM Case Tokens

![Screenshot](/images/screenshot.png)

This extension implements some additional tokens for use in Message Templates when generating PDF/sending emails for Cases.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v5.4+
* CiviCRM 4.7+

## Installation (Web UI)

This extension can be installed via the Web UI.

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl org.civicrm.casetokens@https://github.com/civicrm/org.civicrm.casetokens/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/civicrm/org.civicrm.casetokens.git
cv en casetokens
```

## Usage

Select the case tokens from the dropdown "tokens" list.

Currently implemented:
- "Case Roles": 
  - "case_roles.{$role}_display_name"
  - "case_roles.{$role}_address"
  - "case_roles.{$role}_phone"
  - "case_roles.{$role}_email"

## Known Issues

The tokens will only be available for selection in MessageTemplates via the Send an Email / Print PDF forms when accessed directly via a Case (this is because it uses the Case ID to get the configured case roles).
