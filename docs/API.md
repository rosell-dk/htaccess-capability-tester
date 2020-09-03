# API overview
This document is under development...

## The tests



### `htaccessEnabled()`
Apache can be configured to ignore `.htaccess` files altogether. This method tests if the `.htaccess` file is processed at all

The method works by trying out a series of subtests until a conclusion is reached. It can come out inconclusive, but it is very unlikely.

The first test works by setting the little known *ServerSignature* directive and relying on its even more little known side-effect, which is that it sets a server variable. The directive is part of core, which means it will almost always work. In order to disallow a core directive, the server admin must set *AllowOverride* to *None* and by setting *AllowOverrideList* to a list that does not include *ServerSignature*. Unfortunately, a PHP script is needed to check if the environment variable is set, which means that the test can come out inconclusive

The rest of the subtests simply consists of calling other test methods, which does not rely on PHP. If rewriting for example can be proven to work, well then the `.htaccess` must have been processed.

**Prerequisite to come out conclusive**

At least one of these must be satisfied:
- PHP allowed
- AllowOverride includes "Options"
- AllowOverride includes "Indexes" and mod_dir (Status: Base)
- AllowOverride includes "FileInfo" and at least one of these modules is loaded: mod_mime (Base), mod_rewrite, mod_headers


### `canRewrite()`
