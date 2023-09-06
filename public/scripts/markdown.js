const nunjucks = require('nunjucks');
const fs = require('fs');
const marked = require('marked');

nunjucks.configure({ autoescape: true });

const mdFileContent = fs.readFileSync()