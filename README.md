# Paste service

A file upload service like gist under 100 lines.

Stores pastes in files. No external dependency.

to upload file
```sh
> cat yourfile | curl -H "APIKEY:pass" -X POST -d @- http://host/
host/7UJM
```

to read file
```sh
> curl -X POST -d @- http://host/7UJM
```