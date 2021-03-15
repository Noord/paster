# Paste service

A file upload service like gist under 100 lines of code. Stores pastes in files. No dependency.

Doesn't check mimetypes or anything else. Which is not suitable for  public networks without credentials

```sh
# to initialize
mkdir pastes
cp cred.php.inc cred.local.php.inc
vim cred.local.php.inc # add your user tokens
```

Usage:

```sh
#to upload file
> cat file | curl -s -H "APIKEY:pass" -X POST --data-binary @- host/
host/7UJM
# to read file
> curl -s host/7UJM
```
