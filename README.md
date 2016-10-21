# Screaming Fist

### A sample WordPress theme demonstrating use of [Sass](https://sass-lang.com) to create custom themes.

![Screaming Fist Logo created by [Michael Wolfe](http://www.wolfecreativedesign.com/#/screaming-fist/)](./screaming-fist-logo.png)

Screaming Fist Logo created by [Michael Wolfe](http://www.wolfecreativedesign.com/#/screaming-fist/)

### Features

* Custom CSS for WYSIWYG editor
* Custom CSS for WP Admin
* Far futures expiration of CSS file
* gulp build process with autoprefixer and livereload
* Separate development and production CSS

### Setup

```
npm install
bower install
gulp
```

### Development

Run `gulp` in "watch mode" so that CSS is regenerated from Sass and browsers are reloaded via [livereload](http://livereload.com/extensions/) when Sass changes.
```
gulp watch
```

### Class Namespaces

* `b-` base styles
* `c-` components
* `l-` layouts
* `t-` global tokens
* `lt-` local tokens
* `s-` scopes

### Resources

* <https://sass-guidelin.es/> - An opinionated styleguide for writing sane, maintainable and scalable Sass.
* <https://sass-guidelin.es/#the-7-1-pattern> - The 7-1 Pattern for Sass architecture
* <http://csswizardry.com/2015/03/more-transparent-ui-code-with-namespaces/>

