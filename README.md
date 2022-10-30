# php-email
Provides an object-oriented way to send and queue emails.

There is a need to send e-mails in almost all projects, e.g. for user registration or
password reset functions. Most e-mails sent are very easy. Sophisticated features
like PGP encryption etc is not required. This library offers a way to create
such e-mails without setting up the PHPMailer (the underlying library), to send
multiple e-mails and even to defer sending e-mail sending by using a queue backed
by a database - and all in an object-oriented way.

Features are:

* Encapsulating SMTP settings in an `EmailQueue` object.
* Reuse a mailer object for multiple e-mails
* Sending HTML and TEXT e-mails
* Embedding pictures and attaching files
* Enqueueing mails in a database backend
* Prefixing subject lines automatically
* Easily create compatible e-mail addresses including the address name
* E-mail queue will try to send an e-mail multiple times before failing
* Ability to set a "Mail Mode". Mail Modes can BCC, reroute or even block e-mails completely
  which can be useful in development or acceptance environments. 

# License
This project is licensed under [GNU LGPL 3.0](LICENSE.md). 

# Installation

## By Composer

```sh
composer require technicalguru/email
```

## By Package Download
You can download the source code packages from [GitHub Release Page](https://github.com/technicalguru/php-email/releases)

# How to use it

## Create Main Configuration Object

The central class for configuration is the `EmailConfig`. It holds all necessary information.
Let's start with the basic skeleton:

```

use TgEmail\EmailConfig;

$config = new EmailConfig();
$config->setTimezone('Europe/Berlin');
$config->setDefaultSender('John Doe <john.doe@example.com>');
$config->setSubjectPrefix('[MyAppName] ');
$config->setDebugAddress('admin@example.com');
```

The lines above create the configuration and tells it to use the timezone `Europe/Berlin` when it needs
to create timestamps. This is required mainly when e-mails are queued and the timestamp needs to be
recorded. This value is optional and defaults to `UTC`.

Next, a default sender address is configured. The default sender will be used when a specific
e-mail to be sent does not define a sender address. Creating e-mail addresses is explained further below.

The subject prefix is used on every e-mail to be sent later. Subjects will be prefixed with this string. The
default is `NULL` and will not modify the subject.

A debug address is required only when you need to send a test mail.

## Create SMTP Configuration

We still need to tell where our SMTP server is located. So this is how you set these values:

```
use TgEmail\Config\SmtpConfig;

$host         = 'smtp.example.com;
$port         = 587;
$auth         = TRUE;
$username     = 'mySmtpUser';
$password     = 'mySmtpPassword';
$secureOption = 'starttls';

$smtpConfig = new SmtpConfig($host, $port, $auth, $username, $password, $secureOption);
$config->setSmtpConfig($smtpConfig);
```

Most options are self-explaining. `$auth` tells underlying PHPMailer whether to authenticate with given
user credentials. `$secureOption` is defined by `PHPMailer` and shall have value `smtps` or `starttls`. 
See the PHPMailer documentation for further information.

All properties can be set by using a setter:

```
use TgEmail\Config\SmtpConfig;

$smtpConfig = new SmtpConfig();
$smtpConfig->setHost('smtp.example.com');
$smtpConfig->setPort(587);

// continue setup...
```

Authentication credentials can also be set by using the `\TgUtils\Auth\CredentialsProvider` interface from
`technicalguru/utils` package:

```
// Define here your provider
$provider = ...; 

// Tell SMTP config
$smtpConfig->setCredentialsProvider($provider);
```

## Create the Main MailQueue object

Now it's time to create our central `MailQueue` object:

```
use TgEmail\EmailQueue;

$mailer = new EmailQueue($config);
```

You are ready send your first e-mail.

## Send a Test E-Mail

There is a fast and easy way to check whether your setup works correctly:

```
$email = $mailer->createTestMail();
$rc = $mailer->send($email);
```

## Send an E-Mail

We have setup the minimum requirements to send an e-mail:

```
use TgEmail\Email;

$email = new Email();
$email
    ->setSubject('Hello World')
    ->addTo('john.doe@example.com', 'John Doe')
    ->addBcc('jane.doe@example.com')
    ->setReplyTo('my-support@example.com')
    ->setBody(Email::TEXT, 'The text e-mail body')
    ->setBody(Email::HTML, '<p>The HTML e-mail body</p>');
    
// Will return FALSE when sending fails
$rc = $mailer->send($email);
```

That's it. The code snippet above is all you would need in your application code in order to send e-mails.
Configuration and setup shall be buried somewhere in your infrastructure setup.

## Hot to add Attachments or embed Images

Attaching files or embedding images is simple. You will need to have the file available and readable
on the filesystem:

```
use TgEmail\Attachment;

$myFile  = new Attachment(Attachment::ATTACHED, 'file.pdf', NULL, '/local/path/to/file.pdf', 'application/pdf'); 
$myImage = new Attachment(Attachment::EMBEDDED, 'img.png', 'img1', '/local/path/to/img.png', 'image/png');

$email
   ->addAttachment($myFile)
   ->addAttachment($myImage);
```

Note the third parameter of embedded images. It defines a unique ID within your HTML email which you can reference by

```
// Using the embedded image
$myHTML = '<img src="cid:img1">';
```

The `MailQueue` will leave all your attachments untouched on your filesystem. However, sometimes you may wish to get rid
of the file after you sent the e-mail. The constructor takes two additional arguments:

```
$myFile = new Attachment(Attachment::ATTACHED, $filename, $cid, $path, $mimeType, TRUE, TRUE);
```

The first boolean will trigger the file to be deleted after the e-mail was sent successfully. The second boolean tells
whether the file can be deleted when sending failed. Using these parameters you don't need to take care 
about temporary files anymore. Especially when it comes to queueing and deferred sending.

## Mail Modes

`MailQueue` supports so-called Mail Modes. They tell the mailer object how to generally treat e-mails. This comes
comfortable when you're either testing a setup, when you are in an environment that has real e-mail addresses (such as User Acceptance Test environments) or when actually sending out e-mails doesn't make much sense.

These modes are available:

* `EmailQueue::DEFAULT` - This is the normal operation. All e-mails are sent as defined.
* `EmailQueue::BLOCK` - This will prevent any mail to be sent or queued. The return code is always TRUE.
* `EmailQueue::REROUTE` - All e-mails will be sent to another address, usually an admin or developer address and the
  defined recipients of the e-mail are ignored.
* `EmailQueue::BCC` - The e-mails will be sent to their intended recipients but additional addresses are set on BCC.
 
### Blocking all E-Mails

Blocking all e-mails to be sent or queued is quite easy:

```
$mailer->setMailMode(EmailQueue::BLOCK);
```

The same method can be used on the central `EmailConfig` object.

### Rerouting all E-Mails

You need a `RerouteConfig` configuration to be set in the main configuration. You can set this up-front when creating the
config object, or alltogether when setting the mail mode:

```
use TgEmail\Config\RerouteConfig;

// Create the config
$subjectPrefix = '[Rerouted]';
$recipients    = array('my-dev-account@example.com');
$rerouteConfig = new RerouteConfig($subjectPrefix, $recipients);

// And set the mail mode
$mailer->setMailMode(EmailQueue::REROUTE, $rerouteConfig);
```

### Set a Developer as BCC on all sent E-mails

You need a `BccConfig` configuration to be set in the main configuration. You can set this up-front when creating the
config object, or alltogether when setting the mail mode:

```
use TgEmail\Config\BccConfig;

// Create the config
$recipients = array('my-dev-account@example.com');
$bccConfig  = new BccConfig($recipients);

// And set the mail mode
$mailer->setMailMode(EmailQueue::BCC, $bccConfig);
```

## Queue E-Mails to be sent later

One drawback of sending out e-mails directly from application code is the that it is time-consuming. Your
user needs to wait for the sending to complete before she/he can see any response from your application. 
Queueing e-mails is the solution as sending is deferred (preferrable to a cron job) and the user receives
her/his application response fast. 

You will need a [`TgDatabase\Database`](https://github.com/technicalguru/php-database/blob/main/src/TgDatabase/Database.php) 
object to queue e-mails. Otherwise, `EmailQueue` will throw exceptions when you try to queue e-mails. Please refer
to the [`TgDatabase\Database`](https://github.com/technicalguru/php-database/) documentation about how to create
the `Database` object. Setup the according `EmailsDAO` and `EmailQueue` as follows:

```
use TgEmail\EmailsDAO;

$dao    = new EmailsDAO($database);
$mailer = new EmailQueue($config, $dao);
```

The mailer will automatically create the queue table if it does not exist. 

Once, the `EmailsDAO` is available, you can easily queue e-mails:

```
// Create your email object here
$email = ...

// Queue it. Will return FALSE when sending fails
$rc = $mailer->queue($email);
```

## Processing the E-Mail Queue

You can process the queue in another call or during a cronjob:

```
$mailer->processQueue($maxSeconds);
```

The argument `$maxSeconds` will ensure that the processing stops when the time limit has been reached.
The argument is optional and defaults to 60 seconds.

## How to Create an E-mail Address

There are multiple ways to create e-mail addresses. All mailing components use an `EmailAddress` object.
You can use this object as an argument whereever e-mail addresses are expected. Several ways exist
to create such an object.

```
// From a string
$address = EmailAddress::from('john.doe@example.com');
$address = EmailAddress::from('<john.doe@example.com>');
$address = EmailAddress::from('John Doe <john.doe@example.com>');

// From email string and name
$address = EmailAddress::from('john.doe@example.com', 'John Doe');

// From another object
$obj = new \stdClass;
$obj->name  = 'John Doe';
$obj->email = 'john.doe@example.com';
$address = EmailAddress::from($obj);

// From another EmailAddress
$address = EmailAddress::from($anotherEmailAddressObject);
```

This means that you can use these flavours when creating e-mails:

```
$email->addTo('John Doe <john.doe@example.com>');
$email->addTo('john.doe@example.com', 'John Doe');
$email->addTo(array('John Doe <john.doe@example.com>', $anotherEmailAddressObject, $obj);
```

## Creating Configuration Objects from Objects, Arrays or JSON strings

The configuration objects introduced above can also be created using JSON strings, objects or associative arrays.
The following snippets describe the JSON objects in short notation. 

```
SmtpConfig:
-----------
{
   "host":         "www.example.com",
   "port":         587,
   "debugLevel":   0,
   "auth":         true,
   "secureOption": "starttls",
   "charset":      "utf8",
   "credentials": {
      "user": "username",
      "pass": "password"
   }
},

RerouteConfig:
--------------
{
   "recipients":    "hans.mustermann@example.com",
   "subjectPrefix": "[Rerouted]"
},

BccConfig:
----------
{
   "recipients":  "hans.mustermann@example.com"
},

EmailConfig:
------------
{
   "timezone":      "Europe\/Berlin",
   "mailMode":      "default",
   "smtpConfig":    {... see above ...},
   "rerouteConfig": {... see above ...},
   "bccConfig":     {... see above ...},
   "debugAddress":  "john.doe@example.com",
   "defaultSender": "jane.doe@example.com",
   "subjectPrefix": "[PHPUnitTest] "
}
```

Each of the configuration classes provide a static `from()` method that take these types as an argument and return the 
configuration object itself:

```
$smtpConfig    = SmtpConfig::from($jsonStringOrObjectOrAssocArray);
$rerouteConfig = RerouteConfig::from($jsonStringOrObjectOrAssocArray);
$bccConfig     = BccConfig::from($jsonStringOrObjectOrAssocArray);
$emailConfig   = EmailConfig::from($jsonStringOrObjectOrAssocArray);
```

## Sending and queueing multiple e-mails

It is possible to pass an array of `Email` objects to `send()` and `queue()` functions. However, especially for sending
e-mails immediately you should be aware that this can take some time. A better strategy is to queue mass mailings.

# Development Notes

Most PHPUnit tests will not be executed when there is no SMTP server or database available. The unit tests will check
for environment variable `EMAIL_TEST_SMTP` and `EMAIL_DATABASE`. There is a bash script available, 
[`set-test-env.sh`](https://github.com/technicalguru/php-email/blob/main/set-test-env.sh) that creates those
variables for you. Copy it to e.g. `set-local-test-env.sh` and follow instructions in the file.

# Contribution
Report a bug, request an enhancement or pull request at the [GitHub Issue Tracker](https://github.com/technicalguru/php-email/issues).
