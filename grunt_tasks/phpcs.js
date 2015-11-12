/**
* @file Analyse PHP, CSS, and JS files for coding standard violations
*/

module.exports = {
    src: {
        src: ['src/**/*.php']
    },
    options: {
        standard: 'phpcs.xml',
        extensions: 'php',
        showSniffCodes: true
    }
};
