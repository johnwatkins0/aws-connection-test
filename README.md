# aws-connection-test

To test this:

1. Run `composer install`
2. Run using php cli:

```
php index.php [repository] [roleArn]
```

For example:

```
php index.php 10up myrolearn
```

If roleArn is not included, the script will use the default profile and attempt to read it from .aws/credentials.



