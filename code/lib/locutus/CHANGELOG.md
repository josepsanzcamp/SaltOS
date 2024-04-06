# Changelog

Our combined changelog and roadmap. It contains todos as well as dones.

Only project-wide changes are mentioned here. For individual function changelogs, please refer to their respective Git
histories.

Locutus does not follow SemVer as we're a work in progress - and even though we try, we cannot guarantee BC-safety for
the hundreds of contributions across the many languages that Locutus is assimilating.

## Backlog

Ideas that will be planned and find their way into a release at one point

- [ ] Address the 25 remaining test failures that are currently skipped (find out which ones via
      `yarn test:languages:noskip`)
- [ ] Compare example test cases for PHP against `php -r` to make sure they are correctly mimicking the most recent
      stable behavior
- [ ] Have _one_ way of checking pure JS arrays vs PHP arrays (vs:
      `Object.prototype.toString.call(arr1) === '[object Array]'`, `typeof retObj[p] === 'object'`,
      `var asString = Object.prototype.toString.call(mixedVar) var asFunc = _getFuncName(mixedVar.constructor) if (asString === '[object Object]' && asFunc === 'Object') {`
      )
- [ ] Investigate if we can have one helper function for intersecting, and use that in all `array_*diff*` and
      `array_*sort*` functions. Refrain from using `labels`, which those functions currently still rely on
- [ ] Investigate if we can have one helper function for sorting, and use that in all `*sort*` functions
- [ ] Investigate if we can have one helper function to resolve
      `Function/'function'/'Class::function'/[$object, 'function']`, and use that in `is_callable`, `array_walk`,
      `call_user_func_array` etc.
- [ ] Parse `require`s with AST just like Browserify does. Then we can add dependencies back to website
- [ ] Port a few more tricky/inter-depending Go functions
- [ ] Port a few more tricky/inter-depending Python functions
- [ ] Port a few more tricky/inter-depending Ruby functions
- [ ] website: Render authors server-side
- [ ] website: Fix the search functionality

## main

Released: TBA. [Diff](https://github.com/locutusjs/locutus/compare/v2.0.32...main).

- [ ]

## v2.0.32

Released: 2024-04-05. [Diff](https://github.com/locutusjs/locutus/compare/v2.0.31...v2.0.32).

- [x] Update intro and add to the NPM module as a `README.md`

## v2.0.31

Released: 2024-04-05. [Diff](https://github.com/locutusjs/locutus/compare/v2.0.30...v2.0.31).

- [x] dx: Add `use strict` to generated tests
- [x] dx: Add Stale Action
- [x] file_exists: Introduced (in #461, thx @erikn69)
- [x] strtotime: Add support oracle dates (fixes #340)
- [x] bin2hex: Add support for multi-byte characters (fixes #427)
- [x] var_dump: Detect circular references (fixes #305)
- [x] escapeshellarg: Add Windows support (fixes #395)
- [x] fmod: Fix Uncaught RangeError: toFixed() digits argument must be between 0 and 100 (thx @dekairi, fixes #417)

## v2.0.30

Released: 2024-04-05. [Diff](https://github.com/locutusjs/locutus/compare/v2.0.29...v2.0.30).

- [x] Relax yarn engine requirement (fixes #467)
- [x] Allow for custom mocha tests for functions (that arent generated based on header comments)

## v2.0.29

Released: 2024-04-04. [Diff](https://github.com/locutusjs/locutus/compare/v2.0.16...v2.0.29).

- [x] Fix issue #458: unserialize fails when serialized array contains (@kukawski)
- [x] dx: Switch from `master` -> `main` branch
- [x] dx: Upgrade to Yarn 4 managed by Corepack
- [x] dx: Add testing for Node 20
- [x] dx: Add prettier & upgrade ESLint & StandardJS
- [x] dx: Upgrade Hexo to latest
- [x] dx: Clarify contributing docs
- [x] dx: Allow all core contributors to cut NPM releases by pushing Git tags (GHA CI handles the rest)
- [x] dx: Upgrade all remaining dependencies

## v2.0.16

Released: 2019-06-12. [Diff](https://github.com/locutusjs/locutus/compare/v2.0.10...v2.0.16).

- [x] Switch from Travis CI to GitHub Actions
- [x] Fix ReDOS on IPv6
- [x] Basic timezone support in strtotime

## v2.0.11

Released: 2019-06-12. [Diff](https://github.com/locutusjs/locutus/compare/v2.0.10...v2.0.11).

- [x] functions: Community-contributed function improvements, see respective functions' changelogs in the Diff:
- [x] ci: test Node.js 6, 8, 10 and 11 (#384)
- [x] website: Fix code listing on locutus website (#379)

## v2.0.10

Released: 2018-09-07. [Diff](https://github.com/locutusjs/locutus/compare/v2.0.9...v2.0.10).

- [x] functions: Community-contributed function improvements, see respective functions' changelogs in the Diff.

## v2.0.9

Released: 2017-06-22. [Diff](https://github.com/locutusjs/locutus/compare/v2.0.8...v2.0.9).

- [x] functions: Community-contributed function improvements, see respective functions' changelogs in the Diff.

## v2.0.8

Released: 2017-02-23. [Diff](https://github.com/locutusjs/locutus/compare/v2.0.7...v2.0.8).

- [x] Upgrade eslint and fix newly found issues accordingly
- [x] functions: Community-contributed function improvements, see respective functions' changelogs in the Diff.

## v2.0.7

Released: 2017-02-09. [Diff](https://github.com/locutusjs/locutus/compare/v2.0.6...v2.0.7).

- [x] functions: Community-contributed function improvements, see respective functions' changelogs in the Diff.

## v2.0.6

Released: 2016-06-16. [Diff](https://github.com/locutusjs/locutus/compare/v2.0.5...v2.0.6).

- [x] Language fixes

## v2.0.5

Released: 2016-06-16. [Diff](https://github.com/locutusjs/locutus/compare/v2.0.4...v2.0.5).

- [x] Cache node modules on Travis so we'll be less dependent on npm connectivity

## v2.0.4

Released: 2016-05-25. [Diff](https://github.com/locutusjs/locutus/compare/v2.0.3...v2.0.4).

- [x] Upgrade depurar to 0.2.2, fixing an issue with the testwriter (@kukawski)
- [x] Add the 'reimplemented by' and 'parts by' contributionKeys to the /authors website page
- [x] Fix linting warnings when hacking on website by adding eslint dependencies locally
- [x] Improve array_rand: Fix coding style, hangs when selected huge number of keys from huge array, function signature
      (@kukawski)

## v2.0.3

Released: 2016-05-22. [Diff](https://github.com/locutusjs/locutus/compare/v2.0.2...v2.0.3).

- [x] Minor `util.js` refactoring
- [x] Use hexo deploy instead of custom bash script to aid Windows compatibility
- [x] Use cross-env and rimraf in build scripts to aid Windows compatibility
- [x] Improve Travis auto-deployments (now using official deploy methods)
- [x] Switch from locutusjs.io to locutus.io
- [x] Triage all open issues and PRs
- [x] Triage all open issues and PRs
- [x] docs: Miscellaneous cosmetic updates
- [x] website: Miscellaneous cosmetic updates
- [x] website: Show languages & functions in profile sidebar
- [x] website: Add social buttons
- [x] website: Let Travis auto-deploy to gh-pages on main changes
- [x] website: Use Hexo deploy vs bash script

## v2.0.2

Released: 2016-05-02. [Diff](https://github.com/locutusjs/locutus/compare/v2.0.1...v2.0.2).

- [x] Don't use `files` in package.json as we don't ship all of `dist` now

## v2.0.1

Released: 2016-05-02. [Diff](https://github.com/locutusjs/locutus/compare/v2.0.0...v2.0.1).

- [x] Don't use `bin` in package.json as we don't ship `cli.js`

## v2.0.0

Released: 2016-05-02. [Diff](https://github.com/locutusjs/locutus/compare/v1.3.2...v2.0.0).

- [x] website: Add profile to sidebar
- [x] Rename `_locutus_shared` to `_helpers`. Rename `_locutus_shared_bc` to `_bc`
- [x] website: Fix jumpy scrolling due to on the githubs
- [x] website: DRY up add_more code
- [x] website: About as homepage
- [x] Transpile to ES5 before publishing to npm
- [x] Add `estarget` option to all functions, set `ip2long` to `es2015`
- [x] Change `fix(me)?` and `Todo` to `@todo`
- [x] Replace single line `/**/` comments with `//`
- [x] Enforce a 100 character line-length via eslint, and change all functions accordingly
- [x] Do not pass values by reference via the `window` global, use e.g. `countObj.value` and `errorObj.value` instead
- [x] Have _one_ way of getting an `ini` value and its default
- [x] Track all cases of `setTimeout`, use them without window prefix. Remove `codez` replace hack
- [x] Track all cases of XMLHttpRequest
- [x] Test `is_array` in-browser to see if the `require` for `ini_get` works correctly with Browserify
- [x] Deprecate blocking ajax requests in e.g. `file_get_contents`
- [x] Use native `sha1` and `md5` encoding when available
- [x] Remove XUL from functions
- [x] Rename `strictForIn` to `sortByReference`
- [x] Remove `// (BEGIN|END) (STATIC|REDUNDANT)`
- [x] Index all `note`s
- [x] Either deprecate or document all functions using `eval` or `new Function`
- [x] Port Util to ES6 class
- [x] Write one function (`ip2long`) in ES6 and make it pass tests & linting
- [x] Deprecate support for node_js: 0.8
- [x] Make Travis fail on eslint issues
- [x] Move CHANGELOG to own file
- [x] Make all functions pass eslint with JavaScript Standard Style
- [x] Remove `_workbench` and `_experimental`. They are available for reference in `1.3.2` but making them harder to
      find for newcomers should help avoid a lot of complaints
- [x] Move functions that overly rely on ini & locales & global & ajax file operations to \_legacy
- [x] Address ~50 test failures that were previously skipped and now enabled
- [x] `json_*` functions can leverage Node's
- [x] Add ES6 capabilities
- [x] Adopt better global detection, use `$locutus.golang.<specifics>`
- [x] Add more 'social' buttons to website (twitter, github)
- [x] Rework injectweb after structural changes in util.js
- [x] Use `require` for dependencies
- [x] Remove `;` from examples in accordance with JavaScript Standard Style
- [x] Use mocha for generating language tests
- [x] Use require for dependencies
- [x] In util.opener: First `*` should point to the requesting/current language
- [x] Split out the npm module so you could do `var sprintf = require('locutus/sprintf')`
- [x] Launch BC breaking blogpost
- [x] Roll out support for Ruby, C, Go, Python

## v1.3.2

Released: April 4, 2016

- [x] Start using a CHANGELOG https://github.com/locutusjs/locutus/releases/tag/v1.3.2
