const { DateTime } = require("luxon");

module.exports = function(eleventyConfig) {
    eleventyConfig.addPassthroughCopy('./src/style.css');
    eleventyConfig.addPassthroughCopy('./src/.htaccess');
    eleventyConfig.addPassthroughCopy('./src/sitemap.xml');
    eleventyConfig.addPassthroughCopy('./src/img');
    eleventyConfig.addPassthroughCopy('./src/scripts');
    eleventyConfig.addPassthroughCopy('./src/admin');
    eleventyConfig.addPassthroughCopy('./src/fonts');
    eleventyConfig.addPassthroughCopy('./src/assets');
    eleventyConfig.addPassthroughCopy('./src/untis');


    eleventyConfig.addPassthroughCopy({
        "./node_modules/dav/dav.min.js": "./src/assets/dav/dav.min.js"
    });
    eleventyConfig.addFilter("postDate", (dateObj) => {
        return DateTime.fromJSDate(dateObj).toLocaleString(DateTime.DATE_MED);
    });

    eleventyConfig.addCollection("faecher", function(collection) {
        return collection.getFilteredByGlob("src/_pages/fächer/*.md");
    });

    eleventyConfig.addNunjucksFilter("sortByOrder", (collection) => {
        return collection.sort((a, b) => a.data.order - b.data.order);
    });

    return {
        dir: {
            input: "src",
            output: "public"
        }
    }
}