# How to contribute

I'm really glad you're reading this, because we need volunteer developers to
help this project come to fruition.

If you haven't already, come find us in IRC (#contao.mm on freenode).
We want you working on things you're excited about.

The following is a set of guidelines for contributing to MetaModels and its
packages, which are hosted in the [MetaModels organization][1] on GitHub. These
are just guidelines, not rules, use your best judgement and feel free to
propose changes to this document in a pull request.

## Submitting issues

* Use the search function to see if a similar issue has already been submitted.
* Describe the issue in detail and include all the steps to follow in order to
  reproduce the bug.
* Include the version of Contao, PHP and MetaModels you are using (if possible
  with a detailed list of other installed extensions that might be related).
* Include screenshots or screencasts if possible; they are immensely helpful.
* If you are reporting a bug, please include any related error message you are
  seeing and also check the `system/logs/error.log` file. The error message is
  not just the message but also the complete(!) stack trace below the message.
  This trace is a long list of function calls which helps us to diagnose the
  problem en detail.

## Submitting changes

* Please send a [GitHub Pull Request to MetaModels][1] with a clear list of what
  you've done (read more about [pull requests][2]).
* When you send a pull request, we will love you forever if you include
  phpunit tests. We can always use more test coverage.
* Please follow the [phpcq 2.0][3] coding standards.
* Please make sure all of your commits are atomic (only one feature or fix per
  commit).
* We use phpcq/all-tasks in these projects, so please check your changes
  using phpcq when submitting a pull request.
* Create your pull request against the [`master`][4] branch for bug fixes or the
  [`develop`][5] branch for new features.
* Include screenshots in your pull request whenever possible.

Always write a clear log message for your commits.
One-line messages are fine for small changes, but bigger changes should look
like this:

    $ git commit -m "A brief summary of the commit
    >
    > A paragraph describing what changed and its impact."

## Testing

We have a handful of unit tests. Please write unit tests for new code you
create.

## Git commit messages

* Use the present tense ("Add feature" not "Added feature").
* Use the imperative mood ("Move cursor to …" not "Moves cursor to …").
* Reference issues and pull requests liberally.

[1]: https://github.com/MetaModels
[2]: http://help.github.com/pull-requests/
[3]: https://github.com/phpcq/coding-standard
[4]: https://github.com/MetaModels/core/pull/new/master
[5]: https://github.com/MetaModels/core/pull/new/develop
