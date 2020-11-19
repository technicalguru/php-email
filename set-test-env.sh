#!/bin/bash

#######################################################
#
# TEST HOWTO
#
# Copy this file to e.g. set-local-test-env.sh and
# set the various variables below. Then source the
# file on you bash shell:
#
# ~/git/php-email$ . ./set-local-test-env.sh
#
# and start the PHP Unit tests:
#
# ~/git/php-email$ ./vendor/bin/phpunit tests
#
#######################################################

# Set this to 2 if your tests fail (will show SMTP debug)
DEBUG_LEVEL=0

# Timezone for dates
TIMEZONE="Europe\\/Berlin"

# The email address that all test emails shall be sent to
TARGET_EMAIL=john.doe@example.com

# The default sender address
SENDER_EMAIL=jane.doe@example.com

# The SMTP information
SMTP_HOST=www.example.com
SMTP_PORT=587
SMTP_AUTH=true
SMTP_USER=username
SMTP_PASS=password
SMTP_SECURE=starttls

# The Debug Address (for test mail feature
DEBUG_EMAIL=$TARGET_EMAIL

# The BCC email address (BCC mail mode)
BCC_EMAIL=hans.mustermann@example.com

# The Reroute email address (REROUTE mail mode)
REROUTE_EMAIL=$BCC_EMAIL

# The database information
DB_HOST=localhost
DB_PORT=3306
DB_NAME=databasename
DB_USER=username
DB_PASS=password
DB_PREFIX=phpunittest_

################ DO NOT EDIT FROM HERE ON #############
BCC_CONFIG="{\"recipients\":\"${BCC_EMAIL}\"}"
REROUTE_CONFIG="{\"recipients\":\"${REROUTE_EMAIL}\",\"subjectPrefix\":\"[Rerouted]\"}"

SMTP_CONFIG="{\"host\":\"${SMTP_HOST}\",\"port\":${SMTP_PORT},\"debugLevel\":${DEBUG_LEVEL},\"auth\":${SMTP_AUTH},\"credentials\":{\"user\":\"${SMTP_USER}\",\"pass\":\"${SMTP_PASS}\"},\"secureOption\":\"${SMTP_SECURE}\",\"charset\":\"utf8\"}"

EMAIL_TEST_SMTP="{\"timezone\":\"${TIMEZONE}\",\"mailMode\":\"default\",\"targetAddress\":\"${TARGET_EMAIL}\", \"smtpConfig\":${SMTP_CONFIG},\"rerouteConfig\":${REROUTE_CONFIG},\"bccConfig\":${BCC_CONFIG},\"debugAddress\":\"${DEBUG_EMAIL}\",\"defaultSender\":\"${SENDER_EMAIL}\",\"subjectPrefix\":\"[PHPUnitTest] \"}"
EMAIL_DATABASE="{\"host\":\"${DB_HOST}\",\"port\":${DB_PORT},\"dbname\":\"${DB_NAME}\",\"user\":\"${DB_USER}\",\"pass\":\"${DB_PASS}\",\"tablePrefix\":\"${DB_PREFIX}\"}"

export EMAIL_TEST_SMTP
export EMAIL_DATABASE




