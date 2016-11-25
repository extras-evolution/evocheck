### EvoCheck 0.1
A small assistant to help finding compromised code in a MODX Evolution installation for the seldom case, it has been "hacked".

##### Features
- Search DB (Plugins, Snippets etc) and Files for any string (RegEx supported)
- Files can be filtered by touch-date (useful, but not relyable!)
- Show summary all found strings with highlighting
- Show a list of plugins assigned to critical plugin-events
- Same check and report of all files that are set in MODX Config under "Check Files on Login" (index.php, .htaccess etc.)

##### Installation
By uploading "EvoCheck Standalone" only when needed, it is guaranteed EvoCheck has not been compromised and is fully working.

- Download package
- open file `evocheck.php` and change password in line 18
- upload to directory /manager of the installation you want to check

#### Usage
Experienced users will not need this manual. It is just a simple try of explanation to none-experienced users, to enable them start investigating on their own and get more help at https://github.com/modxcms/evolution .

- call file directly via browser `http(s)://domainxy.com/manager/evocheck.php`, enter your password
- search database & files and check for suspicious code, that in most cases is made not readable for humans by intention, like
  - `eval(base64_decode("aWYgKCFkZWZpbmVkKCdBTFJFQURZX1JVTl8xYmMyOWIzN.....`
  - `$nds3 = $ymdq98[7].$ymdq98[1].$ymdq98[8].$ymdq98[6]`
  - to be continued..
- Not every piece of code looking suspicious is part of a hack, therefore before altering any files or DB-tables, make a backup first!
- The "Changed-after"-date is not relyable! File-dates can be easily modified on many servers using PHPs `touch()`-function  
- Click "Delete me now" when you finished work, or delete EvoCheck manually if "Delete me now" throws an error.

#### Todo
- add option to pre-select most common RegEx-strings (list will be extended by experience)
- redesign:
  - maybe more console-like ?
  - layout with smaller fonts
  - frames top/bottom instead of left/right (to have long Input-Field for RegEx)
- improve/refactor renderSummary() / highlighting ?
- check UTF-8 support for highlighting etc
- add searching "TemplateVars" as they also support `eval()` ? 

#### History
- v0.1 initial release