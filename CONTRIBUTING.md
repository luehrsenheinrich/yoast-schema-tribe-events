# Contributing to this project

Hi! Thank you for your interest in contributing to this plugin, we really appreciate it.

There are many ways to contribute – reporting bugs, feature suggestions, fixing bugs, submitting pull requests for enhancements.

## Reporting Bugs, Asking Questions, Sending Suggestions

Just [file a GitHub issue](./issues/new), that’s all! If you want to prefix the title with a “Question:”, “Bug:”, or the general area of the issue, that would be helpful, but by no means mandatory. If you have write access, add the appropriate labels.

If you’re filing a bug, specific steps to reproduce are helpful. Please include the URL of the page that has the bug, along with what you expected to see and what happened instead.

## Helping with the documentation

Every plugin is just as good as the documentation. In this repository we offer [collaboration with a wiki](./wiki) to create a documentation for this plugin.

## Setting up the dev environment

If you want to contribute code to the plugin you have to set up the environment locally. Make sure that you have `node`, `docker` and `grunt` installed.

The working directory is the `build` directory. If you change something in another location of the repository the pull request  will be ignored.

The development server and all dependencies are handled by docker, node and composer. Make sure you have [docker-compose installed](https://docs.docker.com/compose/install/) and run `npm run setup` in the directory. Your spawned WordPress instance will be available under `http://localhost` with the account `wordpress:wordpress`.

Please be aware, that you should usually not write code directly on the master branch.

Start the grunt watcher with the terminal command `grunt watch`. Grunt will make sure that the code will be compiled and copied to the trunk folder.

Before committing execute the command `grunt lint` to test if your code follows the general coding standards.

*IMPORTANT*: Edits outside the `build` directory will be overwritten by the grunt tasks. Make sure you don't work within the `trunk` folder or your work will be lost.

## I'm stuck, what do I do?

Not knowing what to do is perfectly normal for any developer or programmer. One of the main skills of a good programmer is the ability to quickly adapt patterns and find solutions. In short: Being able type the right question into Google. If you are stuck somewhere in plugin development we suggest the following:

1. Check out the [WordPress Codex](https://codex.wordpress.org) and read the documentation of what you're trying to build.
2. Look at how others do it. Places to look for best practices are the [TwentyNinteen plugin](https://github.com/WordPress/twentynineteen), the [Underscores plugin](https://github.com/automattic/_s) or the [TwentySeventeen plugin](https://github.com/WordPress/twentyseventeen).
3. Ask a senior programmer nearby

## Development Workflow

To keep the work in this repository structured and maintainable, we follow a certain way to add changes and code. A good workflow is structured like this:

1. Write or take an issue about the problem you want to solve
2. Add your own branch to the repository and add code to this branch
3. As soon as you have a presentable solution, add a pull request to the master branch
4. Get reviews for your solution and make sure the automated tests pass
5. Before merging the PR to the master branch, update your branch from master to resolve conflicts
6. Merge the PR into the master branch and delete your branch

### Naming branches

Ideally name your branches with prefixes and descriptions, like this: [type]/[change]. A good prefix would be:

* add/ = add a new feature
* try/ = experimental feature, "tentatively add"
* update/ = update an existing feature
* fix/ = fix a bug or unwanted behavior

For example, add/gallery-block means you're working on adding a new gallery block.

## Releasing Updates

The release workflow is more or less automated. A npm workflow takes the code, runs the tests and builds it into a release-ready zip. This zip is then attached to a GitHub release, from where it is then pushed to our own update server.

While theoretically everyone with write access to the repository has the ability to push a release, the release should only be done by one person, either the *build master* or the *project manager*.

*Important*: The GitHub release is added as a draft and has to be published manually.

To create a release just run `npm run release`. The perquisites for this are write access to the repository and a GitHub token in the `.env` file.
