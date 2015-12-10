/**
* @file File Watcher
*
* Executes lint tasks on PHP and JSON files.
*/

module.exports = {
    json: {
        files: [
            '*.json',
            'config/*.json'
        ],
        tasks: ['jsonlint']
    },
    php: {
        files: [
            'src/**/*.php',
            'tests/**/*.php'
        ],
        tasks: ['phplint']
    }
};
