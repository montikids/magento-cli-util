## Intro
The utility designed to configure the production DB on the environment dev/local/stage.
It  based on console commands and settings stored in the YAML format (folder "configs").

## Getting started


### Install

1. Update `composer.json`
```shell
"repositories": [
...
       {
            "type": "git",
            "url": "https://github.com/montikids/cli-magento-configure"
        }
        ...
    ],
    "
```

2. `composer require montikids/cli-magento-configure:^1.0`
3. `php bin/magento setup:upgrade`
4. `cd cli_util`
5. `chmod +x bin/n98-magerun2.phar`
6. `php bin/console configure:init [env_name]`. Available env_name: "*local*","*dev*","*stage*".
   After successful execution of the command, will add a new value to *"app/etc/env.php"*

### CLI commands
Available CLI commands:
- `php bin/console configure:init [env_name]` - add env name to app/etc/env.php
- `php bin/console db:anonymize` - hide the client's personal data in the DB
- `php bin/console db:configure` - changing store settings in table *core_config_data* and run custom SQL queries

### YAML configure
The configuration files located folder:  _configs_.

After installation module:
```shell
cp cli_util/configs/anonymize/example/*  cli_util/configs/anonymize 
cp cli_util/configs/storeConfigData/example/*  cli_util/configs/storeConfigData
```

#### Anonymize Customer Data
File: *configs/anonymize/base.yml*

Section: **Variables** -  list of variables to use in the section *config*

Example:
```yaml
variables:
  $ANONYMIZED_PASSWORD: 'Password123'
  ....   
```

**config**  - anonymizing DB data using configuration

```yaml
config:
  table_name:
    - [colName, concatString, concatFieldName, additionalString, encrypt(bool), concatFieldNameAfter(bool)]
```

Example:
```yaml
config:
  customer_entity:
    - [ 'firstname', 'firstname_', 'entity_id', '', false, true ]
    - [ 'lastname','lastname_', 'entity_id', '', false, true ]
    - [ 'email', 'dev_', 'entity_id', '$ANONYMIZED_MAIL', false, true ]
    - [ 'password_hash', '$ANONYMIZED_PASSWORD', null, null, true, true ]
```

As a result, YAML config converted to:
```sql
UPDATE customer_entity SET firstname=CONCAT('firstname_', entity_id, ''),lastname=CONCAT('lastname_', entity_id, ''),email=CONCAT('dev_', entity_id, '@trash-mail.com'),password_hash=CONCAT(CONCAT(SHA2('e4723c5083bd68dc6867f783ecb3d430Password123', 256), ':e4723c5083bd68dc6867f783ecb3d430:1'), '', '');
```

**sql_query** - contains a list of SQL queries

```yaml
sql_query:
   alias_name: '[SQL QUERY]'
```
Example:

```yaml
sql_query:
   clean_quote: 'DELETE FROM quote'
   sales_order_gift_null: 'UPDATE sales_order SET gift_info = NULL'
```

**magerun2_run** - run magerun2 commands, [List command](https://github.com/netz98/n98-magerun2)

```yaml
magerun2_run:
   alias_name: '[command]'
```

Example:

```yaml
magerun2_run:
   clean_cache: 'cache:clean'
   custom_admin_pass: admin:user:change-password admin admin1234
```

#### Configure DB
File: *`configs/storeConfigData/*.yml`*

File List:
* **base.yml** - base file containing settings
* **dev.yml** - overriding settings or extend for *dev* env
* **stage.yml** overriding settings or extend for *stage* env
* **local.yml.sample** - sample file for *local* env

To work in the local development environment, you need to copy **local.yml.sample** to **local.yml**

###### Sections
**config** -  configure table *"core_config_date"*.

Where are the values "*scope*", "*scope_id*", "*path*" - equivalent table values *"core_config_date"*

```yaml
config:
   scope:
      scope_id
      path: [value] or
               [additional_action_name]: [value]

```

*"additional_action_name"* supports methods:
* `encrypt` - convert value to encrypted
* `delete` - remove config path from core_config data

Example:

```yaml
config:
   default:
      0:
         web/secure/use_in_frontend: '0'
         payment/stripecore/test_api_key:
            encrypt: 'secret_value'
         payment/mr_quadpay/sandbox_flag:
            delete: true

```

**sql_query** - contains a list of SQL queries

```yaml
sql_query:
   alias_name: '[SQL QUERY]'
```
Example:

```yaml
sql_query:
   clean_quote: 'DELETE FROM quote'
   sales_order_gift_null: 'UPDATE sales_order SET gift_info = NULL'
```


**magerun2_run** - run magerun2 commands, [List command](https://github.com/netz98/n98-magerun2)

```yaml
magerun2_run:
   alias_name: '[command]'
```

Example:

```yaml
magerun2_run:
   clean_cache: 'cache:clean'
   custom_admin_pass: admin:user:change-password admin admin1234
```
