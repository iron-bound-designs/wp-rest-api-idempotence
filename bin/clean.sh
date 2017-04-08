#!/bin/sh

rm -Rf .git
rm -Rf .github
rm -Rf tests
rm -f .gitattributes
rm -f .gitignore
rm -f .travis.yml
rm -f phpunit.xml
rm -f readme.md

rm -rf vendor/*/*/.git
rm -rf vendor/*/*/doc
rm -rf vendor/*/*/docs
rm -rf vendor/*/*/test
rm -rf vendor/*/*/tests
rm -rf vendor/*/*/examples
rm -rf vendor/*/*/website
rm -rf vendor/*/*/benchmarks
rm -rf vendor/*/*/news
rm -rf vendor/*/*/demo
rm -rf vendor/gregwar/Captcha/Font