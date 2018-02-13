# Contributing

I welcome anyone who wants to contribute (who will be fully credited!) or provide constructive feedback, no matter the age or level of experience.  
Thanks for adding to the package and making the world a better place™.

## Issues

To encourage active collaboration, pull requests are strongly encouraged, not just bug reports.  *"Bug reports"* may also be sent in the form of a pull request containing a failing test.

However, if you file a bug report, your issue should contain a title and a clear description of the issue.  You should also include as much relevant information as possible and a code sample that demonstrates the issue.  
The goal of a bug report is to make it easy for yourself - and others - to replicate the bug and develop a fix. If an issue you have is already reported, please add additional information or add a 👍 reaction to indicate your agreement.

Remember, bug reports are created in the hope that others with the same problem will be able to collaborate with you on solving it!  
Do not expect that the bug report will automatically see any activity or that others will jump to fix it.  
Creating a bug report serves to help yourself and others start on the path of fixing the problem.

## Pull requests

* **[PSR-2 Coding Standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)** - The easiest way to apply the conventions is to install an external tool like [PHP Code Sniffer](https://github.com/squizlabs/PHP_CodeSniffer).
* **Add tests!** - Your patch won't be accepted if it doesn't have tests.
* **Document any changes** - Make sure the `README.md` and any other relevant documentation are kept up-to-date.
* **Create feature branches** - Use `git checkout -b my-new-feature`. Don't ask to pull from your `master` branch.
* **One pull request per feature** - If you want to do more than one thing please send multiple pull requests.
* **Send coherent history** - Make sure each individual commit in your pull request is meaningful. If you had to make multiple intermediate commits while developing, please [squash them](http://www.git-scm.com/book/en/v2/Git-Tools-Rewriting-History#Changing-Multiple-Commit-Messages) before submitting.

## Code review guidelines

Here are some things to look for:

1. **Required CI checks pass.** This is a prerequisite for the review, and it is the PR author's responsibility. As long as the tests don’t pass, the PR won't get reviewed.
2. **Simplicity.** Is this the simplest way to achieve the intended goal? If there are too many files, redundant functions, or complex lines of code, suggest a simpler way to do the same thing. In particular, avoid implementing an overly general solution when a simple, small, and pragmatic fix will do.
3. **Testing.** Do the tests ensure this code won’t break when other stuff changes around it? When it does break, will the tests added help identify which part of the library has the problem? Was an appropriate set of edge cases covered? Look at the test coverage report if there is one. Are all significant code paths in the new code exercised at least once?
4. **No unnecessary or unrelated changes.** PRs shouldn’t come with random formatting changes, especially in unrelated parts of the code. If there is some refactoring that needs to be done, it should be in a separate PR from a bug fix or feature, if possible.
5. **Code has appropriate comments.** Code should be commented, or written in a clear “self-documenting” way.
6. **Language usage.** This is a PHP 7 package so make sure type/return hints are used wherever possible. Ideally a linter will enfore a lot of good practises, but use your common sense and follow the style of the surrounding code.

## Follow-up
This project makes use of an [editorconfig](http://editorconfig.org/). This ensures consistent settings across editors of:
* indent size
* trailing newlines
* trimming final whitespace
* etc.  

It's the first step in ensuring consistent code output. You might have to install the plugin for your editor of choice.  This projects .editorconfig file should kick in once you do.

**Happy coding :)**!
