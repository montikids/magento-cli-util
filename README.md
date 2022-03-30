
# Magento 2 CLI Util

> It doesn't work standalone: Magento 2 instance is required

The tool is designed to automate, simplify, and make it safe to convert the production environment into a staging or a development one. It can be used by developers manually or as a part of a deployment process to configure the staging environment.

It allows describing a scenario of sanitizing data in the database, Thus you can be sure you didn't forget to do something important, like disabling an integration locally (that can create a mess) or anonymizing customer emails (that could lead to accidental sending emails) and so on.

The util is based on the [Symfony Console](https://symfony.com/doc/current/components/console.html), so you must be already familiar with it. It's easy to use and to extend.

## Features
- Using YAML configs to describe data sanitizing scenarios
- Anonymizing sensitive customer data in Magento tables:
  - emails
  - names
  - addresses
  - etc.
- Replacing or deleting Magento store configuration values in the `core_config_data` table:
  - API integration keys
  - tracking services settings
  - domain name
  - etc.
- Running custom SQL queries as a part of the scenario
- Running specified [N98 Magerun 2](https://github.com/netz98/n98-magerun2) tool commands or Magento CLI commands as a part of the scenario
- Flexible configuration per environment:
  - Use the base (`base.yml`) config to describe the most common parts
  - Use an environment-specific (`dev.yml`, `stage.yml`) config to either describe additional rules or override ones specified in the base config
  - Use the local config (`local.yml`) to cover your personal needs: add, customize or override any rules defined in the base and/or the environment config
- Specifying the verbosity flag `-v` allows you to get more detailed output or error information

## Installation

Add the following content to the `repositories` section of your `composer.json` to make composer know where to search

```json
{
    "type": "git",
    "url": "https://github.com/montikids/magento-cli-util"
}
```
You probably would get something like that, if your Magento is installed via composer
```json
"repositories": [
   {
       "type": "composer",
       "url": "https://repo.magento.com/",
       "canonical": false
   },
   {
       "type": "git",
       "url": "https://github.com/montikids/magento-cli-util"
   }
],
```

Then run composer update to update the `composer.lock` file and force composer to discover the new repository
```bash
composer update
```

Now you can require the package as usual
```bash
composer require --dev montikids/magento-cli-util:@dev
```

After installation you must configure the environment.
## Usage / Commands
> After installation the tool is available as a composer binary: `vendor/bin/mk-cli-util`


### List commands
See all the commands currently available
```bash
vendor/bin/mk-cli-util list
```

Available list of commands:
```bash
vendor/bin/mk-cli-util configure:env <env> #  Set the util environment type. Different environments use different config files.
vendor/bin/mk-cli-util db:anonymize # Anonymize sensitive data in the Magento database
vendor/bin/mk-cli-util db:apply-config  #  Update "core_config_data" Magento DB table with the config file values
```


### Configure environment

#### Description
Before the first run, you must set the environment type you're going to use. Without it, you can't run other util commands for security reasons.

Available environments:
- `dev` – is intended to be used for the local development environment
- `stage` – is intended to be used for staging environment

#### Syntax
```bash
vendor/bin/mk-cli-util configure:env <env>
```

#### Example
Normal use
```bash
vendor/bin/mk-cli-util configure:env dev
```

For more verbosity
```bash
vendor/bin/mk-cli-util configure:env dev -v
```

#### What does it do
- Setting the specified environment type to the Magento's env config (`app/etc/env.php`)
```php
'mk_cli_util' => [
   'environment' => 'dev'
]
```
- Creating the `mk-cli-util` folder in your repository root, if it doesn't exist
- Copying config samples folder
- Initializing configs for the specified environment (only if they don't exist yet!). So, theoretically, you're ready to go. **But, please, don't do this without adjusting the configs according to your needs!**
- Adding a `.gitignore` file inside the folder to exclude local configuration files

> It's recommended to add the `mk-cli-util` folder to git in order to share configs across your team and have the ability to easily adjust the rules when a new feature creates some points to sanitize.


### Anonymize DB data

#### Description
Running this command starts data anonymizing processing in the Magento database according to the scenario specified in the config(s).

Config paths:
- `mk-cli-util/config/anonymize/base.yml` – required, contains the main and the most common set of rules
- `mk-cli-util/config/anonymize/{{env_name}}.yml` – optional, the config name depends on the configured environment, contains rules are specific for the environment
- `mk-cli-util/config/anonymize/local.yml` – optional, contains rules that are specific for you only, should never be under VCS

#### Syntax
```bash
vendor/bin/mk-cli-util db:anonymize
```

#### Example
Normal use
```bash
vendor/bin/mk-cli-util db:anonymize
```

For more verbosity (see SQL queries are executed, the number of affected rows, and so on)
```bash
vendor/bin/mk-cli-util db:anonymize -v
```

#### What does it do
- Reading the `app/etc/env.php` file to get Magento table prefix and the default connection settings
- Anonymizing the tables are specified in the corresponding section of the config according to rules specified for each table separately
- Running custom SQL queries according to the corresponding section of the config
- Running [N98 Magerun 2](https://github.com/netz98/n98-magerun2) tool according to the corresponding section of the config


### Apply store config values

#### Description
Running this command starts applying store config values (`core_config_data` table) according to the scenario specified in the config(s).

Config paths:
- `mk-cli-util/config/store-config/base.yml` – required, contains the main and the most common set of rules
- `mk-cli-util/config/store-config/{{env_name}}.yml` – optional, the config name depends on the configured environment, contains rules are specific for the environment
- `mk-cli-util/config/store-config/local.yml` – optional, contains rules that are specific for you only, should never be under VCS

#### Syntax
```bash
vendor/bin/mk-cli-util db:apply-config
```

#### Example
Normal use
```bash
vendor/bin/mk-cli-util db:apply-config
```

For more verbosity (see SQL queries are executed, the number of affected rows, and so on)
```bash
vendor/bin/mk-cli-util db:apply-config -v
```

#### What does it do
- Reading the `app/etc/env.php` file to get Magento table prefix and the default connection settings
- Updating the `core_config_data` table content according to the corresponding section of the config
- Running custom SQL queries according to the corresponding section of the config
- Running [N98 Magerun 2](https://github.com/netz98/n98-magerun2) tool according to the corresponding section of the config
## Config building reference
All the configs are YAML files.
The configs are merged in a way that allows inheriting from the basic configs and overriding values in the most specific one.
You can use YAML variables (a.k.a. anchors) if you want.

### Config types

#### Base
This config is required and created automatically during environment configuration.

It contains the main and the most common set of rules.

> It's recommended to keep it under VCS

#### Environment-specific
These configs are optional.
Config with the corresponding name is created automatically during environment configuration but you can't remove it if you don't need it.
The base config is used in this case.

These configs are used to specify some rules that are specific per environment. They inherit the base config.
The `dev.yml` config is intended to be used across the development team locally.
The `stage.yml` is intended to be used during or after deployment on the staging.

> It's recommended to keep them under VCS

#### Local
The config is optional, use it if you need a more specific config than the base and the environmental ones.
Each developer machine or staging instance can have its copy.

It inherits both: the base config and the env (if there is one).
In case there is no local config, the base and the environment-specific ones are used.

> Should be excluded from VCS

### Config merge logic

#### Priorities
1. Local
2. Environment-specific
3. Base

i.e. the local config is the top-priority one.

#### Merge rules
- Config with a lower priority is inherited by a config with a higher priority
- If the same path is specified in more than one config, the value from the config with higher priority is used


### Anonymize config
Contains rules of Magento tables data sanitizing.

#### Paths
Base config
- `mk-cli-util/config/anonymize/base.yml`
  Environment-specific configs
- `mk-cli-util/config/anonymize/dev.yml`
- `mk-cli-util/config/anonymize/stage.yml`
  Local config
- `mk-cli-util/config/anonymize/local.yml`

#### Sections
- `tables`
  - required
  - contains table names and rules for them that describe which columns should be processed and how
  - the structure is the following 
    ```yaml
    tables:
      {{table_name}}
        {{field_name}}
          value: {{option_value}}
          field_to_concat: {{option_value}}
          postfix: {{option_value}}
          is_password: {{option_value}}
          concat_field_name: {{option_value}}

        {{field_name}}: null
    ```
    - `{{table_name}}` – is a valid table name in the Magento database without the table prefix (if there is one)
    - `{{field_name}}` – is a valid field name of the table
      - set the whole field to `null` to skip anonymization (makes sense when you override the value in a more specific config)
    - `{{option_value}}` – depends on the option:
      - `value`
        - is a _string_ or _null_, the default value is `''`
        - describes the column main value to replace with or the prefix, if you concatenate it with either another field value, postfix, or both
        - can be an empty string
        - if is set to _null_ the field would be set to `NULL` in the database, other field options are ignored
      - `field_to_concat`
        - is a valid table field name in the same table (_string_) or _null_, the default value is `null`
        - the value of the specified field is concatenated to the `value`
        - if is set to _null_, no field value is concatenated
      - `postfix`
        - is a _string_ or _null_, the default value is `null`
        - some static string you want to be put after either `value` or `field_to_concat` (depending on the options values)
        - if is set to _null_, no postfix is added
      - `is_password`
        - is a _boolean_ value, the default value is `false`
        - for now, there is only one place where you can use this option – the `customer_entity` table
        - when the option is set to `true`, the `value` is encrypted in the way Magento encrypts customer passwords, so you can use the specified password to login into a customer account
        - when the option is set to `true`, `field_to_concat` and `postfix` are ignored
      - `concat_field_name`
        - is a _boolean_ value, the default value is `false`
        - when it's set to `true`, the `{{field_name}}` is joined to the end of the result value as a static string
        - the field name is joined after the `postfix`, `field_to_concat`, or `value`, depending on their settings
        - I have no idea why someone would need it but here we are
      - **any field option can be omitted, the default value is used then**

- `sql_query`
  - optional
  - is a key-value set of custom SQL queries you want to be run after the `tables` section is processed
  - the structure is the following
    ```yaml
    sql_query:
      {{alias}}: {{query}}
      {{alias}}: null
    ```
    - `{{alias}}`
      - is as a _string_
      - must be a valid YAML key (try to not use any special characters or spaces)
      - stands for some meaningful name for the query
    - `{{query}}`
    - is a valid SQL query (_string_) or _null_
    - that is run "as is", it means you _must_ specify the table prefix if you use it
    - trailing semicolon is optional
    - the `null` values means you want to skip running this query (makes sense when you override the value in a more specific config)
  - **you can set the whole section to _null_ (`sql_query: null`) if you don't want any queries executed** (makes sense when you override the section in a more specific config)

- `n98_magerun2_command`
- optional
- is a key-value set of [N98 Magerun 2](https://github.com/netz98/n98-magerun2) tool commands you want to be run after the `tables` section is processed
- the structure is the following
  ```yaml
  n98_magerun2_command:
    {{alias}}: {{command}}
    {{alias}}: null
  ```
  - `{{alias}}`
    - is as a _string_
    - must be a valid YAML key (try to not use any special characters or spaces)
    - stands for some meaningful name for the command
  - `{{command}}`
    - is a valid [N98 Magerun 2](https://github.com/netz98/n98-magerun2) tool command (_string_) or _null_
    - that is run "as is", it means you _must_ specify all the command arguments if there are some
    - the `null` values means you want to skip running this command (makes sense when you override the value in a more specific config)
  - **you can set the whole section to _null_ (`n98_magerun2_command: null`) if you don't want any commands executed** (makes sense when you override the section in a more specific config)

#### Examples
The following example demonstrates the general approch to writing the sections and how to use YAML anchors (variables). For more extended samples, please check [the corresponding folder](config/_sample/anonymize) in the repository.
```yaml
variables:
 - &mail_domain '@trash-mail.com'
 - &customer_password 'Password123'
 - &customer_phone '1234 5678'
 - &customer_fax '1234 5678'
 - &customer_street ' test avenue'
 - &ip '0.0.0.0'
 
tables:
 customer_entity:
   firstname:
     value: 'firstname_'
     field_to_concat: 'entity_id'
   lastname:
     value: 'firstname_'
     field_to_concat: 'entity_id'
   email:
     value: 'dev_'
     field_to_concat: 'entity_id'
     postfix: *mail_domain
   password_hash:
     value: *customer_password
     is_password: true
 
 customer_address_entity:
   firstname:
     value: 'firstname_'
     field_to_concat: 'entity_id'
   lastname:
     value: 'lastname_'
     field_to_concat: 'entity_id'
   street:
     value: *customer_street
     field_to_concat: 'entity_id'
   city:
     value: 'city_'
     field_to_concat: 'entity_id'
   telephone:
     value: *customer_phone
     field_to_concat: 'entity_id'
   fax:
     value: *customer_fax
     field_to_concat: 'entity_id'
 
sql_query:
 truncate_reporting_users: "TRUNCATE reporting_users;"
 
n98_magerun2_command:
 reindex: 'indexer:reindex'
```

#### Merge examples
Base config
```yaml
variables:
 - &mail_domain '@trash-mail.com'
 - &customer_password 'Password123'
 - &customer_phone '1234 5678'
 - &customer_fax '1234 5678'
 - &customer_street ' test avenue'
 - &ip '0.0.0.0'
 
tables:
 customer_entity:
   firstname:
     value: 'firstname_'
     field_to_concat: 'entity_id'
   lastname:
     value: 'firstname_'
     field_to_concat: 'entity_id'
   email:
     value: 'dev_'
     field_to_concat: 'entity_id'
     postfix: *mail_domain
   password_hash:
     value: *customer_password
     is_password: true
  
 customer_address_entity:
   firstname:
     value: 'firstname_'
     field_to_concat: 'entity_id'
   lastname:
     value: 'lastname_'
     field_to_concat: 'entity_id'
   street:
     value: *customer_street
     field_to_concat: 'entity_id'
   city:
     value: 'city_'
     field_to_concat: 'entity_id'
   telephone:
     value: *customer_phone
     field_to_concat: 'entity_id'
   fax:
     value: *customer_fax
     field_to_concat: 'entity_id'
 
sql_query:
 truncate_reporting_users: "TRUNCATE reporting_users;"
 
n98_magerun2_command:
 reindex: 'indexer:reindex'
```

Environment-specific config
```yaml
variables:
 - &customer_password 'my_awesome_password'
 - &mail_domain '@test.com'
 
tables:
 customer_entity:
   firstname:
     value: 'John'
     field_to_concat: null
   email:
     postfix: *mail_domain
   password_hash:
     value: *customer_password
   lastname:
     value: 'Doe'
    
sql_query:
 truncate_queue_message_status: 'TRUNCATE queue_message_status;'
 clean_queue_message: 'DELETE FROM queue_message;'
 
n98_magerun2_command:
 generate_urn: 'dev:urn-catalog:generate .idea/misc.xml'
```

Local config
```yaml
variables:
 - &customer_password '567***$ERFhgdgds!#$@'
 - &mail_domain '@local.test'
 
tables:
 customer_entity:
   firstname:
     value: 'Sheldon '
     field_to_concat: 'entity_id'
   email:
     value: ''
     postfix: *mail_domain
   password_hash:
     value: *customer_password
   lastname: null
 
 customer_address_entity:
   firstname:
     value: 'Sheldon '
 
sql_query:
 truncate_reporting_users: null
 
n98_magerun2_command:
 reindex: null
```

The result
```yaml
tables:
 customer_entity:
   firstname:
     value: 'Sheldon '
     field_to_concat: 'entity_id'
   lastname:
     value: 'firstname_'
     field_to_concat: 'entity_id'
   email:
     value: ''
     field_to_concat: 'entity_id'
     postfix: '@local.test'
   password_hash:
     value: '567***$ERFhgdgds!#$@'
     is_password: true
  
 customer_address_entity:
   firstname:
     value: 'Sheldon '
     field_to_concat: 'entity_id'
   lastname:
     value: 'lastname_'
     field_to_concat: 'entity_id'
   street:
     value: ' test avenue'
     field_to_concat: 'entity_id'
   city:
     value: 'city_'
     field_to_concat: 'entity_id'
   telephone:
     value: '1234 5678'
     field_to_concat: 'entity_id'
   fax:
     value: '1234 5678'
     field_to_concat: 'entity_id'
 
sql_query:
 truncate_reporting_users: null
 truncate_queue_message_status: 'TRUNCATE queue_message_status;'
 clean_queue_message: 'DELETE FROM queue_message;'
 
n98_magerun2_command:
 reindex: null
 generate_urn: 'dev:urn-catalog:generate .idea/misc.xml'
```


### Store config values config
Contains rules of updating values of the `core_config_data` Magento table.

#### Syntax
```bash
vendor/bin/mk-cli-util db:apply-config 
```

#### Paths
Base config
- `mk-cli-util/config/store-config/base.yml`
  Environment-specific configs
- `mk-cli-util/config/store-config/dev.yml`
- `mk-cli-util/config/store-config/stage.yml`
  Local config
- `mk-cli-util/config/store-config/local.yml`

#### Sections
- `config`
  - required
  - contains config paths (that represent the `path` field of the `core_config_data` table) and rules for them that describe how to process their values
  - paths can be specified for the default scope, for any website, or for any store
  - the structure is the following
    ```yaml
    config:
      {{scope_name}}:
        {{scope_id}}:
          {{path}}: {{value}}
          {{path}}:
            encrypt: {{non_ecrypted_value}}
          {{path}}:
            delete: true
          {{path}}:
            skip: true
    ```
    - `{{scope_name}}` – is `default`, `websites`, or `stores`
    - `{{scope_id}}` – is `0` for the `default` scope and a valid (existed) website or store ID for `websites` and `stores` correspondingly
      - set the whole field to `null` to skip anonymization (makes sense when you override the value in a more specific config)
    - `{{path}}` – the config value path, exactly it's specified in the `path` field of the `core_config_data` table
    - `{{value}}`
      - is a _string_ or a _special instruction_ (`delete: true`, `skip: true`)
      - for boolean values use `'0'` and `'1'` instead of `false` and `true` correspondingly
      - specify integer/float/whatever values as strings, e.g.: `'139'`, `'18.35'`, and so on
      - `encrypt: {{non_ecrypted_value}}`
        - is a special instruction that encrypts the `{{non_ecrypted_value}}` in the way Magento does it
        - use it to set fields with type [obscure](https://devdocs.magento.com/guides/v2.4/config-guide/prod/config-reference-systemxml.html#field-type-reference), like passwords, API keys, and so on
        - `{{non_ecrypted_value}}` – is a _string_ that stands for the password/API ket/whatever in a raw mode (before encryption)
      - `delete: true`
        - is a special instruction that removes the row with the specified path completely from the table
        - you may need it in case you want to force using the most default value from `config.xml` or just to have the config value unset
      - `skip: true`
        - is a special instruction that allows skipping processing the path value (makes sense when you override the value in a more specific config)

- `sql_query`
  - optional
  - is a key-value set of custom SQL queries you want to be run after the `tables` section is processed
  - the structure is the following
    ```yaml
    sql_query:
      {{alias}}: {{query}}
      {{alias}}: null
    ```
    - `{{alias}}`
      - is as a _string_
      - must be a valid YAML key (try to not use any special characters or spaces)
      - stands for some meaningful name for the query
    - `{{query}}`
      - is a valid SQL query (_string_) or _null_
      - that is run "as is", it means you _must_ specify the table prefix if you use it
      - trailing semicolon is optional
      - the `null` values means you want to skip running this query (makes sense when you override the value in a more specific config)
    - **you can set the whole section to _null_ (`sql_query: null`) if you don't want any queries executed** (makes sense when you override the section in a more specific config)

- `n98_magerun2_command`
  - optional
  - is a key-value set of [N98 Magerun 2](https://github.com/netz98/n98-magerun2) tool commands you want to be run after the `tables` section is processed
  - the structure is the following
    ```yaml
    n98_magerun2_command:
      {{alias}}: {{command}}
      {{alias}}: null
    ```
    - `{{alias}}`
      - is as a _string_
      - must be valid YAML key (try to not use any special characters or spaces)
      - stands for some meaningful name for the command
    - `{{command}}`
      - is a valid [N98 Magerun 2](https://github.com/netz98/n98-magerun2) tool command (_string_) or _null_
      - that is run "as is", it means you _must_ specify all the command arguments if there are some
      - the `null` values means you want to skip running this command (makes sense when you override the value in a more specific config)
    - **you can set the whole section to _null_ (`n98_magerun2_command: null`) if you don't want any commands executed** (makes sense when you override the section in a more specific config)

#### Examples
The following example demonstrates the general approch to writing the sections. For more extended samples, please check [the corresponding folder](config/_sample/store-config) in the repository.

```yaml
config:
 default:
   0:
     ### Security ###
     admin/security/password_lifetime: '9000'
     admin/security/session_lifetime: '9000'
 
     ### HTTPS ###
     web/secure/use_in_frontend:
       delete: true
     web/secure/use_in_adminhtml:
       delete: true
 
     ### Mail ###
     system/smtp/disable: '1'
     system/smtp/host:
       delete: true
     system/smtp/port:
       delete: true
 
 stores:
   1:
     general/store_information/name: 'My store name (store-level)'
 
 websites:
   1:
     general/store_information/name: 'My store name (website-level)'
 
sql_query:
 clean_report: 'DELETE FROM reporting_users;'
 
n98_magerun2_command:
 cache_flush: 'cache:flush'
```

#### Merge examples
Base config
```yaml
config:
 default:
   0:
     ### Security ###
     admin/security/password_lifetime: '9000'
     admin/security/session_lifetime: '9000'
 
     ### HTTPS ###
     web/secure/use_in_frontend:
       delete: true
     web/secure/use_in_adminhtml:
       delete: true
 
     ### Mail ###
     system/smtp/disable: '1'
     system/smtp/host:
       delete: true
     system/smtp/port:
       delete: true
 
 stores:
   1:
     general/store_information/name: 'My store name (store-level)'
 
 websites:
   1:
     general/store_information/name: 'My store name (website-level)'
 
sql_query:
 clean_report: 'DELETE FROM reporting_users;'
 
n98_magerun2_command:
 cache_flush: 'cache:flush'
```

Environment-specific config
```yaml
config:
 default:
   0:
     # Let's mitigate security politics for developers machines
     admin/security/password_lifetime: ''
     admin/security/session_lifetime: 31536000
     admin/security/password_is_forced: '0'
 
     # In case the most developers don't use HTTPS locally
     web/secure/use_in_frontend:
       delete: true
     web/secure/use_in_adminhtml:
       delete: true
 
     # You probably don't want sending data to NewRelic from developers machines
     newrelicreporting/general/enable: '0'
     newrelicreporting/cron/enable_cron: '0'
     newrelicreporting/general/account_id:
       delete: true
     newrelicreporting/general/app_id:
       delete: true
     newrelicreporting/general/api:
       delete: true
     newrelicreporting/general/insights_insert_key:
       delete: true
 
     # Enable some payment methods handy to use on development
     payment/checkmo/active: '1'
 
sql_query:
 truncate_wishlist: 'TRUNCATE wishlist;'
 clean_report: null
 
n98_magerun2_command:
 show_mode: 'deploy:mode:show'
```

Local config
```yaml
config:
 default:
   0:
     web/unsecure/base_url: 'http://magento.test/'
     web/secure/base_url: 'https://magento.test/'
     admin/url/custom: 'https://magento.test/'
 
     web/secure/use_in_frontend: '1'
     web/secure/use_in_adminhtml: '1'
 
 stores:
   1:
     general/store_information/name:
       skip: true
 
sql_query: null
```

The result
```yaml
config:
 default:
   0:
     admin/security/password_lifetime: ''
     admin/security/session_lifetime: 31536000
     admin/security/password_is_forced: '0'
 
     web/unsecure/base_url: 'http://magento.test/'
     web/secure/base_url: 'https://magento.test/'
     admin/url/custom: 'https://magento.test/'
 
     web/secure/use_in_frontend: '1'
     web/secure/use_in_adminhtml: '1'
 
     newrelicreporting/general/enable: '0'
     newrelicreporting/cron/enable_cron: '0'
     newrelicreporting/general/account_id:
       delete: true
     newrelicreporting/general/app_id:
       delete: true
     newrelicreporting/general/api:
       delete: true
     newrelicreporting/general/insights_insert_key:
       delete: true
 
     payment/checkmo/active: '1'
 
     system/smtp/disable: '1'
     system/smtp/host:
       delete: true
     system/smtp/port:
       delete: true
 
 stores:
   1:
     general/store_information/name:
       skip: true
 
 websites:
   1:
     general/store_information/name: 'My store name (website-level)'
 
sql_query: null
 
n98_magerun2_command:
 cache_flush: 'cache:flush'
 show_mode: 'deploy:mode:show'
```
## Local development and testing

As far as the package can't exist standalone (without a Magento installation), developing it locally is quite a challenge.

> It's highly recommended to use a copy of your Magento project in a separate folder.
> It should prevent you from constant modifying/reverting `composer.json` and `composer.lock` files and save you a lot of time and nerves.
> That's because local development requires adding the custom repository that
> points to the local directory

### Preparing
There is a trick to make composer load the local version of the package instead of downloading it from the repository.
Add the following content into the `repositories` section of your `composer.json` of your Magento installation.

```json
{
   "type": "path",
   "url": "../magento-cli-util",
   "options": {
       "symlink": false
   }
}
```

where
- `"type": "path"` – tells composer you want to load a local directory
- `"url": "../magento-cli-util"` – relative or absolute path to the directory with the package
- `"symlink": false` – tells composer to copy/mirror files instead of creating a symlink

> symlink may seem a more convenient option to you but you probably would face some issues on using relative paths

#### If you have already installed the package
Unfortunately, in this case, the only solution that I found is to remove the `composer.lock` file.
Without this, the repository URL isn't updated and you continue receiving the package from the repository instead of the specified local path.
```bash
rm -f composer.lock
```

Remove the package version you have and clean the composer cache
```bash
rm -rf vendor/montikids/magento-cli-util && rm -rf vendor/bin/mk-cli-util && composer clear-cache
```

Run the installation of the packages and go to the kitchen to make yourself a cup of coffee
```bash
composer install
```

#### If it's the first installation
Require the package as usual
```bash
composer require --dev montikids/magento-cli-util:@dev
```

You should see something like this (pay attention to the word `mirroring`)
```
- Installing montikids/magento-cli-util (dev-1.0.0): Mirroring from ../magento-cli-util
```

### Developing
1. Make the changes you need in the local folder with the package
2. Remove the outdated version from the Magento project's vendor, clean cache, and run the installation of the missed package

```bash
rm -rf vendor/montikids/magento-cli-util
rm -rf vendor/bin/mk-cli-util
composer clear-cache
composer install
```
I prefer to run it as a single command, it's much more convenient when you need to test your changes quite often

```bash
rm -rf vendor/montikids/magento-cli-util && rm -rf vendor/bin/mk-cli-util && composer clear-cache && composer install
```
3. Profit!

> In case you modified the `composer.json` of the package (e.g. the version of one of the dependencies),
> you would have to update the `composer.lock` in your Magento project
> ```bash
> composer update
> ```
> If it doesn't help, remove the `composer.lock` and run
> ```bash
> composer install
> ```

## Authors

- [@novakivskiy](https://github.com/novakivskiy)
- [@timoffmax](https://github.com/timoffmax)
 

