# gitlabwiki Plugin for DokuWiki

Embed GitLab wikis and readmes

All documentation for this plugin can be found at
https://github.com/flaminggoat/dokuwiki-gitlab-wiki

## Usage

### Embedding project wikis
```html
<gitlab-wiki project="group/project"/>
```

### Embedding readmes
By default the the master branch will be used:
```html
<gitlab-wiki project="group/project" mdfile="README.md"/>
```
If you need to embed from a specific branch or commmit the ref can be specified
```html
<gitlab-wiki project="group/project" mdfile="README.md" ref="main"/>
```

If you install this plugin manually, make sure it is installed in
lib/plugins/gitlabwiki/ - if the folder is called different it
will not work!

Please refer to http://www.dokuwiki.org/plugins for additional info
on how to install plugins in DokuWiki.

----
Copyright (C) Theo Hussey <husseytg@gmail.com>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; version 2 of the License

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

See the LICENSE file for details
