const nunjucks = require('nunjucks');
const fs = require('fs');
const marked = require('marked');

nunjucks.configure({ autoescape: true });

const mdFileContent = fs.readFileSync('src/_pages/fächer/kunst.md', 'utf-8');

const [frontMatter, content] = mdFileContent.split('---').slice(1);

const htmlContent = marked(content);

const templateData = {
    frontMatter: YAML.parse (frontMatter.trim()),
    content: htmlContent,
};

const html = nunjucks.render('src/_includes/fächer.njk', templateData);